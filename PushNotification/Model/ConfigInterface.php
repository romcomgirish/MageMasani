<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model;

interface ConfigInterface
{
    public const XML_PATH_ENABLED = 'pushnotification/general/enabled';
    public const XML_PATH_ASYNC = 'pushnotification/general/async_enabled';
    public const XML_PATH_ALLOWED_DEVICE_TYPES = 'pushnotification/general/allowed_device_types';
    public const XML_PATH_FIREBASE = 'pushnotification/firebase/';

    /**
     * Check if module is enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null): bool;

    /**
     * Check if asynchronous sending is enabled
     *
     * @param int|string|null $storeId
     * @return bool
     */
    public function isAsyncEnabled($storeId = null): bool;

    /**
     * Get allowed device types
     *
     * @param int|string|null $storeId
     * @return string
     */
    public function getAllowedDeviceTypes($storeId = null): string;

    /**
     * Get Firebase configurations
     *
     * @param int|string|null $storeId
     * @return array
     */
    public function getFirebaseConfig($storeId = null): array;

    /**
     * Get Vapid Key
     *
     * @param int|string|null $storeId
     * @return string
     */
    public function getVapidKey($storeId = null): string;

    /**
     * Get default icon URL
     *
     * @param int|string|null $storeId
     * @return string
     */
    public function getDefaultIconUrl($storeId = null): string;

    /**
     * Get Firebase Service Account info
     *
     * @param int|string|null $storeId
     * @return array
     */
    public function getServiceAccount($storeId = null): array;
}
