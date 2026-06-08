<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MageMasani\PushNotification\Model\ConfigInterface;
use MageMasani\PushNotification\Model\CampaignMatcher;
use MageMasani\PushNotification\Model\Sender;
use Psr\Log\LoggerInterface;

class CustomerLogoutObserver implements ObserverInterface
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
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param ConfigInterface $config
     * @param CampaignMatcher $campaignMatcher
     * @param Sender $sender
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $config,
        CampaignMatcher $campaignMatcher,
        Sender $sender,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->campaignMatcher = $campaignMatcher;
        $this->sender = $sender;
        $this->logger = $logger;
    }

    /**
     * Send 'logout' push notification on customer logout
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $customer = $observer->getEvent()->getCustomer();
        if (!$customer || !$customer->getId()) {
            return;
        }

        if (!$this->config->isEnabled()) {
            return;
        }

        $customerId = (int) $customer->getId();
        $campaigns = $this->campaignMatcher->getMatchingCampaigns('logout');

        foreach ($campaigns as $campaign) {
            try {
                $this->sender->sendToCustomer(
                    $customerId,
                    (string) $campaign->getTitle(),
                    (string) $campaign->getBody(),
                    (string) $campaign->getClickUrl() ?: '/',
                    (string) $campaign->getImageUrl(),
                    'event',
                    (int) $campaign->getEntityId()
                );
            } catch (\Throwable $e) {
                $this->logger->error(
                    'PushNotification: Failed to send logout notification',
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
