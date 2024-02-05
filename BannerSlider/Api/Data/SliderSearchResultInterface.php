<?php
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
