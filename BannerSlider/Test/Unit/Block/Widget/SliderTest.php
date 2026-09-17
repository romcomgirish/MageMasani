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

namespace MageMasani\BannerSlider\Test\Unit\Block\Widget;

use MageMasani\BannerSlider\Api\BannerRepositoryInterface;
use MageMasani\BannerSlider\Api\Data\BannerInterface;
use MageMasani\BannerSlider\Api\Data\BannerSearchResultInterface;
use MageMasani\BannerSlider\Block\Widget\Slider;
use MageMasani\BannerSlider\Model\Config;
use MageMasani\BannerSlider\Model\ImageUploader;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Helper\Conditions;
use Magento\Widget\Model\Template\FilterEmulate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for Slider Widget Block
 */
class SliderTest extends TestCase
{
    /**
     * @var Slider
     */
    private Slider $sliderBlock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var BannerRepositoryInterface|MockObject
     */
    private $bannerRepositoryMock;

    /**
     * @var SearchCriteriaBuilderFactory|MockObject
     */
    private $searchCriteriaBuilderFactoryMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var SortOrderBuilder|MockObject
     */
    private $sortOrderBuilderMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    /**
     * @var Conditions|MockObject
     */
    private $conditionsMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var FilterEmulate|MockObject
     */
    private $filterEmulateMock;

    /**
     * @var ImageUploader|MockObject
     */
    private $imageUploaderMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepositoryMock;

    /**
     * @var CategoryRepositoryInterface|MockObject
     */
    private $categoryRepositoryMock;

    /**
     * @var DateTime|MockObject
     */
    private $dateTimeMock;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->contextMock->method('getStoreManager')->willReturn($this->storeManagerMock);
        $this->storeManagerMock->method('getStore')->willReturn($this->storeMock);

        $this->bannerRepositoryMock = $this->createMock(BannerRepositoryInterface::class);
        $this->searchCriteriaBuilderFactoryMock = $this->createMock(SearchCriteriaBuilderFactory::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->searchCriteriaBuilderFactoryMock->method('create')->willReturn($this->searchCriteriaBuilderMock);

        $this->sortOrderBuilderMock = $this->createMock(SortOrderBuilder::class);
        $this->sortOrderBuilderMock->method('setField')->willReturnSelf();
        $this->sortOrderBuilderMock->method('setAscendingDirection')->willReturnSelf();
        $this->sortOrderBuilderMock->method('create')->willReturn($this->createMock(SortOrder::class));

        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->conditionsMock = $this->createMock(Conditions::class);
        $this->configMock = $this->createMock(Config::class);
        $this->filterEmulateMock = $this->createMock(FilterEmulate::class);
        $this->imageUploaderMock = $this->createMock(ImageUploader::class);
        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->categoryRepositoryMock = $this->createMock(CategoryRepositoryInterface::class);
        $this->dateTimeMock = $this->createMock(DateTime::class);

        $this->sliderBlock = new Slider(
            $this->contextMock,
            $this->bannerRepositoryMock,
            $this->searchCriteriaBuilderFactoryMock,
            $this->sortOrderBuilderMock,
            $this->serializerMock,
            $this->conditionsMock,
            $this->configMock,
            $this->filterEmulateMock,
            $this->imageUploaderMock,
            $this->productRepositoryMock,
            $this->categoryRepositoryMock,
            $this->dateTimeMock
        );
    }

