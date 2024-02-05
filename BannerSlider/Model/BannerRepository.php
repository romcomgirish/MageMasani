<?php

namespace MageMasani\BannerSlider\Model;

use MageMasani\BannerSlider\Api\BannerRepositoryInterface;
use MageMasani\BannerSlider\Api\Data;
use MageMasani\BannerSlider\Model\ResourceModel\Banner as ResourceBanner;
use MageMasani\BannerSlider\Model\ResourceModel\Banner\CollectionFactory as BannerCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Banner BannerRepository Class
 */
class BannerRepository implements BannerRepositoryInterface
{
    /**
     * @var ResourceBanner
     */
    protected ResourceBanner $resource;

    /**
     * @var BannerFactory
     */
    protected BannerFactory $bannerFactory;

    /**
     * @var BannerCollectionFactory
     */
    protected BannerCollectionFactory $bannerCollectionFactory;

    /**
     * @var Data\SliderSearchResultInterfaceFactory
     */
    protected Data\SliderSearchResultInterfaceFactory $searchResultsFactory;

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
     * @param ResourceBanner $resource
     * @param BannerFactory $bannerFactory
     * @param BannerCollectionFactory $bannerCollectionFactory
     * @param Data\SliderSearchResultInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceBanner                           $resource,
        BannerFactory                           $bannerFactory,
        BannerCollectionFactory                 $bannerCollectionFactory,
        Data\SliderSearchResultInterfaceFactory $searchResultsFactory,
        DataObjectHelper                        $dataObjectHelper,
        DataObjectProcessor                     $dataObjectProcessor,
        JoinProcessorInterface                  $extensionAttributesJoinProcessor,
        CollectionProcessorInterface            $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->bannerFactory = $bannerFactory;
        $this->bannerCollectionFactory = $bannerCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritdoc
     */
    public function save(Data\BannerInterface $banner)
    {
        try {
            $this->resource->save($banner);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $banner;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->bannerCollectionFactory->create();
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
    public function delete(Data\BannerInterface $banner)
    {
        try {
            $this->resource->delete($banner);
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
        $slider = $this->bannerFactory->create();
        $this->resource->load($slider, $Id);
        if (!$slider->getId()) {
            throw new NoSuchEntityException(__('The Slider with the "%1" ID doesn\'t exist.', $Id));
        }
        return $slider;
    }
}
