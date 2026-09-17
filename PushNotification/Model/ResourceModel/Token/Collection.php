<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model\ResourceModel\Token;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MageMasani\PushNotification\Model\Token as TokenModel;
use MageMasani\PushNotification\Model\ResourceModel\Token as TokenResource;

/**
 * Collection for  resource model
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Resource model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(TokenModel::class, TokenResource::class);
    }
}
