<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface CampaignInterface extends ExtensibleDataInterface
{
    public const ENTITY_ID = 'entity_id';
    public const TITLE = 'title';
    public const BODY = 'body';
    public const CLICK_URL = 'click_url';
    public const IMAGE_URL = 'image_url';
    public const CUSTOM_EVENT = 'custom_event';
    public const NOTIFICATION_TYPE = 'notification_type';
    public const SCHEDULE_TO = 'schedule_to';
    public const ORDER_STATUS = 'order_status';
    public const STATUS = 'status';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * Get entity ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set entity ID
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Get Title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Set Title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title);

    /**
     * Get Body
     *
     * @return string|null
     */
    public function getBody();

    /**
     * Set Body
     *
     * @param string $body
     * @return $this
     */
    public function setBody(string $body);

    /**
     * Get Click URL
     *
     * @return string|null
     */
    public function getClickUrl();

    /**
     * Set Click URL
     *
     * @param string|null $clickUrl
     * @return $this
     */
    public function setClickUrl(?string $clickUrl);

    /**
     * Get Image URL
     *
     * @return string|null
     */
    public function getImageUrl();

    /**
     * Set Image URL
     *
     * @param string|null $imageUrl
     * @return $this
     */
    public function setImageUrl(?string $imageUrl);

    /**
     * Get Custom Event
     *
     * @return string|null
     */
    public function getCustomEvent();

    /**
     * Set Custom Event
     *
     * @param string|null $customEvent
     * @return $this
     */
    public function setCustomEvent(?string $customEvent);

    /**
     * Get Notification Type
     *
     * @return string|null
     */
    public function getNotificationType();

    /**
     * Set Notification Type
     *
     * @param string|null $notificationType
     * @return $this
     */
    public function setNotificationType(?string $notificationType);

    /**
     * Get Schedule To
     *
     * @return string|null
     */
    public function getScheduleTo();

    /**
     * Set Schedule To
     *
     * @param string|null $scheduleTo
     * @return $this
     */
    public function setScheduleTo(?string $scheduleTo);

    /**
     * Get Order Status
     *
     * @return string|null
     */
    public function getOrderStatus();

    /**
     * Set Order Status
     *
     * @param string|null $orderStatus
     * @return $this
     */
    public function setOrderStatus(?string $orderStatus);

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status);

    /**
     * Get created at
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt);

    /**
     * Get updated at
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt);
}
