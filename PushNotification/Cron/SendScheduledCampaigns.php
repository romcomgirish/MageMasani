<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Cron;

use MageMasani\PushNotification\Api\Data\CampaignInterface;
use MageMasani\PushNotification\Model\ConfigInterface;
use MageMasani\PushNotification\Model\ResourceModel\Campaign as CampaignResource;
use MageMasani\PushNotification\Model\ResourceModel\Campaign\CollectionFactory as CampaignCollectionFactory;
use MageMasani\PushNotification\Model\Sender;
use Psr\Log\LoggerInterface;

/**
 * Cron job SendScheduledCampaigns
 */
class SendScheduledCampaigns
{
    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @var CampaignCollectionFactory
     */
    private CampaignCollectionFactory $collectionFactory;

    /**
     * @var CampaignResource
     */
    private CampaignResource $campaignResource;

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
     * @param CampaignCollectionFactory $collectionFactory
     * @param CampaignResource $campaignResource
     * @param Sender $sender
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $config,
        CampaignCollectionFactory $collectionFactory,
        CampaignResource $campaignResource,
        Sender $sender,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
        $this->campaignResource = $campaignResource;
        $this->sender = $sender;
        $this->logger = $logger;
    }

    /**
     * Find due scheduled campaigns and send push notifications to all active tokens.
     *
     * @return void
     */
    public function execute(): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $campaigns = $this->getDueCampaigns();
        if (empty($campaigns)) {
            return;
        }

        foreach ($campaigns as $campaign) {
            try {
                $result = $this->sender->send(
                    (string) $campaign->getTitle(),
                    (string) $campaign->getBody(),
                    (string) $campaign->getClickUrl() ?: '/',
                    (string) $campaign->getImageUrl(),
                    'scheduled',
                    (int) $campaign->getEntityId()
                );

                // Mark campaign as sent
                $campaign->setData('sent_at', (new \DateTime())->format('Y-m-d H:i:s'));
                $this->campaignResource->save($campaign);

                $this->logger->info(
                    'PushNotification: Scheduled campaign sent',
                    [
                        'campaign_id' => $campaign->getEntityId(),
                        'title' => $campaign->getTitle(),
                        'success' => $result['success'],
                        'failure' => $result['failure']
                    ]
                );
            } catch (\Throwable $e) {
                $this->logger->error(
                    'PushNotification: Failed to send scheduled campaign',
                    [
                        'campaign_id' => $campaign->getEntityId(),
                        'error' => $e->getMessage()
                    ]
                );
            }
        }
    }

    /**
     * Get enabled scheduled campaigns that are due and have not been sent yet.
     *
     * @return CampaignInterface[]
     */
    private function getDueCampaigns(): array
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');

        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(CampaignInterface::STATUS, 1);
        $collection->addFieldToFilter(CampaignInterface::NOTIFICATION_TYPE, 'scheduled');
        $collection->addFieldToFilter(CampaignInterface::SCHEDULE_TO, ['lteq' => $now]);
        $collection->addFieldToFilter('sent_at', ['null' => true]);

        return $collection->getItems();
    }
}
