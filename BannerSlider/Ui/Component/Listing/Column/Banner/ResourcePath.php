<?php

namespace MageMasani\BannerSlider\Ui\Component\Listing\Column\Banner;

use MageMasani\BannerSlider\BannerImageUploader;
use MageMasani\BannerSlider\Model\ImageUploader;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\App\ObjectManager;

/**
 * Banner ResourcePath Class
 */
class ResourcePath extends Column
{
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Repository
     */
    private Repository $assetRepository;

    /**
     * @var ImageUploader|BannerImageUploader|mixed
     */
    private ImageUploader $imageUploader;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param Repository $assetRepository
     * @param ImageUploader|null $imageUploader
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface      $context,
        UiComponentFactory    $uiComponentFactory,
        StoreManagerInterface $storeManager,
        Repository $assetRepository,
        ImageUploader $imageUploader = null,
        array                 $components = [],
        array                 $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
        $this->assetRepository = $assetRepository;
        $this->imageUploader = $imageUploader ?:  ObjectManager::getInstance()->get(BannerImageUploader::class);
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (!empty($item['resource_type'] == 'local_image')) {
                    $item['resource_path'] = $this->setLocalImage($item);
                } elseif (!empty($item['resource_type'] == 'custom_html')) {
                    $item['resource_path'] = $this->getCustomHtml();
                } elseif (!empty($item['resource_type'] == 'youtube_video')) {
                    $item['resource_path'] = $this->getYoutubeVideo();
                } elseif (!empty($item['resource_type'] == 'external_image')) {
                    $item['resource_path'] = $this->getExternalImage($item);
                }
            }
        }
        return $dataSource;
    }

    /**
     * Process
     *
     * @param array $item
     * @return string
     * @throws NoSuchEntityException
     */
    public function setLocalImage(array $item): string
    {
        $resourcePath = $item['resource_path'];
        if ($resourcePath) {
            $path = $this->imageUploader->getBasePath().'/'.$resourcePath;
            $imageUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $path;
            return sprintf(
                '<img style="width: 100px; height: auto;" src="%s" alt="%s" />',
                $imageUrl,
                $resourcePath
            );
        } else {
            return __('No image found');
        }
    }

    /**
     * Get custom html logo
     *
     * @return string
     */
    public function getCustomHtml(): string
    {
        return sprintf(
            '<img style="width: 100px; height: auto;" src="%s" alt="%s" />',
            $this->assetRepository->getUrl('MageMasani_BannerSlider::images/editior.png'),
            '&lt;HTML /&gt;'
        );
    }

    /**
     * Get youtube video logo.
     *
     * @return string
     */
    public function getYoutubeVideo(): string
    {
        return sprintf(
            '<img style="width: 100px; height: auto;" src="%s" alt="%s" />',
            $this->assetRepository->getUrl('MageMasani_BannerSlider::images/Youtube.png'),
            __('YouTube')
        );
    }

    /**
     * Get external image logo.
     *
     * @param array $item
     * @return string
     */
    public function getExternalImage(array $item): string
    {
        return sprintf(
            '<img style="width: 100px; height: auto;" src="%s" alt="%s" />',
            $item['resource_path'],
            $item['resource_path']
        );
    }
}
