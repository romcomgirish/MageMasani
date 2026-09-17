<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Model Config
 */
class Config implements ConfigInterface
{
    /**
     * Required keys in Firebase configuration object
     *
     * @var string[]
     */
    private const FIREBASE_KEYS = [
        'apiKey',
        'authDomain',
        'projectId',
        'storageBucket',
        'messagingSenderId',
        'appId'
    ];

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;
    /**
     * @var array
     */
    private array $firebaseConfigCache = [];
    /**
     * @var array
     */
    private array $serviceAccountCache = [];

    /**
     * Initialize dependencies
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @return void
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Check if enabled
     *
     * @param $storeId 
     * @return bool
     */
    public function isEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if asyncenabled
     *
     * @param $storeId 
     * @return bool
     */
    public function isAsyncEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ASYNC,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get alloweddevicetypes
     *
     * @param $storeId 
     * @return string
     */
    public function getAllowedDeviceTypes($storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_ALLOWED_DEVICE_TYPES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get firebaseconfig
     *
     * @param $storeId 
     * @return array
     */
    public function getFirebaseConfig($storeId = null): array
    {
        $cacheKey = (string) $storeId;
        if (isset($this->firebaseConfigCache[$cacheKey])) {
            return $this->firebaseConfigCache[$cacheKey];
        }

        $empty = array_fill_keys(self::FIREBASE_KEYS, '');

        $json = trim((string) $this->scopeConfig->getValue(
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
                $config[$key] = (string) $decoded[$key];
            }
        }
        return $this->firebaseConfigCache[$cacheKey] = $config;
    }

    /**
     * Tolerantjsondecode
     *
     * @param string $raw
     * @return ?array
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
        $cleaned = preg_replace('!/\*.*?\*/!s', '', (string) $cleaned);
        $cleaned = preg_replace('!//[^\n\r]*!', '', (string) $cleaned);
        $cleaned = preg_replace('/([{,]\s*)([A-Za-z_][A-Za-z0-9_]*)\s*:/', '$1"$2":', (string) $cleaned);
        $cleaned = preg_replace_callback(
            '/:\s*\'((?:\\\\.|[^\'\\\\])*)\'/',
            static fn($m) => ': ' . json_encode($m[1], JSON_UNESCAPED_SLASHES),
            (string) $cleaned
        );
        $cleaned = preg_replace('/,(\s*[}\]])/', '$1', (string) $cleaned);

        $decoded = json_decode((string) $cleaned, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Get vapidkey
     *
     * @param $storeId 
     * @return string
     */
    public function getVapidKey($storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            self::XML_PATH_FIREBASE . 'vapid_key',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get defaulticonurl
     *
     * @param $storeId 
     * @return string
     */
    public function getDefaultIconUrl($storeId = null): string
    {
        $icon = (string) $this->scopeConfig->getValue(
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
     * Get serviceaccount
     *
     * @param $storeId 
     * @return array
     */
    public function getServiceAccount($storeId = null): array
    {
        $cacheKey = (string) $storeId;
        if (isset($this->serviceAccountCache[$cacheKey])) {
            return $this->serviceAccountCache[$cacheKey];
        }

        $json = trim((string) $this->scopeConfig->getValue(
            self::XML_PATH_FIREBASE . 'service_account_json',
            ScopeInterface::SCOPE_STORE,
            $storeId
        ));
        if ($json === '') {
            throw new \RuntimeException('Service Account JSON is not configured.');
        }

        $data = json_decode($json, true);
        if (
            !is_array($data)
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

    /**
     * Get abandoned cart inactivity delay in minutes
     *
     * @param int|string|null $storeId
     * @return int
     */
    public function getAbandonedCartDelayMinutes($storeId = null): int
    {
        $delay = (int) $this->scopeConfig->getValue(
            self::XML_PATH_ABANDONED_CART_DELAY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $delay > 0 ? $delay : 60;
    }

    /**
     * Get abandoned cart max age in days
     *
     * @param int|string|null $storeId
     * @return int
     */
    public function getAbandonedCartMaxAgeDays($storeId = null): int
    {
        $days = (int) $this->scopeConfig->getValue(
            self::XML_PATH_ABANDONED_CART_MAX_AGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $days > 0 ? $days : 7;
    }
}
