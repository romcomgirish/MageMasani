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

namespace MageMasani\BannerSlider\Block\Widget;

use MageMasani\BannerSlider\Api\BannerRepositoryInterface;
use MageMasani\BannerSlider\Api\Data\BannerInterface;
use MageMasani\BannerSlider\Model\Config;
use MageMasani\BannerSlider\Model\ImageUploader;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Helper\Conditions;
use Magento\Widget\Model\Template\FilterEmulate;

/**
 * BannerSlider Widget Block Class
 */
class Slider extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = 'MageMasani_BannerSlider::widget/slider.phtml';

    /**
     * @var BannerRepositoryInterface
     */
    private BannerRepositoryInterface $bannerRepository;

    /**
     * @var SearchCriteriaBuilderFactory
     */
    private SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;

    /**
     * @var SortOrderBuilder
     */
    private SortOrderBuilder $sortOrderBuilder;

    /**
     * @var Conditions
     */
    private Conditions $conditions;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @var FilterEmulate
     */
    private FilterEmulate $filterEmulate;

    /**
     * @var ImageUploader
     */
    private ImageUploader $imageUploader;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private CategoryRepositoryInterface $categoryRepository;

    /**
     * @var DateTime
     */
    private DateTime $dateTime;

    /**
     * @var array|null
     */
    private ?array $bannerCollection = null;

    /**
     * @var string|null
     */
    private ?string $mediaBaseUrl = null;

    /**
     * @param Template\Context $context
     * @param BannerRepositoryInterface $bannerRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param SortOrderBuilder $sortOrderBuilder
     * @param SerializerInterface $serializer
     * @param Conditions $conditions
     * @param Config $config
     * @param FilterEmulate $filterEmulate
     * @param ImageUploader $imageUploader
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param DateTime $dateTime
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        BannerRepositoryInterface $bannerRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        SortOrderBuilder $sortOrderBuilder,
        SerializerInterface $serializer,
        Conditions $conditions,
        Config $config,
        FilterEmulate $filterEmulate,
        ImageUploader $imageUploader,
        ProductRepositoryInterface $productRepository,
        CategoryRepositoryInterface $categoryRepository,
        DateTime $dateTime,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->bannerRepository = $bannerRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->conditions = $conditions;
        $this->serializer = $serializer;
        $this->config = $config;
        $this->filterEmulate = $filterEmulate;
        $this->imageUploader = $imageUploader;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * Get list of active banners based on current slider id with sorting and scheduling checks.
     *
     * @return array
     */
    public function getBannerCollection(): array
    {
        if ($this->bannerCollection !== null) {
            return $this->bannerCollection;
        }

        $sliderId = (int) $this->getData('slider_id');
        if ($sliderId <= 0) {
            $this->bannerCollection = [];
            return $this->bannerCollection;
        }

        $sortOrder = $this->sortOrderBuilder
            ->setField('sort_order')
            ->setAscendingDirection()
            ->create();

        $searchCriteria = $this->searchCriteriaBuilderFactory->create()
            ->addFilter('slider_id', $sliderId, 'eq')
            ->addFilter('status', 1, 'eq')
            ->setSortOrders([$sortOrder])
            ->create();

        $banners = $this->bannerRepository->getList($searchCriteria)->getItems();
        $currentTime = $this->dateTime->gmtTimestamp();

        $data = [];
        foreach ($banners as $banner) {
            // Check active date scheduling if set
            $startDate = $banner->getStartDate();
            $endDate = $banner->getEndDate();

            if (!empty($startDate) && strtotime($startDate) > $currentTime) {
                continue;
            }
            if (!empty($endDate) && strtotime($endDate) < $currentTime) {
                continue;
            }

            $resourceType = (string) $banner->getResourceType();
            $resourcePath = (string) $banner->getResourcePath();

            $data[] = [
                'entity_id' => (int) $banner->getEntityId(),
                'slider_id' => (int) $banner->getSliderId(),
                'title' => (string) $banner->getTitle(),
                'resource_type' => $resourceType,
                'resource_path' => $this->checkResourceType($resourceType, $resourcePath),
                'alt_text' => (string) $banner->getAltText(),
                'status' => (int) $banner->getStatus(),
                'sort_order' => (int) $banner->getSortOrder(),
                'created_at' => (string) $banner->getCreatedAt(),
                'updated_at' => (string) $banner->getUpdatedAt(),
                'start_date' => (string) $banner->getStartDate(),
                'end_date' => (string) $banner->getEndDate(),
                'link_type' => (string) $banner->getLinkType(),
                'link_type_resource' => (string) $banner->getLinkTypeResource(),
                'link_url' => $this->resolveLinkUrl((string) $banner->getLinkType(), (string) $banner->getLinkTypeResource())
            ];
        }

        $this->bannerCollection = $data;
        return $this->bannerCollection;
    }

    /**
     * Check and format resource type.
     *
     * @param string $resourceType
     * @param string $resourcePath
     * @return string
     */
    public function checkResourceType(string $resourceType, string $resourcePath): string
    {
        return match ($resourceType) {
            'local_image' => $this->getMediaUrl($resourcePath),
            'custom_html' => (string) $this->filterEmulate->filter($resourcePath),
            default => $resourcePath,
        };
    }

    /**
     * Resolve target URL from banner link type and resource identifier.
     *
     * @param string $linkType
     * @param string $linkTypeResource
     * @return string
     */
    public function resolveLinkUrl(string $linkType, string $linkTypeResource): string
    {
        if (empty($linkTypeResource) || $linkType === '0') {
            return '#';
        }

        if ($linkType === 'link_type_custom') {
            return $linkTypeResource;
        }

        try {
            if ($linkType === 'link_type_product') {
                if (is_numeric($linkTypeResource)) {
                    $product = $this->productRepository->getById((int) $linkTypeResource);
                } else {
                    $product = $this->productRepository->get($linkTypeResource);
                }
                return $product->getProductUrl();
            }

            if ($linkType === 'link_type_category' && is_numeric($linkTypeResource)) {
                $category = $this->categoryRepository->get((int) $linkTypeResource);
                return $category->getUrl();
            }
        } catch (NoSuchEntityException) {
            return '#';
        }

        return $linkTypeResource;
    }

    /**
     * Get full path of image.
     *
     * @param string $imageSource
     * @return string
     */
    public function getMediaUrl(string $imageSource): string
    {
        if (empty($imageSource)) {
            return '';
        }

        try {
            if ($this->mediaBaseUrl === null) {
                $store = $this->_storeManager->getStore();
                $this->mediaBaseUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $this->imageUploader->getBasePath() . '/';
            }
            return $this->mediaBaseUrl . $imageSource;
        } catch (NoSuchEntityException) {
            return '';
        }
    }

    /**
     * Get slider options for Slick Carousel including responsive breakpoints.
     *
     * @return string
     */
    public function getSliderOptions(): string
    {
        $responsive = $this->getConditionsArray();

        $sliderAttribute = [
            'slidesToShow' => (int) ($this->getData('items_to_show') ?: 1),
            'speed' => (int) ($this->getData('sliding_speed') ?: 300),
            'autoplay' => $this->getData('autoplay') === '1',
            'autoplaySpeed' => (int) ($this->getData('autoplay_speed') ?: 3000),
            'fade' => $this->getData('animation_style') === 'fade',
            'arrows' => $this->getData('show_nav') === '1',
            'dots' => $this->getData('show_dots') === '1',
            'infinite' => true
        ];

        if (!empty($responsive)) {
            $sliderAttribute['responsive'] = $responsive;
        }

        return (string) $this->serializer->serialize($sliderAttribute);
    }

    /**
     * Fetches decoded conditions array for responsive breakpoints.
     *
     * @return array
     */
    public function getConditionsArray(): array
    {
        if ($this->getData('conditions_encoded')) {
            $condition = (array) $this->conditions->decode($this->getData('conditions_encoded'));
        } else {
            $condition = (array) $this->config->getSliderBreakPoints();
        }

        $condition = array_filter($condition);
        $data = [];
        foreach ($condition as $content) {
            if (isset($content['break_point'])) {
                $data[] = [
                    'breakpoint' => (int) $content['break_point'],
                    'settings' => [
                        'slidesToShow' => (int) ($content['slide_to_show'] ?? 1),
                        'slidesToScroll' => (int) ($content['slide_to_scroll'] ?? 1)
                    ]
                ];
            }
        }
        return $data;
    }

    /**
     * Fetches serialized conditions for backward compatibility.
     *
     * @return string
     */
    public function getConditionsSerialize(): string
    {
        return (string) $this->serializer->serialize($this->getConditionsArray());
    }
}
