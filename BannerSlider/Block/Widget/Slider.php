<?php

namespace MageMasani\BannerSlider\Block\Widget;

use MageMasani\BannerSlider\Api\BannerRepositoryInterface;
use MageMasani\BannerSlider\BannerImageUploader;
use MageMasani\BannerSlider\Model\Config;
use MageMasani\BannerSlider\Model\ImageUploader;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Helper\Conditions;
use Magento\Widget\Model\Template\FilterEmulate;

/**
 * BannerSlider Class
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
     * @var ImageUploader|BannerImageUploader|mixed
     */
    private ImageUploader $imageUploader;

    /**
     * @param Template\Context $context
     * @param BannerRepositoryInterface $bannerRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     * @param SerializerInterface $serializer
     * @param Conditions $conditions
     * @param Config $config
     * @param FilterEmulate $filterEmulate
     * @param ImageUploader|null $imageUploader
     * @param array $data
     */
    public function __construct(
        Template\Context             $context,
        BannerRepositoryInterface    $bannerRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        SerializerInterface          $serializer,
        Conditions                   $conditions,
        Config                       $config,
        FilterEmulate                $filterEmulate,
        ImageUploader $imageUploader = null,
        array                        $data = []
    ) {
        parent::__construct($context, $data);
        $this->bannerRepository = $bannerRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->conditions = $conditions;
        $this->serializer = $serializer;
        $this->config = $config;
        $this->filterEmulate = $filterEmulate;
        $this->imageUploader = $imageUploader ?:  ObjectManager::getInstance()->get(BannerImageUploader::class);
    }

    /**
     * Get list of banners based on current slider id.
     *
     * @return array
     */
    public function getBannerCollection(): array
    {
        $data = [];
        $searchCriteria = $this->searchCriteriaBuilderFactory->create()
            ->addFilter('slider_id', (int)$this->getData('slider_id'), 'eq')
            ->create();
        $banners = $this->bannerRepository->getList($searchCriteria)->getItems();
        foreach ($banners as $banner) {
            $data[] = [
                'entity_id' => $banner['entity_id'],
                'slider_id' => $banner['slider_id'],
                'title' => $banner['title'],
                'resource_type' => $banner['resource_type'],
                'resource_path' => $this->checkResourceType($banner['resource_type'], $banner['resource_path']),
                'alt_text' => $banner['alt_text'],
                'status' => $banner['status'],
                'sort_order' => $banner['sort_order'],
                'created_at' => $banner['created_at'],
                'updated_at' => $banner['updated_at'],
                'start_date' => $banner['start_date'],
                'end_date' => $banner['end_date'],
                'link_type' => $banner['link_type']
            ];
        }
        return $data;
    }

    /**
     * Check resource type.
     *
     * @param string $resource_type
     * @param string $resource_path
     * @return string
     */
    public function checkResourceType(string $resource_type, string $resource_path): string
    {
        if ($resource_type == 'local_image') {
            return $this->getMediaUrl($resource_path);
        } elseif ($resource_type == 'custom_html') {
            return $this->filterEmulate->filter($resource_path);
        } else {
            return $resource_path;
        }
    }

    /**
     * Get full path of image.
     *
     * @param string $imageSource
     * @return string
     */
    public function getMediaUrl(string $imageSource): string
    {
        try {
            $store = $this->_storeManager->getStore();
            return $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA).$this->imageUploader->getBasePath().'/'.$imageSource;
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * Get slider options.
     *
     * @return bool|string
     */
    public function getSliderOptions(): bool|string
    {
        $sliderAttribute = [
            'slidesToShow' => $this->getData('items_to_show'),
            'speed' => $this->getData('sliding_speed'),
            'autoplay' => $this->getData('autoplay') === '1',
            'autoplaySpeed' => $this->getData('autoplay_speed'),
            'fade' => $this->getData('animation_style') === 'fade',
            'arrows' => $this->getData('show_nav') === '1',
            'dots' => $this->getData('show_dots') === '1',
            'infinite' => true
        ];
        return $this->serializer->serialize($sliderAttribute);
    }

    /**
     * Fetches `conditions` containing serialized items then turns them into DataObjects
     *
     * @return bool|string
     */
    public function getConditionsSerialize(): bool|string
    {

        if ($this->getData('conditions_encoded')) {
            $condition = $this->conditions->decode($this->getData('conditions_encoded'));
        } else {
            $condition = $this->config->getSliderBreakPoints();
        }
        $condition = array_filter($condition);
        $data = [];
        foreach ($condition as $content) {
            $data[] = ['breakpoint' => $content['break_point'],
                'settings' => [
                    'slidesToShow' => $content['slide_to_show'],
                    'slidesToScroll' => $content['slide_to_scroll']
                ]];
        }
        return $this->serializer->serialize($data);
    }
}
