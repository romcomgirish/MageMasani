<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Api;

/**
 * Interface CampaignRepositoryInterface
 *
 * @api
 */
interface CampaignRepositoryInterface
{
    /**
     * Save campaign.
     *
     * @param \MageMasani\PushNotification\Api\Data\CampaignInterface $campaign
     * @return \MageMasani\PushNotification\Api\Data\CampaignInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\CampaignInterface $campaign);

    /**
     * Retrieve campaign.
     *
     * @param string $id
     * @return \MageMasani\PushNotification\Api\Data\CampaignInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve campaign matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MageMasani\PushNotification\Api\Data\CampaignSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete campaign.
     *
     * @param \MageMasani\PushNotification\Api\Data\CampaignInterface $campaign
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\CampaignInterface $campaign);

    /**
     * Delete campaign by ID.
     *
     * @param string $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
