<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model;

use Magento\Framework\HTTP\Client\CurlFactory;
use MageMasani\PushNotification\Model\ConfigInterface;
use MageMasani\PushNotification\Model\ResourceModel\Token as TokenResource;

/**
 * Model Sender
 */
class Sender
{
    /**
     * Oauth2 token uri constant
     *
     * @var string
     */
    private const OAUTH2_TOKEN_URI = 'https://oauth2.googleapis.com/token';
    /**
     * Fcm scope constant
     *
     * @var string
     */
    private const FCM_SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';
    /**
     * Fcm v1 endpoint constant
     *
     * @var string
     */
    private const FCM_V1_ENDPOINT = 'https://fcm.googleapis.com/v1/projects/%s/messages:send';

    /**
     * Http timeout sec constant
     *
     * @var int
     */
    private const HTTP_TIMEOUT_SEC = 15;
    /**
     * Http connect timeout sec constant
     *
     * @var int
     */
    private const HTTP_CONNECT_TIMEOUT_SEC = 5;
    /**
     * Access token skew sec constant
     *
     * @var int
     */
    private const ACCESS_TOKEN_SKEW_SEC = 60;

    /**
     * Max title length constant
     *
     * @var int
     */
    private const MAX_TITLE_LENGTH = 240;
    /**
     * Max body length constant
     *
     * @var int
     */
    private const MAX_BODY_LENGTH = 4000;

    /**
     * @var CurlFactory
     */
    private CurlFactory $curlFactory;
    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;
    /**
     * @var TokenResource
     */
    private TokenResource $tokenResource;
    /**
     * @var HistoryLogger
     */
    private HistoryLogger $historyLogger;
    /**
     * @var \Magento\Framework\MessageQueue\PublisherInterface
     */
    private \Magento\Framework\MessageQueue\PublisherInterface $publisher;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private \Magento\Framework\Serialize\Serializer\Json $serializer;

    /**
     * @var string
     */
    private ?string $cachedAccessToken = null;
    /**
     * @var int
     */
    private int $cachedAccessTokenExpiry = 0;

    /**
     * Initialize dependencies
     *
     * @param CurlFactory $curlFactory
     * @param ConfigInterface $config
     * @param TokenResource $tokenResource
     * @param HistoryLogger $historyLogger
     * @param \Magento\Framework\MessageQueue\PublisherInterface $publisher
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @return void
     */
    public function __construct(
        CurlFactory $curlFactory,
        ConfigInterface $config,
        TokenResource $tokenResource,
        HistoryLogger $historyLogger,
        \Magento\Framework\MessageQueue\PublisherInterface $publisher,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->curlFactory = $curlFactory;
        $this->config = $config;
        $this->tokenResource = $tokenResource;
        $this->historyLogger = $historyLogger;
        $this->publisher = $publisher;
        $this->serializer = $serializer;
    }

    /**
     * Send push notification to ALL active tokens.
     *
     * @return array{success:int,failure:int,errors:array}
     */
    public function send(
        string $title,
        string $body,
        string $clickUrl = '/',
        string $imageUrl = '',
        string $notificationType = 'manual',
        ?int $campaignId = null
    ): array {
        // if ($this->config->isAsyncEnabled()) {
        //     return $this->publishToQueue($title, $body, $clickUrl, $imageUrl, $notificationType, $campaignId, null);
        // }

        return $this->sendDirect($title, $body, $clickUrl, $imageUrl, $notificationType, $campaignId);
    }

    /**
     * Send direct synchronous push notification to ALL active tokens.
     *
     * @return array{success:int,failure:int,errors:array}
     */
    public function sendDirect(
        string $title,
        string $body,
        string $clickUrl = '/',
        string $imageUrl = '',
        string $notificationType = 'manual',
        ?int $campaignId = null
    ): array {
        $tokens = $this->getActiveTokens();
        if ($tokens === []) {
            throw new \RuntimeException('No tokens to send to.');
        }

        return $this->executeDirectSend($tokens, $title, $body, $clickUrl, $imageUrl, $notificationType, true, null, $campaignId);
    }

    /**
     * Send push notification to a specific customer's active tokens.
     *
     * @return array{success:int,failure:int,errors:array}
     */
    public function sendToCustomer(
        int $customerId,
        string $title,
        string $body,
        string $clickUrl = '/',
        string $imageUrl = '',
        string $notificationType = 'event',
        ?int $campaignId = null
    ): array {
        if ($this->config->isAsyncEnabled()) {
            return $this->publishToQueue($title, $body, $clickUrl, $imageUrl, $notificationType, $campaignId, $customerId);
        }

        return $this->sendDirectToCustomer($customerId, $title, $body, $clickUrl, $imageUrl, $notificationType, $campaignId);
    }

    /**
     * Send direct synchronous push notification to a specific customer's active tokens.
     *
     * @return array{success:int,failure:int,errors:array}
     */
    public function sendDirectToCustomer(
        int $customerId,
        string $title,
        string $body,
        string $clickUrl = '/',
        string $imageUrl = '',
        string $notificationType = 'event',
        ?int $campaignId = null
    ): array {
        $tokens = $this->getActiveTokensByCustomerId($customerId);
        if ($tokens === []) {
            return ['success' => 0, 'failure' => 0, 'errors' => []];
        }

        return $this->executeDirectSend($tokens, $title, $body, $clickUrl, $imageUrl, $notificationType, false, $customerId, $campaignId);
    }

