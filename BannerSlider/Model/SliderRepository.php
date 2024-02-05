<?php

namespace MageMasani\BannerSlider\Model;

use MageMasani\BannerSlider\Api\SliderRepositoryInterface;
use MageMasani\BannerSlider\Api\Data;
use MageMasani\BannerSlider\Model\ResourceModel\Slider as ResourceSlider;
use MageMasani\BannerSlider\Model\ResourceModel\Slider\CollectionFactory as SliderCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;

/**
 * Slider SliderRepository Class
 */
class SliderRepository implements SliderRepositoryInterface
{
    /**
     * @var ResourceSlider
     */
    protected ResourceSlider $resource;

    /**
     * @var SliderFactory
     */
    protected SliderFactory $sliderFactory;

    /**
     * @var SliderCollectionFactory
     */
    protected SliderCollectionFactory $sliderCollectionFactory;

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
     * @param ResourceSlider $resource
     * @param SliderFactory $sliderFactory
     * @param SliderCollectionFactory $sliderCollectionFactory
     * @param Data\SliderSearchResultInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceSlider                           $resource,
        SliderFactory                           $sliderFactory,
        SliderCollectionFactory                 $sliderCollectionFactory,
        Data\SliderSearchResultInterfaceFactory $searchResultsFactory,
        DataObjectHelper                        $dataObjectHelper,
        DataObjectProcessor                     $dataObjectProcessor,
        JoinProcessorInterface                  $extensionAttributesJoinProcessor,
        CollectionProcessorInterface            $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->sliderFactory = $sliderFactory;
        $this->sliderCollectionFactory = $sliderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritdoc
     */
    public function save(Data\SliderInterface $slider)
    {
        try {
            $this->resource->save($slider);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $slider;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->sliderCollectionFactory->create();
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
    public function delete(Data\SliderInterface $slider)
    {
        try {
            $this->resource->delete($slider);
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
        $slider = $this->sliderFactory->create();
        $this->resource->load($slider, $Id);
        if (!$slider->getId()) {
            throw new NoSuchEntityException(__('The Slider with the "%1" ID doesn\'t exist.', $Id));
        }
        return $slider;
    }
}
