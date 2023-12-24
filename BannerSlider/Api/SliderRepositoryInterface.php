<?php
declare(strict_types=1);

namespace MageMasani\BannerSlider\Api;

/**
 * BannerSlider Slider CRUD interface.
 */
interface SliderRepositoryInterface
{
    /**
     * Save slider.
     *
     * @param \MageMasani\BannerSlider\Api\Data\SliderInterface $slider
     * @return \MageMasani\BannerSlider\Api\Data\SliderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\SliderInterface $slider);

    /**
     * Retrieve slider.
     *
     * @param string $id
     * @return \MageMasani\BannerSlider\Api\Data\SliderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve slider matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MageMasani\BannerSlider\Api\Data\SliderSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete slider.
     *
     * @param \MageMasani\BannerSlider\Api\Data\SliderInterface $slider
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\SliderInterface $slider);

    /**
     * Delete slider by ID.
     *
     * @param string $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
