<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Model NotificationHistory
 */
class NotificationHistory extends AbstractDb
{
    /**
     * Resource model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magemasani_pushnotification_history', 'entity_id');
    }
}
