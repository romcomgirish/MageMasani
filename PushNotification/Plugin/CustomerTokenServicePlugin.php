<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Plugin;

use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use MageMasani\PushNotification\Model\ConfigInterface;
use MageMasani\PushNotification\Model\CampaignMatcher;
use MageMasani\PushNotification\Model\Sender;
use Psr\Log\LoggerInterface;

/**
 * Plugin CustomerTokenServicePlugin
 */
class CustomerTokenServicePlugin
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
     * @var CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param ConfigInterface $config
     * @param CampaignMatcher $campaignMatcher
     * @param Sender $sender
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $config,
        CampaignMatcher $campaignMatcher,
        Sender $sender,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->campaignMatcher = $campaignMatcher;
        $this->sender = $sender;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    /**
     * Send 'login' notification after token is successfully generated for GraphQL/REST
     *
     * @param CustomerTokenServiceInterface $subject
     * @param string $result Token
     * @param string $username
     * @param string $password
     * @return string
     */
    public function afterCreateCustomerAccessToken(
        CustomerTokenServiceInterface $subject,
        string $result,
        $username,
        $password
    ): string {
        try {
            $customer = $this->customerRepository->get($username);
            $this->sendNotification('login', (int) $customer->getId());
        } catch (\Throwable $e) {
            $this->logger->error('PushNotification Token Login Error: ' . $e->getMessage());
        }
        return $result;
    }

    /**
     * Send 'logout' notification after customer token is successfully revoked for GraphQL/REST
     *
     * @param CustomerTokenServiceInterface $subject
     * @param bool $result
     * @param int $customerId
     * @return bool
     */
    public function afterRevokeCustomerAccessToken(
        CustomerTokenServiceInterface $subject,
        bool $result,
        $customerId
    ): bool {
        if ($result) {
            try {
                $this->sendNotification('logout', (int) $customerId);
            } catch (\Throwable $e) {
                $this->logger->error('PushNotification Token Logout Error: ' . $e->getMessage());
            }
        }
        return $result;
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
        if (!$this->config->isEnabled()) {
            return;
        }

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
                    'PushNotification: Failed to send token event customer notification: ' . $eventConstant,
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
