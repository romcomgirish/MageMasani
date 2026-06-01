<?php
declare(strict_types=1);

namespace MageMasani\WebPushNotification\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    public const XML_PATH_ENABLED  = 'webpushnotification/general/enabled';
    public const XML_PATH_FIREBASE = 'webpushnotification/firebase/';

    private const FIREBASE_KEYS = [
        'apiKey', 'authDomain', 'projectId', 'storageBucket', 'messagingSenderId', 'appId'
    ];

    private StoreManagerInterface $storeManager;

    /** @var array<string,array<string,string>> */
    private array $firebaseConfigCache = [];

    /** @var array<string,array> */
    private array $serviceAccountCache = [];

    public function __construct(Context $context, StoreManagerInterface $storeManager)
    {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    public function isEnabled($storeId = null): bool
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getFirebaseConfig($storeId = null): array
    {
        $cacheKey = (string)$storeId;
        if (isset($this->firebaseConfigCache[$cacheKey])) {
            return $this->firebaseConfigCache[$cacheKey];
        }

        $empty = array_fill_keys(self::FIREBASE_KEYS, '');

        $json = trim((string)$this->scopeConfig->getValue(
            self::XML_PATH_FIREBASE . 'firebase_config_json',
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));

        if ($json === '') {
            return $this->firebaseConfigCache[$cacheKey] = $empty;
        }

        $decoded = $this->tolerantJsonDecode($json);
        if (!is_array($decoded)) {
            return $this->firebaseConfigCache[$cacheKey] = $empty;
        }

        $config = $empty;
        foreach (self::FIREBASE_KEYS as $key) {
            if (isset($decoded[$key]) && is_scalar($decoded[$key])) {
                $config[$key] = (string)$decoded[$key];
            }
        }
        return $this->firebaseConfigCache[$cacheKey] = $config;
    }

    /**
     * Accepts both strict JSON ({"apiKey":"..."}) and JS object literal
     * ({apiKey: "..."}) since admins typically copy/paste the latter from
     * the Firebase Console.
     */
    private function tolerantJsonDecode(string $raw): ?array
    {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $cleaned = trim($raw);
        $cleaned = preg_replace('/^(?:const|let|var)\s+\w+\s*=\s*/', '', $cleaned);
        $cleaned = rtrim($cleaned, ";\n\r\t ");
        $cleaned = preg_replace('!/\*.*?\*/!s', '', (string)$cleaned);
        $cleaned = preg_replace('!//[^\n\r]*!', '', (string)$cleaned);
        $cleaned = preg_replace('/([{,]\s*)([A-Za-z_][A-Za-z0-9_]*)\s*:/', '$1"$2":', (string)$cleaned);
        $cleaned = preg_replace_callback(
            '/:\s*\'((?:\\\\.|[^\'\\\\])*)\'/',
            static fn($m) => ': ' . json_encode($m[1], JSON_UNESCAPED_SLASHES),
            (string)$cleaned
        );
        $cleaned = preg_replace('/,(\s*[}\]])/', '$1', (string)$cleaned);

        $decoded = json_decode((string)$cleaned, true);
        return is_array($decoded) ? $decoded : null;
    }

    public function getVapidKey($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_FIREBASE . 'vapid_key',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getDefaultIconUrl($storeId = null): string
    {
        $icon = (string)$this->scopeConfig->getValue(
            self::XML_PATH_FIREBASE . 'icon',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($icon === '') {
            return '';
        }
        $mediaUrl = $this->storeManager->getStore($storeId)
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return rtrim($mediaUrl, '/') . '/webpush/' . ltrim($icon, '/');
    }

    /**
     * @return array{client_email:string,private_key:string,project_id:string,token_uri?:string}
     */
    public function getServiceAccount($storeId = null): array
    {
        $cacheKey = (string)$storeId;
        if (isset($this->serviceAccountCache[$cacheKey])) {
            return $this->serviceAccountCache[$cacheKey];
        }

        $json = trim((string)$this->scopeConfig->getValue(
            self::XML_PATH_FIREBASE . 'service_account_json',
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
        if ($json === '') {
            throw new \RuntimeException('Service Account JSON is not configured.');
        }

        $data = json_decode($json, true);
        if (!is_array($data)
            || empty($data['client_email'])
            || empty($data['private_key'])
            || empty($data['project_id'])
        ) {
            throw new \RuntimeException(
                'Service Account JSON is invalid (missing client_email, private_key or project_id).'
            );
        }

        return $this->serviceAccountCache[$cacheKey] = $data;
    }
}
