<?php
/**
 * MageMasani BannerSlider Module
 *
 * @category  MageMasani
 * @package   MageMasani_BannerSlider
 * @author    MageMasani <support@magemasani.com>
 * @copyright Copyright (c) MageMasani (https://www.magemasani.com/)
 * @license   GPL-3.0-or-later
 */

declare(strict_types=1);

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
