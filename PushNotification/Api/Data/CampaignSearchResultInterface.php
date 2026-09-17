<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface CampaignSearchResultInterface
 *
 * @api
 */
interface CampaignSearchResultInterface extends SearchResultsInterface
{
    /**
     * Get items.
     *
     * @return \MageMasani\PushNotification\Api\Data\CampaignInterface[]
     */
    public function getItems();

    /**
     * Set items.
     *
     * @param \MageMasani\PushNotification\Api\Data\CampaignInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
