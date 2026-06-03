<?php

namespace MageMasani\BannerSliderGraphQl\Model\Resolver;

use MageMasani\BannerSlider\Api\BannerRepositoryInterface;
use MageMasani\BannerSlider\BannerImageUploader;
use MageMasani\BannerSlider\Model\ImageUploader;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\UrlInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplier\Filter;
use Magento\Store\Model\ScopeInterface;
use MageMasani\BannerSlider\Model\ConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Model\Template\FilterEmulate;
use MageMasani\BannerSlider\Api\Data\BannerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\ResourceConnection;

/**
 * Resolver fetches the data and formats it according to the GraphQL schema.
 *
 */
class BannerSlider implements ResolverInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    public SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var BannerRepositoryInterface
     */
    private BannerRepositoryInterface $bannerRepositoryRepository;

    /**
     * @var SortOrderBuilder
     */
    private SortOrderBuilder $sortOrderBuilder;

    /**
     * @var ServiceOutputProcessor
     */
    private ServiceOutputProcessor $serviceOutputProcessor;

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var FilterEmulate
     */
    private FilterEmulate $filterEmulate;

    /**
     * @var ImageUploader|BannerImageUploader|mixed
     */
    private ImageUploader $imageUploader;

    /**
     * @var ProductCollectionFactory
     */
    private ProductCollectionFactory $productCollectionFactory;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    /**
     * @var string|null
     */
    private ?string $mediaBaseUrl = null;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param BannerRepositoryInterface $bannerRepositoryRepository
     * @param SortOrderBuilder $sortOrderBuilder
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param FilterEmulate $filterEmulate
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ImageUploader|null $imageUploader
     * @param ResourceConnection|null $resourceConnection
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BannerRepositoryInterface $bannerRepositoryRepository,
        SortOrderBuilder $sortOrderBuilder,
        ServiceOutputProcessor $serviceOutputProcessor,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        FilterEmulate $filterEmulate,
        ProductCollectionFactory $productCollectionFactory,
        ?ImageUploader $imageUploader = null,
        ?ResourceConnection $resourceConnection = null
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->bannerRepositoryRepository = $bannerRepositoryRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->filterEmulate = $filterEmulate;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageUploader = $imageUploader ?: ObjectManager::getInstance()->get(BannerImageUploader::class);
        $this->resourceConnection = $resourceConnection ?: ObjectManager::getInstance()->get(ResourceConnection::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        try {
            if (!$this->scopeConfig->isSetFlag(ConfigInterface::MODULE_ENABLE, ScopeInterface::SCOPE_STORE)) {
                return [];
            }
            $this->validateArgs($args);
            $args[Filter::ARGUMENT_NAME][BannerInterface::STATUS] = ['eq' => 1];
            $searchCriteria = $this->searchCriteriaBuilder->build($field->getName(), $args);
            $searchCriteria->setCurrentPage($args['currentPage']);
            $searchCriteria->setPageSize($args['pageSize']);
            if (isset($args['sort'])) {
                $sort = $args['sort'];
                foreach ($sort as $key => $val) {
                    $sortOrder = $this->sortOrderBuilder->setField($key)->setDirection($val)->create();
                    $searchCriteria->setSortOrders([$sortOrder]);
                }
            }
            $searchResult = $this->bannerRepositoryRepository->getList($searchCriteria);

            $productIds = [];
            $productSkusInput = [];
            foreach ($searchResult->getItems() as $banner) {
                if ($banner->getLinkType() === 'link_type_product' && $banner->getLinkTypeResource()) {
                    $resource = $banner->getLinkTypeResource();
                    if (is_numeric($resource)) {
                        $productIds[] = (int) $resource;
                    } else {
                        $productSkusInput[] = $resource;
                    }
                }
            }

            $resolvedSkus = [];
            if (!empty($productIds) || !empty($productSkusInput)) {
                $connection = $this->resourceConnection->getConnection();
                $tableName = $this->resourceConnection->getTableName('catalog_product_entity');

                $select = $connection->select()->from($tableName, ['entity_id', 'sku']);

                $orConditions = [];
                if (!empty($productIds)) {
                    $orConditions[] = $connection->quoteInto('entity_id IN (?)', $productIds);
                }
                if (!empty($productSkusInput)) {
                    $orConditions[] = $connection->quoteInto('sku IN (?)', $productSkusInput);
                }

                if (count($orConditions) > 0) {
                    $select->where(implode(' OR ', $orConditions));
                }

                $productsData = $connection->fetchAll($select);
                foreach ($productsData as $productData) {
                    $resolvedSkus[$productData['entity_id']] = $productData['sku'];
                    $resolvedSkus[$productData['sku']] = $productData['sku'];
                }
            }

            $postData = [
                "items" => [],
                "total_count" => 0
            ];
            foreach ($searchResult->getItems() as $banner) {
                $customerData = [
                    'entity_id' => (int) $banner->getEntityId(),
                    'slider_id' => (int) $banner->getSliderId(),
                    'title' => $banner->getTitle(),
                    'resource_type' => $banner->getResourceType(),
                    'resource_path' => $banner->getResourcePath(),
                    'alt_text' => $banner->getAltText(),
                    'link_type' => $banner->getLinkType(),
                    'link_type_resource' => $banner->getLinkTypeResource(),
                    'status' => $banner->getStatus(),
                    'sort_order' => (int) $banner->getSortOrder(),
                    'start_date' => $banner->getStartDate(),
                    'end_date' => $banner->getEndDate(),
                    'created_at' => $banner->getCreatedAt(),
                    'updated_at' => $banner->getUpdatedAt()
                ];
                if ($banner->getLinkType() === 'link_type_product') {
                    $resource = $banner->getLinkTypeResource();
                    $customerData['sku'] = $resolvedSkus[$resource] ?? null;
                }
                if ($banner->getResourceType() === 'local_image') {
                    $customerData['resource_path'] = $this->setLocalImage($banner->getResourcePath());
                } elseif ($banner->getResourceType() === 'custom_html') {
                    $customerData['resource_path'] = $this->filterEmulate->filter($banner->getResourcePath());
                }
                $postData["items"][] = $customerData;
            }
            $postData['total_count'] = $searchResult->getTotalCount();
            return $postData;
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
    }

    /**
     * Validate Arguments
     *
     * @param array $args
     * @throws GraphQlInputException
     */
    private function validateArgs(array $args): void
    {
        if (isset($args['currentPage']) && $args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }

        if (isset($args['pageSize']) && $args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
    }

    /**
     * Set local image.
     *
     * @param string $resourcePath
     * @return string
     * @throws NoSuchEntityException
     */
    public function setLocalImage(string $resourcePath): string
    {
        if ($resourcePath) {
            if ($this->mediaBaseUrl === null) {
                $store = $this->storeManager->getStore();
                $this->mediaBaseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $this->imageUploader->getBasePath() . '/';
            }
            return $this->mediaBaseUrl . $resourcePath;
        } else {
            return (string) __('No image found');
        }
    }
}
