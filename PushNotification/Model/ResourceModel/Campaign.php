<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Model Campaign
 */
class Campaign extends AbstractDb
{
    /**
     * Main table constant
     *
     * @var string
     */
    public const MAIN_TABLE = 'magemasani_pushnotification_campaign';
    /**
     * Id field name constant
     *
     * @var string
     */
    public const ID_FIELD_NAME = 'entity_id';

    /**
     * Resource model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, self::ID_FIELD_NAME);
    }
}