    /**
     * Publish sending request to the message queue.
     */
    private function publishToQueue(
        string $title,
        string $body,
        string $clickUrl,
        string $imageUrl,
        string $notificationType,
        ?int $campaignId,
        ?int $customerId
    ): array {
        $this->publisher->publish(
            'magemasani.pushnotification.send',
            $this->serializer->serialize([
                'title' => $title,
                'body' => $body,
                'click_url' => $clickUrl,
                'image_url' => $imageUrl,
                'notification_type' => $notificationType,
                'campaign_id' => $campaignId,
                'customer_id' => $customerId
            ])
        );
        return ['success' => 0, 'failure' => 0, 'queued' => true, 'errors' => []];
    }

    /**
     * Common method to execute FCM request and log history.
     */
    private function executeDirectSend(
        array $tokens,
        string $title,
        string $body,
        string $clickUrl,
        string $imageUrl,
        string $notificationType,
        bool $isGlobal,
        ?int $customerId,
        ?int $campaignId
    ): array {
        // Map tokens to their customer IDs
        $tokenCustomerMap = $this->getTokensCustomerMap($tokens);

        $success = 0;
        $failure = 0;
        $errors = [];

        foreach ($tokens as $token) {
            // Send to this single token
            $singleResult = $this->sendToTokens([$token], $title, $body, $clickUrl, $imageUrl);

            $success += $singleResult['success'];
            $failure += $singleResult['failure'];
            if (!empty($singleResult['errors'])) {
                array_push($errors, ...$singleResult['errors']);
            }

            // Determine customer ID for this token
            $tokenCustomerId = $tokenCustomerMap[$token] ?? null;

            // Log individual history entry for this token/customer
            $this->historyLogger->log(
                $singleResult,
                $title,
                $body,
                $clickUrl,
                $imageUrl,
                $notificationType,
                $tokenCustomerId === null, // isGlobal is true if there's no customer id (i.e. guest)
                $tokenCustomerId,
                $campaignId
            );
        }

        return ['success' => $success, 'failure' => $failure, 'errors' => $errors];
    }

    /**
     * Get customer IDs mapped to tokens.
     *
     * @param string[] $tokens
     * @return array<string, int|null>
     */
    private function getTokensCustomerMap(array $tokens): array
    {
        if (empty($tokens)) {
            return [];
        }
        $connection = $this->tokenResource->getConnection();
        $select = $connection->select()
            ->from($this->tokenResource->getMainTable(), ['token', 'customer_id'])
            ->where('token IN (?)', $tokens);
        $rows = $connection->fetchAll($select);
        $map = [];
        foreach ($rows as $row) {
            $map[$row['token']] = $row['customer_id'] !== null ? (int) $row['customer_id'] : null;
        }
        return $map;
    }

    /**
     * Send push notification to a given list of FCM tokens.
     *
     * @param string[] $tokens
     * @return array{success:int,failure:int,errors:array}
     */
    private function sendToTokens(
        array $tokens,
        string $title,
        string $body,
        string $clickUrl = '/',
        string $imageUrl = ''
    ): array {
        $title = mb_substr(trim($title), 0, self::MAX_TITLE_LENGTH);
        $body = mb_substr(trim($body), 0, self::MAX_BODY_LENGTH);
        if ($title === '' || $body === '') {
            throw new \RuntimeException('Title and body are required.');
        }

        $serviceAccount = $this->config->getServiceAccount();
        $accessToken = $this->getAccessToken($serviceAccount);
        $endpoint = sprintf(self::FCM_V1_ENDPOINT, $serviceAccount['project_id']);
        $iconUrl = $this->config->getDefaultIconUrl();
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
            $aps = [
                'alert' => ['title' => $title, 'body' => $body],
                'sound' => 'default',
                'mutable-content' => 1
            ];

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
                            'aps' => $aps
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
        $this->applyDeviceTypeFilter($select);
        return $connection->fetchCol($select);
    }

    /**
     * Get active FCM tokens for a specific customer.
     *
     * @return string[]
     */
    public function getActiveTokensByCustomerId(int $customerId): array
    {
        $connection = $this->tokenResource->getConnection();
        $select = $connection->select()
            ->from($this->tokenResource->getMainTable(), 'token')
            ->where('is_active = ?', 1)
            ->where('customer_id = ?', $customerId);
        $this->applyDeviceTypeFilter($select);
        return $connection->fetchCol($select);
    }

    /**
     * Apply configured allowed device type filter to the SQL query select.
     *
     * @param \Magento\Framework\DB\Select $select
     * @param int|string|null $storeId
     * @return void
     */
    private function applyDeviceTypeFilter($select, $storeId = null): void
    {
        $allowed = $this->config->getAllowedDeviceTypes($storeId);
        if ($allowed === 'web') {
            $select->where('device_type = ?', 'web');
        } elseif ($allowed === 'api') {
            $select->where('device_type IN (?)', ['android', 'ios']);
        }
    }

    /**
     * Get accesstoken
     *
     * @param array $serviceAccount
     * @return string
     */
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

    /**
     * Signjwt
     *
     * @param array $claim
     * @param string $privateKeyPem
     * @return string
     */
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

    /**
     * Base64urlencode
     *
     * @param string $data
     * @return string
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Check if unregistered
     *
     * @param string $responseBody
     * @return bool
     */
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

    /**
     * Deactivatetoken
     *
     * @param string $token
     * @return void
     */
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
