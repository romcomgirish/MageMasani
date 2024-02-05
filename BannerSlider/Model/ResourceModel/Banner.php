<?php

namespace MageMasani\BannerSlider\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Banner ResourceModel Class
 */
class Banner extends AbstractDb
{
    public const MAIN_TABLE = 'magemasani_bannerslider_banner';
    public const ID_FIELD_NAME = 'entity_id';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, self::ID_FIELD_NAME);
    }
}
