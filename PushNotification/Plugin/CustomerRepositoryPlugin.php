<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use MageMasani\PushNotification\Model\ConfigInterface;
use MageMasani\PushNotification\Model\CampaignMatcher;
use MageMasani\PushNotification\Model\Sender;
use Psr\Log\LoggerInterface;

/**
 * Plugin CustomerRepositoryPlugin
 */
class CustomerRepositoryPlugin
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
     * Track registered emails
     *
     * @var array
     */
    private array $newCustomerEmails = [];

    /**
     * Track group changes
     *
     * @var array
     */
    private array $groupChanges = [];

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
     * Check if customer is new (registration) or changing group before save
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @param string|null $passwordHash
     * @return array
     */
    public function beforeSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface $customer,
        $passwordHash = null
    ): array {
        if (!$customer->getId()) {
            $this->newCustomerEmails[$customer->getEmail()] = true;
        } else {
            try {
                $origCustomer = $subject->getById($customer->getId());
                // Compare group IDs using loose type since one could be string representation
                if ($origCustomer->getGroupId() != $customer->getGroupId()) {
                    $this->groupChanges[$customer->getId()] = true;
                }
            } catch (\Throwable $e) {
                // If customer is not found, treat it as new registration
                $this->newCustomerEmails[$customer->getEmail()] = true;
            }
        }
        return [$customer, $passwordHash];
    }

    /**
     * Send notifications after customer is successfully saved
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $result
     * @param CustomerInterface $customer
     * @param string|null $passwordHash
     * @return CustomerInterface
     */
    public function afterSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface $result,
        CustomerInterface $customer,
        $passwordHash = null
    ): CustomerInterface {
        $email = $result->getEmail();
        $customerId = (int) $result->getId();

        // Handle Registration notification
        if (isset($this->newCustomerEmails[$email])) {
            unset($this->newCustomerEmails[$email]);
            $this->sendNotification('registartion', $customerId);
        }

        // Handle Group Changing notification
        if (isset($this->groupChanges[$customerId])) {
            unset($this->groupChanges[$customerId]);
            $this->sendNotification('group_changing', $customerId);
        }

        return $result;
    }

    /**
     * Helper to send notification for a specific customer event
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
                    'PushNotification: Failed to send event customer notification: ' . $eventConstant,
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
