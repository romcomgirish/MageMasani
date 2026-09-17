<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model\Resolver;

use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\ArgumentApplier\Filter;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use MageMasani\PushNotification\Model\ResourceModel\NotificationHistory\CollectionFactory;

/**
 * Model PushNotificationHistory
 */
class PushNotificationHistory implements ResolverInterface
{
    /**
     * Max page size constant
     *
     * @var int
     */
    private const MAX_PAGE_SIZE = 200;
    /**
     * Allowed sort fields constant
     *
     * @var array
     */
    private const ALLOWED_SORT_FIELDS = ['entity_id', 'sent_at'];
    /**
     * Allowed sort directions constant
     *
     * @var array
     */
    private const ALLOWED_SORT_DIRECTIONS = [SortOrder::SORT_ASC, SortOrder::SORT_DESC];

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;

    /**
     * @var SortOrderBuilder
     */
    private SortOrderBuilder $sortOrderBuilder;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private SearchResultsInterfaceFactory $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SortOrderBuilder $sortOrderBuilder
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionProcessorInterface $collectionProcessor,
        SortOrderBuilder $sortOrderBuilder,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionProcessor = $collectionProcessor;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
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
        if (!$context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthenticationException(
                __('The current customer isn\'t authorized.')
            );
        }

        $customerId = (int) $context->getUserId();
        if ($customerId <= 0) {
            throw new GraphQlAuthenticationException(
                __('The current customer isn\'t authorized.')
            );
        }

        $args = $args ?? [];

        $currentPage = (int) ($args['currentPage'] ?? 1);
        $pageSize = (int) ($args['pageSize'] ?? 20);

        if ($currentPage < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if ($pageSize < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }
        if ($pageSize > self::MAX_PAGE_SIZE) {
            $pageSize = self::MAX_PAGE_SIZE;
        }

        try {
            // Force-override any client-supplied customer_id filter
            if (!isset($args[Filter::ARGUMENT_NAME]) || !is_array($args[Filter::ARGUMENT_NAME])) {
                $args[Filter::ARGUMENT_NAME] = [];
            }
            $args[Filter::ARGUMENT_NAME]['customer_id'] = ['eq' => $customerId];
            $searchCriteria = $this->searchCriteriaBuilder->build($field->getName(), $args);
        } catch (\Exception $e) {
            throw new GraphQlInputException(__('Invalid filter criteria provided.'));
        }

        $searchCriteria->setCurrentPage($currentPage);
        $searchCriteria->setPageSize($pageSize);

        $sortOrders = $this->buildSortOrders($args['sort'] ?? null);
        if ($sortOrders) {
            $searchCriteria->setSortOrders($sortOrders);
        }

        $collection = $this->collectionFactory->create();

        try {
            $this->collectionProcessor->process($searchCriteria, $collection);
        } catch (\Exception $e) {
            throw new GraphQlInputException(__('Invalid filter or sort criteria.'));
        }

        $selection = $info->getFieldSelection(2);
        $needsTotal = !empty($selection['total_count'])
            || (isset($selection['page_info']) && is_array($selection['page_info'])
                && !empty($selection['page_info']['total_pages']));

        $totalCount = $needsTotal ? (int) $collection->getSize() : 0;
        $totalPages = ($needsTotal && $pageSize > 0) ? (int) ceil($totalCount / $pageSize) : 0;

        $items = [];
        foreach ($collection as $history) {
            $items[] = $history;
        }

        return [
            'items' => $items,
            'page_info' => [
                'current_page' => $currentPage,
                'page_size' => $pageSize,
                'total_pages' => $totalPages,
            ],
            'total_count' => $totalCount,
        ];
    }

    /**
     * Whitelist sort field + direction so a bad arg can't reach SQL.
     */
    private function buildSortOrders($sort): array
    {
        if (!is_array($sort) || empty($sort)) {
            return [];
        }

        $orders = [];
        foreach ($sort as $fieldName => $direction) {
            if (!is_string($fieldName) || !in_array($fieldName, self::ALLOWED_SORT_FIELDS, true)) {
                continue;
            }
            $direction = is_string($direction) ? strtoupper($direction) : '';
            if (!in_array($direction, self::ALLOWED_SORT_DIRECTIONS, true)) {
                continue;
            }
            $orders[] = $this->sortOrderBuilder
                ->setField($fieldName)
                ->setDirection($direction)
                ->create();
        }

        return $orders;
    }
}
