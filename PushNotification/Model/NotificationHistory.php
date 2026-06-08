<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model;

use Magento\Framework\Model\AbstractModel;
use MageMasani\PushNotification\Model\ResourceModel\NotificationHistory as ResourceModel;

class NotificationHistory extends AbstractModel
{
    public const ENTITY_ID = 'entity_id';
    public const CAMPAIGN_ID = 'campaign_id';
    public const CUSTOMER_ID = 'customer_id';
    public const TITLE = 'title';
    public const BODY = 'body';
    public const CLICK_URL = 'click_url';
    public const IMAGE_URL = 'image_url';
    public const NOTIFICATION_TYPE = 'notification_type';
    public const IS_GLOBAL = 'is_global';
    public const TOKEN_COUNT = 'token_count';
    public const SUCCESS_COUNT = 'success_count';
    public const FAILURE_COUNT = 'failure_count';
    public const STATUS = 'status';
    public const ERROR_MESSAGE = 'error_message';
    public const SENT_AT = 'sent_at';

    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
