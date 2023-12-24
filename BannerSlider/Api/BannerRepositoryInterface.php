<?php
declare(strict_types=1);

namespace MageMasani\BannerSlider\Api;

interface BannerRepositoryInterface
{
    /**
     * Retrieve banner.
     *
     * @param int $id
     * @param bool $loadFromCache
     * @return \MageMasani\BannerSlider\Api\Data\BannerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadById(int $id, bool $loadFromCache = true);

    /**
     * Factory create.
     *
     * @return \MageMasani\BannerSlider\Api\Data\BannerInterface
     */
    public function create();

    /**
     * Save banner.
     *
     * @param \MageMasani\BannerSlider\Api\Data\BannerInterface $banner
     * @return \MageMasani\BannerSlider\Api\Data\BannerInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\MageMasani\BannerSlider\Api\Data\BannerInterface $banner);

    /**
     * Delete banner.
     *
     * @param \MageMasani\BannerSlider\Api\Data\BannerInterface $banner
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\MageMasani\BannerSlider\Api\Data\BannerInterface $banner): bool;

    /**
     * Delete banner by ID.
     *
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById(int $id): bool;

    /**
     * Retrieve banner matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MageMasani\BannerSlider\Api\Data\BannerSearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Get banner collection.
     *
     * @return \MageMasani\BannerSlider\Model\ResourceModel\Banner\Collection
     */
    public function getCollection(): \MageMasani\BannerSlider\Model\ResourceModel\Banner\Collection;
}
