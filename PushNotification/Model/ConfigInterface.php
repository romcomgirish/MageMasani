<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model;

/**
 * Interface ConfigInterface
 *
 * @api
 */
interface ConfigInterface
{
    /**
     * Xml path enabled constant
     *
     * @var string
     */
    public const XML_PATH_ENABLED = 'pushnotification/general/enabled';
    /**
     * Xml path async constant
     *
     * @var string
     */
    public const XML_PATH_ASYNC = 'pushnotification/general/async_enabled';
    /**
     * Xml path allowed device types constant
     *
     * @var string
     */
    public const XML_PATH_ALLOWED_DEVICE_TYPES = 'pushnotification/general/allowed_device_types';
    /**
     * Xml path firebase constant
     *
     * @var string
     */
    public const XML_PATH_FIREBASE = 'pushnotification/firebase/';
    /**
     * Xml path abandoned cart delay constant
     *
     * @var string
     */
    public const XML_PATH_ABANDONED_CART_DELAY = 'pushnotification/abandoned_cart/delay_minutes';
    /**
     * Xml path abandoned cart max age constant
     *
     * @var string
     */
    public const XML_PATH_ABANDONED_CART_MAX_AGE = 'pushnotification/abandoned_cart/max_age_days';

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

    /**
     * Get abandoned cart inactivity delay in minutes
     *
     * @param int|string|null $storeId
     * @return int
     */
    public function getAbandonedCartDelayMinutes($storeId = null): int;

    /**
     * Get abandoned cart max age in days
     *
     * @param int|string|null $storeId
     * @return int
     */
    public function getAbandonedCartMaxAgeDays($storeId = null): int;
}
