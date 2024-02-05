<?php
/**
 * MageINIC
 * Copyright (C) 2023 MageINIC <support@mageinic.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://opensource.org/licenses/gpl-3.0.html.
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category MageINIC
 * @package MageINIC_CityRegionPostcodeGraphQl
 * @copyright Copyright (c) 2023 MageINIC (https://www.mageinic.com/)
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MageINIC <support@mageinic.com>
 */

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
    private BannerRepositoryInterface $cityRepository;

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
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param BannerRepositoryInterface $cityRepository
     * @param SortOrderBuilder $sortOrderBuilder
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param FilterEmulate $filterEmulate
     * @param ImageUploader|null $imageUploader
     */
    public function __construct(
        SearchCriteriaBuilder     $searchCriteriaBuilder,
        BannerRepositoryInterface $cityRepository,
        SortOrderBuilder          $sortOrderBuilder,
        ServiceOutputProcessor    $serviceOutputProcessor,
        ScopeConfigInterface      $scopeConfig,
        StoreManagerInterface     $storeManager,
        FilterEmulate             $filterEmulate,
        ImageUploader $imageUploader = null
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->cityRepository = $cityRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->filterEmulate = $filterEmulate;
        $this->imageUploader = $imageUploader ?:  ObjectManager::getInstance()->get(BannerImageUploader::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field       $field,
        $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ) {
        try {
            if ($this->scopeConfig->isSetFlag(ConfigInterface::MODULE_ENABLE, ScopeInterface::SCOPE_STORE)) {
                return [];
            }
            $this->validateArgs($args);
            $args[Filter::ARGUMENT_NAME][BannerInterface::STATUS] = ['eq' => 1];
            $searchCriteria = $this->searchCriteriaBuilder->build($field->getName(), $args);
            $searchCriteria->setCurrentPage($args['currentPage']);
            $searchCriteria->setPageSize($args['pageSize']);
            if (isset($args['sort'])) {
                $sort = $args['sort'];
                foreach ($sort as $key => $value) {
                    $sortOrder = $this->sortOrderBuilder->setField($key)->setDirection($value)->create();
                    $searchCriteria->setSortOrders([$sortOrder]);
                }
            }
            $searchResult = $this->cityRepository->getList($searchCriteria);
            $postData = [
                "items" => [],
                "total_count" => 0
            ];
            foreach ($searchResult->getItems() as $banner) {
                $customerData = $this->serviceOutputProcessor->process(
                    $banner,
                    BannerRepositoryInterface::class,
                    'getById'
                );
                if ($banner->getResourceType() == 'local_image') {
                    $customerData['resource_path'] = $this->setLocalImage($banner->getResourcePath());
                } elseif ($banner->getResourceType() == 'custom_html') {
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
            $store = $this->storeManager->getStore();
            return $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) .$this->imageUploader->getBasePath().'/'. $resourcePath;
        } else {
            return __('No image found');
        }
    }
}
