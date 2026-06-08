<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class NotificationHistory extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('magemasani_pushnotification_history', 'entity_id');
    }
}
