<?php

namespace MageMasani\BannerSlider\Model\ResourceModel\Slider;

use MageMasani\BannerSlider\Model\Slider as Model;
use MageMasani\BannerSlider\Model\ResourceModel\Slider as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

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
