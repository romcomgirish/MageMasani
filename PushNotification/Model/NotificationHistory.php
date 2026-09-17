<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model;

use Magento\Framework\Model\AbstractModel;
use MageMasani\PushNotification\Model\ResourceModel\NotificationHistory as ResourceModel;

/**
 * Model NotificationHistory
 */
class NotificationHistory extends AbstractModel
{
    /**
     * Entity id constant
     *
     * @var string
     */
    public const ENTITY_ID = 'entity_id';
    /**
     * Campaign id constant
     *
     * @var string
     */
    public const CAMPAIGN_ID = 'campaign_id';
    /**
     * Customer id constant
     *
     * @var string
     */
    public const CUSTOMER_ID = 'customer_id';
    /**
     * Title constant
     *
     * @var string
     */
    public const TITLE = 'title';
    /**
     * Body constant
     *
     * @var string
     */
    public const BODY = 'body';
    /**
     * Click url constant
     *
     * @var string
     */
    public const CLICK_URL = 'click_url';
    /**
     * Image url constant
     *
     * @var string
     */
    public const IMAGE_URL = 'image_url';
    /**
     * Notification type constant
     *
     * @var string
     */
    public const NOTIFICATION_TYPE = 'notification_type';
    /**
     * Is global constant
     *
     * @var string
     */
    public const IS_GLOBAL = 'is_global';
    /**
     * Token count constant
     *
     * @var string
     */
    public const TOKEN_COUNT = 'token_count';
    /**
     * Success count constant
     *
     * @var string
     */
    public const SUCCESS_COUNT = 'success_count';
    /**
     * Failure count constant
     *
     * @var string
     */
    public const FAILURE_COUNT = 'failure_count';
    /**
     * Status constant
     *
     * @var string
     */
    public const STATUS = 'status';
    /**
     * Error message constant
     *
     * @var string
     */
    public const ERROR_MESSAGE = 'error_message';
    /**
     * Sent at constant
     *
     * @var string
     */
    public const SENT_AT = 'sent_at';

    /**
     * Resource model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
