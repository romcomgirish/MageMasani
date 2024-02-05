<?php
declare(strict_types=1);

namespace MageMasani\BannerSlider\Api;

/**
 * MageMasani Banner CRUD interface.
 */
interface BannerRepositoryInterface
{
    /**
     * Save slider.
     *
     * @param \MageMasani\BannerSlider\Api\Data\BannerInterface $banner
     * @return \MageMasani\BannerSlider\Api\Data\BannerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\BannerInterface $banner);

    /**
     * Retrieve slider.
     *
     * @param string $id
     * @return \MageMasani\BannerSlider\Api\Data\BannerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve slider matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MageMasani\BannerSlider\Api\Data\BannerSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete slider.
     *
     * @param \MageMasani\BannerSlider\Api\Data\BannerInterface $banner
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\BannerInterface $banner);

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
