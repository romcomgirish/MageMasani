<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Newsletter\Model\Subscriber;
use MageMasani\PushNotification\Model\ConfigInterface;
use MageMasani\PushNotification\Model\CampaignMatcher;
use MageMasani\PushNotification\Model\Sender;
use Psr\Log\LoggerInterface;

/**
 * Observer NewsletterSubscriberSaveObserver
 */
class NewsletterSubscriberSaveObserver implements ObserverInterface
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
     * Send push notification when a customer subscribes or unsubscribes to newsletter
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var Subscriber $subscriber */
        $subscriber = $observer->getEvent()->getSubscriber();
        if (!$subscriber) {
            return;
        }

        if (!$this->config->isEnabled()) {
            return;
        }

        $customerId = (int) $subscriber->getCustomerId();
        if (!$customerId) {
            return;
        }

        if ($subscriber->isStatusChanged()) {
            $status = (int) $subscriber->getStatus();
            if ($status === Subscriber::STATUS_SUBSCRIBED) {
                $this->sendNotification('sub_scription', $customerId);
            } elseif ($status === Subscriber::STATUS_UNSUBSCRIBED) {
                $this->sendNotification('sub_scription_cancelation', $customerId);
            }
        }
    }

    /**
     * Helper to send notification for customer event
     *
     * @param string $eventConstant
     * @param int $customerId
     * @return void
     */
    private function sendNotification(string $eventConstant, int $customerId): void
    {
        $campaigns = $this->campaignMatcher->getMatchingCampaigns($eventConstant);
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
                    'PushNotification: Failed to send newsletter event customer notification: ' . $eventConstant,
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
