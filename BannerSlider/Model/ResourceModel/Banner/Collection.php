<?php

namespace MageMasani\BannerSlider\Model\ResourceModel\Banner;

use MageMasani\BannerSlider\Model\Banner as Model;
use MageMasani\BannerSlider\Model\ResourceModel\Banner as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Banner Collection Class
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Initialization here
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
