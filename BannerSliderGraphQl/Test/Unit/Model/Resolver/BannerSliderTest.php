<?php
/**
 * MageMasani BannerSliderGraphQl Module
 *
 * @category  MageMasani
 * @package   MageMasani_BannerSliderGraphQl
 * @author    MageMasani <support@magemasani.com>
 * @copyright Copyright (c) MageMasani (https://www.magemasani.com/)
 * @license   GPL-3.0-or-later
 */

declare(strict_types=1);

namespace MageMasani\BannerSliderGraphQl\Test\Unit\Model\Resolver;

use MageMasani\BannerSlider\Api\BannerRepositoryInterface;
use MageMasani\BannerSlider\Api\Data\BannerInterface;
use MageMasani\BannerSlider\Api\Data\BannerSearchResultInterface;
use MageMasani\BannerSlider\Model\ConfigInterface;
use MageMasani\BannerSlider\Model\ImageUploader;
use MageMasani\BannerSliderGraphQl\Model\Resolver\BannerSlider;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\UrlInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Model\Template\FilterEmulate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for BannerSlider GraphQL Resolver
 */
class BannerSliderTest extends TestCase
{
    /**
     * @var BannerSlider
     */
    private BannerSlider $resolver;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var BannerRepositoryInterface|MockObject
     */
    private $bannerRepositoryMock;

    /**
     * @var SortOrderBuilder|MockObject
     */
    private $sortOrderBuilderMock;

    /**
     * @var ServiceOutputProcessor|MockObject
     */
    private $serviceOutputProcessorMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var StoreInterface|MockObject
     */
    private $storeMock;

    /**
     * @var FilterEmulate|MockObject
     */
    private $filterEmulateMock;

    /**
     * @var ProductCollectionFactory|MockObject
     */
    private $productCollectionFactoryMock;

    /**
     * @var ImageUploader|MockObject
     */
    private $imageUploaderMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * Set up mocks
     */
    protected function setUp(): void
    {
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->bannerRepositoryMock = $this->createMock(BannerRepositoryInterface::class);
        $this->sortOrderBuilderMock = $this->createMock(SortOrderBuilder::class);
        $this->serviceOutputProcessorMock = $this->createMock(ServiceOutputProcessor::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->storeManagerMock->method('getStore')->willReturn($this->storeMock);

        $this->filterEmulateMock = $this->createMock(FilterEmulate::class);
        $this->productCollectionFactoryMock = $this->createMock(ProductCollectionFactory::class);
        $this->imageUploaderMock = $this->createMock(ImageUploader::class);
        $this->resourceConnectionMock = $this->createMock(ResourceConnection::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->resourceConnectionMock->method('getConnection')->willReturn($this->connectionMock);
        $this->resourceConnectionMock->method('getTableName')->willReturn('catalog_product_entity');

        $this->resolver = new BannerSlider(
            $this->searchCriteriaBuilderMock,
            $this->bannerRepositoryMock,
            $this->sortOrderBuilderMock,
            $this->serviceOutputProcessorMock,
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->filterEmulateMock,
            $this->productCollectionFactoryMock,
            $this->imageUploaderMock,
            $this->resourceConnectionMock
        );
    }

    /**
     * Test resolve returns empty array when module is disabled in config
     */
    public function testResolveReturnsEmptyWhenModuleDisabled(): void
    {
        $this->scopeConfigMock->expects($this->once())
            ->method('isSetFlag')
            ->with(ConfigInterface::MODULE_ENABLE, ScopeInterface::SCOPE_STORE)
            ->willReturn(false);

        $fieldMock = $this->createMock(Field::class);
        $contextMock = $this->createMock(ContextInterface::class);
        $infoMock = $this->createMock(ResolveInfo::class);

        $result = $this->resolver->resolve($fieldMock, $contextMock, $infoMock, null, []);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test resolve throws GraphQlInputException for invalid currentPage
     */
    public function testResolveThrowsExceptionForInvalidCurrentPage(): void
    {
        $this->expectException(GraphQlInputException::class);

        $this->scopeConfigMock->method('isSetFlag')->willReturn(true);

        $fieldMock = $this->createMock(Field::class);
        $contextMock = $this->createMock(ContextInterface::class);
        $infoMock = $this->createMock(ResolveInfo::class);

        $this->resolver->resolve($fieldMock, $contextMock, $infoMock, null, ['currentPage' => 0]);
    }

    /**
     * Test resolve successfully executes and resolves items
     */
    public function testResolveSuccess(): void
    {
        $this->scopeConfigMock->method('isSetFlag')->willReturn(true);

        $fieldMock = $this->createMock(Field::class);
        $fieldMock->method('getName')->willReturn('BannerSliderInfo');
        $contextMock = $this->createMock(ContextInterface::class);
        $infoMock = $this->createMock(ResolveInfo::class);

        $searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->searchCriteriaBuilderMock->method('build')->willReturn($searchCriteriaMock);

        $bannerMock = $this->createMock(BannerInterface::class);
        $bannerMock->method('getEntityId')->willReturn(1);
        $bannerMock->method('getSliderId')->willReturn(1);
        $bannerMock->method('getTitle')->willReturn('Main Banner');
        $bannerMock->method('getResourceType')->willReturn('local_image');
        $bannerMock->method('getResourcePath')->willReturn('banner1.jpg');
        $bannerMock->method('getAltText')->willReturn('Main Alt');
        $bannerMock->method('getLinkType')->willReturn('link_type_product');
        $bannerMock->method('getLinkTypeResource')->willReturn('100');
        $bannerMock->method('getStatus')->willReturn(1);
        $bannerMock->method('getSortOrder')->willReturn(1);
        $bannerMock->method('getStartDate')->willReturn('2026-01-01');
        $bannerMock->method('getEndDate')->willReturn('2026-12-31');
        $bannerMock->method('getCreatedAt')->willReturn('2026-01-01');
        $bannerMock->method('getUpdatedAt')->willReturn('2026-01-01');

        $searchResultMock = $this->createMock(BannerSearchResultInterface::class);
        $searchResultMock->method('getItems')->willReturn([$bannerMock]);
        $searchResultMock->method('getTotalCount')->willReturn(1);
        $this->bannerRepositoryMock->method('getList')->willReturn($searchResultMock);

        $selectMock = $this->createMock(Select::class);
        $selectMock->method('from')->willReturnSelf();
        $selectMock->method('where')->willReturnSelf();
        $this->connectionMock->method('select')->willReturn($selectMock);
        $this->connectionMock->method('quoteInto')->willReturn('entity_id IN (100)');
        $this->connectionMock->method('fetchAll')->willReturn([
            ['entity_id' => '100', 'sku' => 'SKU-BANNER-100']
        ]);

        $this->imageUploaderMock->method('getBasePath')->willReturn('MageMasani/banner');
        $this->storeMock->method('getBaseUrl')->willReturn('https://example.com/media/');

        $result = $this->resolver->resolve($fieldMock, $contextMock, $infoMock, null, ['currentPage' => 1, 'pageSize' => 5]);
        $this->assertEquals(1, $result['total_count']);
        $this->assertCount(1, $result['items']);
        $this->assertEquals('SKU-BANNER-100', $result['items'][0]['sku']);
        $this->assertEquals('https://example.com/media/MageMasani/banner/banner1.jpg', $result['items'][0]['resource_path']);
    }
}
