<?php
declare(strict_types=1);

namespace MageMasani\WebPushNotification\Model;

use Magento\Framework\HTTP\Client\CurlFactory;
use MageMasani\WebPushNotification\Helper\Data as Helper;
use MageMasani\WebPushNotification\Model\ResourceModel\Token as TokenResource;

class Sender
{
    private const OAUTH2_TOKEN_URI = 'https://oauth2.googleapis.com/token';
    private const FCM_SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';
    private const FCM_V1_ENDPOINT = 'https://fcm.googleapis.com/v1/projects/%s/messages:send';

    private const HTTP_TIMEOUT_SEC = 15;
    private const HTTP_CONNECT_TIMEOUT_SEC = 5;
    private const ACCESS_TOKEN_SKEW_SEC = 60;

    private const MAX_TITLE_LENGTH = 240;
    private const MAX_BODY_LENGTH = 4000;

    private CurlFactory $curlFactory;
    private Helper $helper;
    private TokenResource $tokenResource;

    private ?string $cachedAccessToken = null;
    private int $cachedAccessTokenExpiry = 0;

    public function __construct(
        CurlFactory $curlFactory,
        Helper $helper,
        TokenResource $tokenResource
    ) {
        $this->curlFactory = $curlFactory;
        $this->helper = $helper;
        $this->tokenResource = $tokenResource;
    }

    /**
     * @return array{success:int,failure:int,errors:array}
     */
    public function send(string $title, string $body, string $clickUrl = '/', string $imageUrl = ''): array
    {
        $title = mb_substr(trim($title), 0, self::MAX_TITLE_LENGTH);
        $body = mb_substr(trim($body), 0, self::MAX_BODY_LENGTH);
        if ($title === '' || $body === '') {
            throw new \RuntimeException('Title and body are required.');
        }

        $tokens = $this->getActiveTokens();
        if ($tokens === []) {
            throw new \RuntimeException('No tokens to send to.');
        }

        $serviceAccount = $this->helper->getServiceAccount();
        $accessToken = $this->getAccessToken($serviceAccount);
        $endpoint = sprintf(self::FCM_V1_ENDPOINT, $serviceAccount['project_id']);
        $iconUrl = $this->helper->getDefaultIconUrl();
        $clickUrl = $clickUrl !== '' ? $clickUrl : '/';

        $success = 0;
        $failure = 0;
        $errors = [];

        $data = [
            'title' => $title,
            'body' => $body,
            'click_action' => $clickUrl,
            'tag' => 'webpush'
        ];
        if ($iconUrl !== '') {
            $data['icon'] = $iconUrl;
        }
        if ($imageUrl !== '') {
            $data['image'] = $imageUrl;
        }

        foreach ($tokens as $token) {
            $payload = [
                'message' => [
                    'token' => $token,
                    'data' => $data,
                    'webpush' => [
                        'headers' => ['Urgency' => 'high', 'TTL' => '2419200'],
                        'fcm_options' => ['link' => $clickUrl]
                    ],
                    'android' => [
                        'priority' => 'HIGH',
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                            'click_action' => $clickUrl,
                            'sound' => 'default'
                        ]
                    ],
                    'apns' => [
                        'headers' => ['apns-priority' => '10'],
                        'payload' => [
                            'aps' => [
                                'alert' => ['title' => $title, 'body' => $body],
                                'sound' => 'default',
                                'mutable-content' => 1
                            ]
                        ]
                    ]
                ]
            ];

            [$status, $responseBody] = $this->postJson($endpoint, $payload, [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json'
            ]);

            if ($status >= 200 && $status < 300) {
                $success++;
                continue;
            }

            $failure++;
            $errors[] = ['token' => $token, 'status' => $status, 'body' => $responseBody];

            if ($status === 401 || $status === 403) {
                $this->cachedAccessToken = null;
                $this->cachedAccessTokenExpiry = 0;
            }
            if ($status === 404 || $status === 410 || $this->isUnregistered($responseBody)) {
                $this->deactivateToken($token);
            }
        }

