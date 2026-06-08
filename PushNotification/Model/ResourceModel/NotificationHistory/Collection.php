<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model\ResourceModel\NotificationHistory;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MageMasani\PushNotification\Model\NotificationHistory as HistoryModel;
use MageMasani\PushNotification\Model\ResourceModel\NotificationHistory as HistoryResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(HistoryModel::class, HistoryResource::class);
    }
}
