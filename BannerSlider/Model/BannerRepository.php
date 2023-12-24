<?php

namespace MageMasani\BannerSlider\Model;

use MageMasani\BannerSlider\Api\BannerRepositoryInterface;
use MageMasani\BannerSlider\Api\Data\BannerInterface;
use MageMasani\BannerSlider\Api\Data\BannerInterfaceFactory as ModelFactory;
use MageMasani\BannerSlider\Model\ResourceModel\Banner as ResourceModel;
use MageMasani\BannerSlider\Model\ResourceModel\Banner\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use MageMasani\BannerSlider\Api\Data\BannerSearchResultInterfaceFactory;
use Psr\Log\LoggerInterface;

class BannerRepository implements BannerRepositoryInterface
{

    /**
     * @var ModelFactory
     */
    private ModelFactory $modelFactory;
    /**
     * @var ResourceModel
     */
    private ResourceModel $resourceModel;
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;
    /**
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;
    /**
     * @var BannerInterface[]
     */
    protected array $objectCache;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var BannerSearchResultInterfaceFactory
     */
    private BannerSearchResultInterfaceFactory $bannerSearchResultFactory;

    /**
     * BannerRepository constructor.
     * @param ModelFactory $modelFactory
     * @param ResourceModel $resourceModel
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param BannerSearchResultInterfaceFactory $bannerSearchResultFactory
     * @param LoggerInterface $logger
     * @param array $objectCache
     */
    public function __construct(
        ModelFactory $modelFactory,
        ResourceModel $resourceModel,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        BannerSearchResultInterfaceFactory $bannerSearchResultFactory,
        LoggerInterface $logger,
        array $objectCache = []
    ) {
        $this->modelFactory = $modelFactory;
        $this->resourceModel = $resourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->bannerSearchResultFactory = $bannerSearchResultFactory;
        $this->logger = $logger;
        $this->objectCache = $objectCache;
    }

    /**
     * @inheritdoc
     */
    public function loadById(int $id, bool $loadFromCache = true)
    {
        $cachedObject = $this->getCachedObject('id', $id);
        if ($loadFromCache && $cachedObject) {
            return $cachedObject;
        } else {
            $model = $this->create();
            $this->resourceModel->load($model, $id);
            if (!$model->getEntityId()) {
                throw NoSuchEntityException::singleField('entity_id', $id);
            }
            $this->cacheObject('id', $id, $model);
            return $model;
        }
    }

    /**
     * @inheritdoc
     */
    public function create()
    {
        return $this->modelFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function save(BannerInterface $banner)
    {
        try {
            $this->resourceModel->save($banner);
            return $this->loadById($banner->getEntityId(), false);
        } catch (AlreadyExistsException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotSaveException(__('There was some error saving the banner'));
        }
    }

    /**
     * @inheritdoc
     */
    public function delete(BannerInterface $banner): bool
    {
        try {
            $this->resourceModel->delete($banner);
            $this->cacheObject('id', $banner->getEntityId(), null);
            return true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            throw new CouldNotDeleteException(__('There was some eror deleting the banner'));
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteById(int $id): bool
    {
        return $this->delete($this->loadById($id));
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResult = $this->bannerSearchResultFactory->create();
        $searchResult->setSearchCriteria($searchCriteria)
            ->setTotalCount($collection->getSize())
            ->setItems($collection->getItems());
        foreach ($searchResult->getItems() as $item) {
            $this->cacheObject('id', $item->getEntityId(), $item);
        }
        return $searchResult;
    }

    /**
     * @inheritdoc
     */
    public function getCollection(): \MageMasani\BannerSlider\Model\ResourceModel\Banner\Collection
    {
        return $this->collectionFactory->create();
    }

    /**
     * @inheritdoc
     */
    protected function cacheObject($type, $identifier, $object)
    {
        $cacheKey = $this->getCacheKey($type, $identifier);
        $this->objectCache[$cacheKey] = $object;
    }

    /**
     * @inheritdoc
     */
    protected function getCachedObject($type, $identifier)
    {
        $cacheKey = $this->getCacheKey($type, $identifier);
        return $this->objectCache[$cacheKey] ?? false;
    }

    /**
     * @inheritdoc
     */
    protected function getCacheKey($type, $identifier)
    {
        return $type . '_' . $identifier;
    }
}
