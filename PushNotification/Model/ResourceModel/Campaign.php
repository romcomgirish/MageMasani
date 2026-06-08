<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Campaign extends AbstractDb
{
    public const MAIN_TABLE = 'magemasani_pushnotification_campaign';
    public const ID_FIELD_NAME = 'entity_id';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, self::ID_FIELD_NAME);
    }
}
