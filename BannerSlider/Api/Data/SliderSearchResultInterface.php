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

namespace MageMasani\BannerSlider\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * mageMasani slider  interface
 */
interface SliderSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get items.
     *
     * @return \MageMasani\BannerSlider\Api\Data\SliderInterface[]
     */
    public function getItems();

    /**
     * Set items.
     *
     * @param \MageMasani\BannerSlider\Api\Data\SliderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