        return ['success' => $success, 'failure' => $failure, 'errors' => $errors];
    }

    /**
     * @return string[]
     */
    public function getActiveTokens(): array
    {
        $connection = $this->tokenResource->getConnection();
        $select = $connection->select()
            ->from($this->tokenResource->getMainTable(), 'token')
            ->where('is_active = ?', 1);
        return $connection->fetchCol($select);
    }

    private function getAccessToken(array $serviceAccount): string
    {
        if (
            $this->cachedAccessToken !== null
            && $this->cachedAccessTokenExpiry > time() + self::ACCESS_TOKEN_SKEW_SEC
        ) {
            return $this->cachedAccessToken;
        }

        $tokenUri = $serviceAccount['token_uri'] ?? self::OAUTH2_TOKEN_URI;
        $issuedAt = time();
        $claim = [
            'iss' => $serviceAccount['client_email'],
            'scope' => self::FCM_SCOPE,
            'aud' => $tokenUri,
            'iat' => $issuedAt,
            'exp' => $issuedAt + 3600
        ];

        $jwt = $this->signJwt($claim, $serviceAccount['private_key']);

        $curl = $this->curlFactory->create();
        $curl->setTimeout(self::HTTP_TIMEOUT_SEC);
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, self::HTTP_CONNECT_TIMEOUT_SEC);
        $curl->setHeaders(['Content-Type' => 'application/x-www-form-urlencoded']);

        try {
            $curl->post($tokenUri, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]);
        } catch (\Throwable $e) {
            throw new \RuntimeException('OAuth2 token request failed: ' . $e->getMessage(), 0, $e);
        }

        $status = (int) $curl->getStatus();
        $body = (string) $curl->getBody();
        $decoded = json_decode($body, true);

        if ($status < 200 || $status >= 300 || empty($decoded['access_token'])) {
            throw new \RuntimeException('Failed to obtain OAuth2 access token (HTTP ' . $status . ').');
        }

        $this->cachedAccessToken = (string) $decoded['access_token'];
        $this->cachedAccessTokenExpiry = time() + (int) ($decoded['expires_in'] ?? 3600);

        return $this->cachedAccessToken;
    }

    /**
     * @return array{0:int,1:string}
     */
    private function postJson(string $url, array $payload, array $headers): array
    {
        $curl = $this->curlFactory->create();
        $curl->setTimeout(self::HTTP_TIMEOUT_SEC);
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, self::HTTP_CONNECT_TIMEOUT_SEC);
        $curl->setHeaders($headers);

        try {
            $curl->post($url, (string) json_encode($payload));
            return [(int) $curl->getStatus(), (string) $curl->getBody()];
        } catch (\Throwable $e) {
            return [0, $e->getMessage()];
        }
    }

    private function signJwt(array $claim, string $privateKeyPem): string
    {
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $segments = [
            $this->base64UrlEncode((string) json_encode($header)),
            $this->base64UrlEncode((string) json_encode($claim))
        ];
        $signingInput = implode('.', $segments);

        $pkey = openssl_pkey_get_private($privateKeyPem);
        if ($pkey === false) {
            throw new \RuntimeException('Service account private_key is not a valid PEM key.');
        }

        $signature = '';
        if (!openssl_sign($signingInput, $signature, $pkey, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('Failed to sign JWT with service account key.');
        }

        $segments[] = $this->base64UrlEncode($signature);
        return implode('.', $segments);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function isUnregistered(string $responseBody): bool
    {
        $decoded = json_decode($responseBody, true);
        if (!is_array($decoded) || empty($decoded['error'])) {
            return false;
        }
        $error = $decoded['error'];
        if (in_array($error['status'] ?? '', ['UNREGISTERED', 'NOT_FOUND'], true)) {
            return true;
        }
        foreach ($error['details'] ?? [] as $detail) {
            if (is_array($detail) && ($detail['errorCode'] ?? '') === 'UNREGISTERED') {
                return true;
            }
        }
        return false;
    }

    private function deactivateToken(string $token): void
    {
        try {
            $connection = $this->tokenResource->getConnection();
            $connection->update(
                $this->tokenResource->getMainTable(),
                ['is_active' => 0],
                ['token = ?' => $token]
            );
        } catch (\Throwable $e) {
            // best-effort; failure is non-critical
        }
    }
}
