<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Cron;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use MageMasani\PushNotification\Model\CampaignMatcher;
use MageMasani\PushNotification\Model\ConfigInterface;
use MageMasani\PushNotification\Model\ResourceModel\NotificationHistory\CollectionFactory as HistoryCollectionFactory;
use MageMasani\PushNotification\Model\Sender;
use Psr\Log\LoggerInterface;

/**
 * Cron job SendAbandonedCartCampaigns
 *
 * Scans active abandoned quotes and sends push notifications to customers.
 */
class SendAbandonedCartCampaigns
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
     * @var QuoteCollectionFactory
     */
    private QuoteCollectionFactory $quoteCollectionFactory;

    /**
     * @var HistoryCollectionFactory
     */
    private HistoryCollectionFactory $historyCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    private PriceCurrencyInterface $priceCurrency;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param ConfigInterface $config
     * @param CampaignMatcher $campaignMatcher
     * @param Sender $sender
     * @param QuoteCollectionFactory $quoteCollectionFactory
     * @param HistoryCollectionFactory $historyCollectionFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $config,
        CampaignMatcher $campaignMatcher,
        Sender $sender,
        QuoteCollectionFactory $quoteCollectionFactory,
        HistoryCollectionFactory $historyCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->campaignMatcher = $campaignMatcher;
        $this->sender = $sender;
        $this->quoteCollectionFactory = $quoteCollectionFactory;
        $this->historyCollectionFactory = $historyCollectionFactory;
        $this->priceCurrency = $priceCurrency;
        $this->logger = $logger;
    }

    /**
     * Find abandoned shopping carts and send push notifications.
     *
     * @return void
     */
    public function execute(): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $campaigns = $this->campaignMatcher->getMatchingCampaigns('abandoned_cart');
        if (empty($campaigns)) {
            return;
        }

        $delayMinutes = $this->config->getAbandonedCartDelayMinutes();
        $maxAgeDays = $this->config->getAbandonedCartMaxAgeDays();

        $cutoffMax = (new \DateTime())->modify("-{$delayMinutes} minutes")->format('Y-m-d H:i:s');
        $cutoffMin = (new \DateTime())->modify("-{$maxAgeDays} days")->format('Y-m-d H:i:s');

        $quoteCollection = $this->quoteCollectionFactory->create();
        $quoteCollection->addFieldToFilter('is_active', 1);
        $quoteCollection->addFieldToFilter('items_count', ['gt' => 0]);
        $quoteCollection->addFieldToFilter('customer_id', ['notnull' => true]);
        $quoteCollection->addFieldToFilter('customer_id', ['gt' => 0]);
        $quoteCollection->addFieldToFilter('updated_at', ['lteq' => $cutoffMax]);
        $quoteCollection->addFieldToFilter('updated_at', ['gteq' => $cutoffMin]);

        $quotes = $quoteCollection->getItems();
        if (empty($quotes)) {
            return;
        }

        /** @var Quote $quote */
        foreach ($quotes as $quote) {
            $customerId = (int) $quote->getCustomerId();
            if (!$customerId) {
                continue;
            }

            // Check if customer store matches module configuration
            if (!$this->config->isEnabled((int) $quote->getStoreId())) {
                continue;
            }

            // Deduplication: check if notification already sent for this quote session
            if ($this->hasAlreadyNotified($customerId, $quote->getUpdatedAt())) {
                continue;
            }

            foreach ($campaigns as $campaign) {
                try {
                    $title = $this->parsePlaceholders((string) $campaign->getTitle(), $quote);
                    $body = $this->parsePlaceholders((string) $campaign->getBody(), $quote);
                    $clickUrl = (string) $campaign->getClickUrl();
                    if (empty($clickUrl) || $clickUrl === '/') {
                        $clickUrl = $quote->getStore()->getUrl('checkout/cart');
                    } else {
                        $clickUrl = $this->parsePlaceholders($clickUrl, $quote);
                    }
                    $imageUrl = (string) $campaign->getImageUrl();

                    $this->sender->sendToCustomer(
                        $customerId,
                        $title,
                        $body,
                        $clickUrl,
                        $imageUrl,
                        'event',
                        (int) $campaign->getEntityId()
                    );
                } catch (\Throwable $e) {
                    $this->logger->error(
                        'PushNotification: Failed to send abandoned cart notification',
                        [
                            'customer_id' => $customerId,
                            'quote_id' => $quote->getId(),
                            'campaign_id' => $campaign->getEntityId(),
                            'error' => $e->getMessage()
                        ]
                    );
                }
            }
        }
    }

    /**
     * Check if customer was already sent an abandoned cart notification for the current cart state.
     *
     * @param int $customerId
     * @param string|null $quoteUpdatedAt
     * @return bool
     */
    private function hasAlreadyNotified(int $customerId, ?string $quoteUpdatedAt): bool
    {
        if (!$quoteUpdatedAt) {
            return false;
        }

        $historyCollection = $this->historyCollectionFactory->create();
        $historyCollection->addFieldToFilter('customer_id', $customerId);
        $historyCollection->addFieldToFilter('notification_type', 'event');
        $historyCollection->addFieldToFilter('sent_at', ['gteq' => $quoteUpdatedAt]);

        return $historyCollection->getSize() > 0;
    }

    /**
     * Replace dynamic placeholders with quote data.
     *
     * @param string $text
     * @param Quote $quote
     * @return string
     */
    private function parsePlaceholders(string $text, Quote $quote): string
    {
        $customerName = trim((string) $quote->getCustomerFirstname() . ' ' . (string) $quote->getCustomerLastname());
        if ($customerName === '' && $quote->getBillingAddress()) {
            $customerName = trim((string) $quote->getBillingAddress()->getName());
        }
        if ($customerName === '') {
            $customerName = __('Customer')->render();
        }

        $itemsCount = (int) ($quote->getItemsQty() ?: $quote->getItemsCount());
        $grandTotal = $this->priceCurrency->format(
            (float) $quote->getGrandTotal(),
            false,
            2,
            $quote->getStoreId()
        );
        $cartUrl = $quote->getStore()->getUrl('checkout/cart');

        $replacements = [
            '{{customer_name}}' => $customerName,
            '{{items_count}}' => (string) $itemsCount,
            '{{grand_total}}' => $grandTotal,
            '{{cart_url}}' => $cartUrl
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
