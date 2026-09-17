<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model\ResourceModel\Campaign;

use MageMasani\PushNotification\Model\Campaign as Model;
use MageMasani\PushNotification\Model\ResourceModel\Campaign as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Collection for  resource model
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    /**
     * Resource model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
