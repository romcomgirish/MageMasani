<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use MageMasani\PushNotification\Model\ConfigInterface;
use MageMasani\PushNotification\Model\CampaignMatcher;
use MageMasani\PushNotification\Model\Sender;
use Psr\Log\LoggerInterface;

/**
 * Observer OrderStatusObserver
 */
class OrderStatusObserver implements ObserverInterface
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
     * Observe sales_order_save_after to send push notifications on status change.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        if (!$order instanceof Order) {
            return;
        }

        // Check if module is enabled
        if (!$this->config->isEnabled((int) $order->getStoreId())) {
            return;
        }

        // Detect status change
        $newStatus = $order->getStatus();
        $oldStatus = $order->getOrigData('status');

        if ($newStatus === null || $newStatus === $oldStatus) {
            return;
        }

        // Guest orders have no customer_id — skip
        $customerId = $order->getCustomerId();
        if (!$customerId) {
            return;
        }

        // Find matching enabled campaigns
        $campaigns = $this->campaignMatcher->getMatchingCampaigns('order_status', $newStatus);
        if (empty($campaigns)) {
            return;
        }

        foreach ($campaigns as $campaign) {
            try {
                $title = $this->parsePlaceholders((string) $campaign->getTitle(), $order);
                $body = $this->parsePlaceholders((string) $campaign->getBody(), $order);
                $clickUrl = (string) $campaign->getClickUrl();
                $imageUrl = (string) $campaign->getImageUrl();

                $this->sender->sendToCustomer(
                    (int) $customerId,
                    $title,
                    $body,
                    $clickUrl ?: '/',
                    $imageUrl,
                    'event',
                    (int) $campaign->getEntityId()
                );
            } catch (\Throwable $e) {
                // Log and continue — never break order save flow
                $this->logger->error(
                    'PushNotification: Failed to send order status notification',
                    [
                        'order_id' => $order->getIncrementId(),
                        'campaign_id' => $campaign->getEntityId(),
                        'status' => $newStatus,
                        'error' => $e->getMessage()
                    ]
                );
            }
        }
    }

    /**
     * Parse and replace placeholder variables in notification text.
     *
     * @param string $text
     * @param Order $order
     * @return string
     */
    private function parsePlaceholders(string $text, Order $order): string
    {
        $customerName = trim((string) $order->getCustomerFirstname() . ' ' . (string) $order->getCustomerLastname());
        if ($customerName === '') {
            $customerName = trim($order->getBillingAddress() ? (string) $order->getBillingAddress()->getName() : '');
        }

        $replacements = [
            '{{customer_name}}' => $customerName,
            '{{order_id}}' => (string) $order->getEntityId(),
            '{{order_increment_id}}' => (string) $order->getIncrementId(),
            '{{order_status}}' => (string) ($order->getStatusLabel() ?: $order->getStatus())
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
