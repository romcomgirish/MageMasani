<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Cron;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use MageMasani\PushNotification\Model\ConfigInterface;
use MageMasani\PushNotification\Model\CampaignMatcher;
use MageMasani\PushNotification\Model\Sender;
use Psr\Log\LoggerInterface;

/**
 * Cron job SendBirthdayCampaigns
 */
class SendBirthdayCampaigns
{
    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @var CampaignMatcher
     */
    private CampaignMatcher $campaignMatcher;

    /**
     * @var Sender
     */
    private Sender $sender;

    /**
     * @var CustomerCollectionFactory
     */
    private CustomerCollectionFactory $customerCollectionFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param ConfigInterface $config
     * @param CampaignMatcher $campaignMatcher
     * @param Sender $sender
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $config,
        CampaignMatcher $campaignMatcher,
        Sender $sender,
        CustomerCollectionFactory $customerCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->campaignMatcher = $campaignMatcher;
        $this->sender = $sender;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * Find customers whose birthday is today and send birthday push notifications.
     *
     * @return void
     */
    public function execute(): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $campaigns = $this->campaignMatcher->getMatchingCampaigns('birthday');
        if (empty($campaigns)) {
            return;
        }

        $todayMonthDay = (new \DateTime())->format('m-d');
        $collection = $this->customerCollectionFactory->create();
        $collection->addAttributeToSelect('entity_id');
        $collection->addAttributeToFilter('dob', ['notnull' => true]);

        // Query customers whose DOB matches today's month and day
        $collection->getSelect()->where(
            'DATE_FORMAT(dob, "%m-%d") = ?',
            $todayMonthDay
        );

        $customers = $collection->getItems();
        if (empty($customers)) {
            return;
        }

        foreach ($customers as $customer) {
            $customerId = (int) $customer->getId();
            foreach ($campaigns as $campaign) {
                try {
                    $this->sender->sendToCustomer(
                        $customerId,
                        (string) $campaign->getTitle(),
                        (string) $campaign->getBody(),
                        (string) $campaign->getClickUrl() ?: '/',
                        (string) $campaign->getImageUrl(),
                        'scheduled',
                        (int) $campaign->getEntityId()
                    );
                } catch (\Throwable $e) {
                    $this->logger->error(
                        'PushNotification: Failed to send birthday notification to customer',
                        [
                            'customer_id' => $customerId,
                            'campaign_id' => $campaign->getEntityId(),
                            'error' => $e->getMessage()
                        ]
                    );
                }
            }
        }
    }
}
