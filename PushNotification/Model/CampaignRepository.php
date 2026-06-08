<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model;

use MageMasani\PushNotification\Api\CampaignRepositoryInterface;
use MageMasani\PushNotification\Api\Data;
use MageMasani\PushNotification\Model\ResourceModel\Campaign as ResourceCampaign;
use MageMasani\PushNotification\Model\ResourceModel\Campaign\CollectionFactory as CampaignCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;

class CampaignRepository implements CampaignRepositoryInterface
{
    /**
     * @var ResourceCampaign
     */
    protected ResourceCampaign $resource;

    /**
     * @var CampaignFactory
     */
    protected CampaignFactory $campaignFactory;

    /**
     * @var CampaignCollectionFactory
     */
    protected CampaignCollectionFactory $campaignCollectionFactory;

    /**
     * @var Data\CampaignSearchResultInterfaceFactory
     */
    protected Data\CampaignSearchResultInterfaceFactory $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected DataObjectHelper $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected DataObjectProcessor $dataObjectProcessor;

    /**
     * @var JoinProcessorInterface
     */
    private JoinProcessorInterface $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;

    /**
     * @param ResourceCampaign $resource
     * @param CampaignFactory $campaignFactory
     * @param CampaignCollectionFactory $campaignCollectionFactory
     * @param Data\CampaignSearchResultInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceCampaign $resource,
        CampaignFactory $campaignFactory,
        CampaignCollectionFactory $campaignCollectionFactory,
        Data\CampaignSearchResultInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->campaignFactory = $campaignFactory;
        $this->campaignCollectionFactory = $campaignCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritdoc
     */
    public function save(Data\CampaignInterface $campaign)
    {
        try {
            $this->resource->save($campaign);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $campaign;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->campaignCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $this->extensionAttributesJoinProcessor->process($collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * @inheritdoc
     */
    public function delete(Data\CampaignInterface $campaign)
    {
        try {
            $this->resource->delete($campaign);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getById($Id)
    {
        $campaign = $this->campaignFactory->create();
        $this->resource->load($campaign, $Id);
        if (!$campaign->getId()) {
            throw new NoSuchEntityException(__('The Campaign with the "%1" ID doesn\'t exist.', $Id));
        }
        return $campaign;
    }
}
