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

namespace MageMasani\BannerSlider\Test\Unit\Model;

use Exception;
use MageMasani\BannerSlider\Api\Data\BannerInterface;
use MageMasani\BannerSlider\Api\Data\BannerSearchResultInterface;
use MageMasani\BannerSlider\Api\Data\BannerSearchResultInterfaceFactory;
use MageMasani\BannerSlider\Model\Banner;
use MageMasani\BannerSlider\Model\BannerFactory;
use MageMasani\BannerSlider\Model\BannerRepository;
use MageMasani\BannerSlider\Model\ResourceModel\Banner as ResourceBanner;
use MageMasani\BannerSlider\Model\ResourceModel\Banner\Collection as BannerCollection;
use MageMasani\BannerSlider\Model\ResourceModel\Banner\CollectionFactory as BannerCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for BannerRepository
 */
class BannerRepositoryTest extends TestCase
{
    /**
     * @var BannerRepository
     */
    private BannerRepository $repository;

    /**
     * @var ResourceBanner|MockObject
     */
    private $resourceMock;

    /**
     * @var BannerFactory|MockObject
     */
    private $bannerFactoryMock;

    /**
     * @var BannerCollectionFactory|MockObject
     */
    private $bannerCollectionFactoryMock;

    /**
     * @var BannerSearchResultInterfaceFactory|MockObject
     */
    private $searchResultsFactoryMock;

    /**
     * @var DataObjectHelper|MockObject
     */
    private $dataObjectHelperMock;

    /**
     * @var DataObjectProcessor|MockObject
     */
    private $dataObjectProcessorMock;

    /**
     * @var JoinProcessorInterface|MockObject
     */
    private $extensionAttributesJoinProcessorMock;

    /**
     * @var CollectionProcessorInterface|MockObject
     */
    private $collectionProcessorMock;

    /**
     * Set up dependencies
     */
    protected function setUp(): void
    {
        $this->resourceMock = $this->createMock(ResourceBanner::class);
        $this->bannerFactoryMock = $this->createMock(BannerFactory::class);
        $this->bannerCollectionFactoryMock = $this->createMock(BannerCollectionFactory::class);
        $this->searchResultsFactoryMock = $this->createMock(BannerSearchResultInterfaceFactory::class);
        $this->dataObjectHelperMock = $this->createMock(DataObjectHelper::class);
        $this->dataObjectProcessorMock = $this->createMock(DataObjectProcessor::class);
        $this->extensionAttributesJoinProcessorMock = $this->createMock(JoinProcessorInterface::class);
        $this->collectionProcessorMock = $this->createMock(CollectionProcessorInterface::class);

        $this->repository = new BannerRepository(
            $this->resourceMock,
            $this->bannerFactoryMock,
            $this->bannerCollectionFactoryMock,
            $this->searchResultsFactoryMock,
            $this->dataObjectHelperMock,
            $this->dataObjectProcessorMock,
            $this->extensionAttributesJoinProcessorMock,
            $this->collectionProcessorMock
        );
    }

    /**
     * Test save banner successfully
     */
    public function testSaveSuccess(): void
    {
        $bannerMock = $this->createMock(BannerInterface::class);
        $this->resourceMock->expects($this->once())->method('save')->with($bannerMock);

        $result = $this->repository->save($bannerMock);
        $this->assertSame($bannerMock, $result);
    }

    /**
     * Test save banner throws CouldNotSaveException on failure
     */
    public function testSaveThrowsCouldNotSaveException(): void
    {
        $this->expectException(CouldNotSaveException::class);

        $bannerMock = $this->createMock(BannerInterface::class);
        $this->resourceMock->expects($this->once())
            ->method('save')
            ->with($bannerMock)
            ->willThrowException(new Exception('Database error'));

        $this->repository->save($bannerMock);
    }

    /**
     * Test getById returns Banner
     */
    public function testGetByIdSuccess(): void
    {
        $bannerMock = $this->createMock(Banner::class);
        $bannerMock->method('getId')->willReturn(1);

        $this->bannerFactoryMock->method('create')->willReturn($bannerMock);
        $this->resourceMock->expects($this->once())->method('load')->with($bannerMock, 1);

        $result = $this->repository->getById(1);
        $this->assertSame($bannerMock, $result);
    }

    /**
     * Test getById throws NoSuchEntityException when entity is not found
     */
    public function testGetByIdThrowsNoSuchEntityException(): void
    {
        $this->expectException(NoSuchEntityException::class);

        $bannerMock = $this->createMock(Banner::class);
        $bannerMock->method('getId')->willReturn(null);

        $this->bannerFactoryMock->method('create')->willReturn($bannerMock);
        $this->resourceMock->expects($this->once())->method('load')->with($bannerMock, 999);

        $this->repository->getById(999);
    }

    /**
     * Test delete banner
     */
    public function testDeleteSuccess(): void
    {
        $bannerMock = $this->createMock(BannerInterface::class);
        $this->resourceMock->expects($this->once())->method('delete')->with($bannerMock);

        $result = $this->repository->delete($bannerMock);
        $this->assertTrue($result);
    }

    /**
     * Test delete throws CouldNotDeleteException
     */
    public function testDeleteThrowsCouldNotDeleteException(): void
    {
        $this->expectException(CouldNotDeleteException::class);

        $bannerMock = $this->createMock(BannerInterface::class);
        $this->resourceMock->expects($this->once())
            ->method('delete')
            ->with($bannerMock)
            ->willThrowException(new Exception('Delete failure'));

        $this->repository->delete($bannerMock);
    }

    /**
     * Test getList returns SearchResults
     */
    public function testGetList(): void
    {
        $searchCriteriaMock = $this->createMock(SearchCriteriaInterface::class);
        $collectionMock = $this->createMock(BannerCollection::class);
        $this->bannerCollectionFactoryMock->method('create')->willReturn($collectionMock);

        $this->collectionProcessorMock->expects($this->once())
            ->method('process')
            ->with($searchCriteriaMock, $collectionMock);

        $searchResultsMock = $this->createMock(BannerSearchResultInterface::class);
        $this->searchResultsFactoryMock->method('create')->willReturn($searchResultsMock);

        $searchResultsMock->expects($this->once())->method('setSearchCriteria')->with($searchCriteriaMock);
        $searchResultsMock->expects($this->once())->method('setItems');
        $searchResultsMock->expects($this->once())->method('setTotalCount');

        $result = $this->repository->getList($searchCriteriaMock);
        $this->assertSame($searchResultsMock, $result);
    }
}
