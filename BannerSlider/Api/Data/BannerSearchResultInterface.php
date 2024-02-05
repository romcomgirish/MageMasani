<?php
namespace MageMasani\BannerSlider\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for mageMasani banner search results.
 */
interface BannerSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get items.
     *
     * @return \MageMasani\BannerSlider\Api\Data\BannerInterface[]
     */
    public function getItems();

    /**
     * Set items.
     *
     * @param \MageMasani\BannerSlider\Api\Data\BannerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
