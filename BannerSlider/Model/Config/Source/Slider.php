<?php

namespace MageMasani\BannerSlider\Model\Config\Source;

use MageMasani\BannerSlider\Api\SliderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source Slider Class
 */
class Slider implements OptionSourceInterface
{
    /**
     * @var SliderRepositoryInterface
     */
    private SliderRepositoryInterface $sliderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param SliderRepositoryInterface $sliderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        SliderRepositoryInterface $sliderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->sliderRepository = $sliderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $this->searchCriteriaBuilder->addFilter('status', '1', 'eq');
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $items = $this->sliderRepository->getList($searchCriteria)->getItems();
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'label' => $item->getTitle(),
                'value' => $item->getEntityId()
            ];
        }
        return $result;
    }
}
