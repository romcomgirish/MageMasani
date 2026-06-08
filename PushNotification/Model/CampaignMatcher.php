<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model;

use MageMasani\PushNotification\Model\ResourceModel\Campaign\CollectionFactory as CampaignCollectionFactory;
use MageMasani\PushNotification\Api\Data\CampaignInterface;

class CampaignMatcher
{
    /**
     * @var CampaignCollectionFactory
     */
    private CampaignCollectionFactory $collectionFactory;

    /**
     * @param CampaignCollectionFactory $collectionFactory
     */
    public function __construct(CampaignCollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Find enabled campaigns matching the given event constant and optional order status.
     *
     * @param string $eventConstant The custom_event value (e.g. 'order_status')
     * @param string|null $orderStatus The specific order status to match (e.g. 'processing')
     * @return CampaignInterface[]
     */
    public function getMatchingCampaigns(string $eventConstant, ?string $orderStatus = null): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(CampaignInterface::STATUS, 1);
        $collection->addFieldToFilter(CampaignInterface::NOTIFICATION_TYPE, 'event');
        $collection->addFieldToFilter(CampaignInterface::CUSTOM_EVENT, $eventConstant);

        if ($orderStatus !== null) {
            $collection->addFieldToFilter(CampaignInterface::ORDER_STATUS, $orderStatus);
        }

        return $collection->getItems();
    }
}