    /**
     * Test getBannerCollection returns empty array when slider_id is missing or <= 0
     */
    public function testGetBannerCollectionReturnsEmptyWhenNoSliderId(): void
    {
        $this->sliderBlock->setData('slider_id', 0);
        $result = $this->sliderBlock->getBannerCollection();
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test getBannerCollection active status and scheduling checks
     */
    public function testGetBannerCollectionWithActiveBanners(): void
    {
        $this->sliderBlock->setData('slider_id', 1);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilderMock->method('addFilter')->willReturnSelf();
        $this->searchCriteriaBuilderMock->method('setSortOrders')->willReturnSelf();
        $this->searchCriteriaBuilderMock->method('create')->willReturn($searchCriteriaMock);

        $bannerMock = $this->createMock(BannerInterface::class);
        $bannerMock->method('getEntityId')->willReturn(1);
        $bannerMock->method('getSliderId')->willReturn(1);
        $bannerMock->method('getTitle')->willReturn('Promo Banner');
        $bannerMock->method('getResourceType')->willReturn('external_image');
        $bannerMock->method('getResourcePath')->willReturn('https://example.com/banner.jpg');
        $bannerMock->method('getAltText')->willReturn('Promo Alt');
        $bannerMock->method('getStatus')->willReturn(1);
        $bannerMock->method('getSortOrder')->willReturn(1);
        $bannerMock->method('getStartDate')->willReturn(null);
        $bannerMock->method('getEndDate')->willReturn(null);
        $bannerMock->method('getLinkType')->willReturn('link_type_custom');
        $bannerMock->method('getLinkTypeResource')->willReturn('https://example.com/shop');

        $searchResultMock = $this->createMock(BannerSearchResultInterface::class);
        $searchResultMock->method('getItems')->willReturn([$bannerMock]);
        $this->bannerRepositoryMock->method('getList')->willReturn($searchResultMock);

        $this->dateTimeMock->method('gmtTimestamp')->willReturn(time());

        $result = $this->sliderBlock->getBannerCollection();
        $this->assertCount(1, $result);
        $this->assertEquals(1, $result[0]['entity_id']);
        $this->assertEquals('Promo Banner', $result[0]['title']);
        $this->assertEquals('https://example.com/shop', $result[0]['link_url']);
    }

    /**
     * Test checkResourceType formatting
     */
    public function testCheckResourceType(): void
    {
        $this->imageUploaderMock->method('getBasePath')->willReturn('MageMasani/banner');
        $this->storeMock->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_MEDIA)
            ->willReturn('https://example.com/media/');

        $localResult = $this->sliderBlock->checkResourceType('local_image', 'sample.jpg');
        $this->assertEquals('https://example.com/media/MageMasani/banner/sample.jpg', $localResult);

        $this->filterEmulateMock->method('filter')->with('<p>Hello</p>')->willReturn('<div>Hello</div>');
        $htmlResult = $this->sliderBlock->checkResourceType('custom_html', '<p>Hello</p>');
        $this->assertEquals('<div>Hello</div>', $htmlResult);

        $defaultResult = $this->sliderBlock->checkResourceType('external_image', 'https://cdn.test/img.png');
        $this->assertEquals('https://cdn.test/img.png', $defaultResult);
    }

    /**
     * Test resolveLinkUrl for product and category links
     */
    public function testResolveLinkUrl(): void
    {
        $customUrl = $this->sliderBlock->resolveLinkUrl('link_type_custom', 'https://example.com/promo');
        $this->assertEquals('https://example.com/promo', $customUrl);

        $productMock = $this->createMock(ProductInterface::class);
        $productMock->method('getProductUrl')->willReturn('https://example.com/products/summer-tee');
        $this->productRepositoryMock->method('getById')->with(10)->willReturn($productMock);

        $productUrl = $this->sliderBlock->resolveLinkUrl('link_type_product', '10');
        $this->assertEquals('https://example.com/products/summer-tee', $productUrl);

        $categoryMock = $this->createMock(CategoryInterface::class);
        $categoryMock->method('getUrl')->willReturn('https://example.com/apparel');
        $this->categoryRepositoryMock->method('get')->with(5)->willReturn($categoryMock);

        $categoryUrl = $this->sliderBlock->resolveLinkUrl('link_type_category', '5');
        $this->assertEquals('https://example.com/apparel', $categoryUrl);

        $emptyUrl = $this->sliderBlock->resolveLinkUrl('0', '');
        $this->assertEquals('#', $emptyUrl);
    }

    /**
     * Test getSliderOptions serializes slick options with responsive breakpoints
     */
    public function testGetSliderOptions(): void
    {
        $this->sliderBlock->setData('items_to_show', 2);
        $this->sliderBlock->setData('sliding_speed', 500);
        $this->sliderBlock->setData('autoplay', '1');

        $this->configMock->method('getSliderBreakPoints')->willReturn([
            ['break_point' => 768, 'slide_to_show' => 1, 'slide_to_scroll' => 1]
        ]);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($this->callback(function ($config) {
                return isset($config['slidesToShow'])
                    && $config['slidesToShow'] === 2
                    && isset($config['autoplay'])
                    && $config['autoplay'] === true
                    && isset($config['responsive']);
            }))
            ->willReturn('{"slidesToShow":2,"autoplay":true}');

        $result = $this->sliderBlock->getSliderOptions();
        $this->assertEquals('{"slidesToShow":2,"autoplay":true}', $result);
    }
}
