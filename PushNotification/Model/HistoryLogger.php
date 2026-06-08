<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model;

use MageMasani\PushNotification\Model\ResourceModel\NotificationHistory as HistoryResource;

/**
 * Service to record push notification send events into the history table.
 */
class HistoryLogger
{
    /**
     * @var NotificationHistoryFactory
     */
    private NotificationHistoryFactory $historyFactory;

    /**
     * @var HistoryResource
     */
    private HistoryResource $historyResource;

    /**
     * @param NotificationHistoryFactory $historyFactory
     * @param HistoryResource $historyResource
     */
    public function __construct(
        NotificationHistoryFactory $historyFactory,
        HistoryResource $historyResource
    ) {
        $this->historyFactory = $historyFactory;
        $this->historyResource = $historyResource;
    }

    /**
     * Record a notification send event.
     *
     * @param array{success:int,failure:int,errors:array} $result
     * @param string $title
     * @param string $body
     * @param string $clickUrl
     * @param string $imageUrl
     * @param string $notificationType  scheduled|event|manual
     * @param bool $isGlobal
     * @param int|null $customerId
     * @param int|null $campaignId
     * @return void
     */
    public function log(
        array $result,
        string $title,
        string $body,
        string $clickUrl = '',
        string $imageUrl = '',
        string $notificationType = 'manual',
        bool $isGlobal = true,
        ?int $customerId = null,
        ?int $campaignId = null
    ): void {
        try {
            $successCount = (int) ($result['success'] ?? 0);
            $failureCount = (int) ($result['failure'] ?? 0);
            $tokenCount = $successCount + $failureCount;
            $status = ($failureCount > 0 && $successCount === 0) ? 'failed' : 'sent';

            $errorMessage = null;
            if (!empty($result['errors'])) {
                $errorMessages = [];
                foreach (array_slice($result['errors'], 0, 5) as $error) {
                    $errorMessages[] = sprintf(
                        'Token: %s... | HTTP %s',
                        substr($error['token'] ?? '', 0, 20),
                        $error['status'] ?? 'unknown'
                    );
                }
                $errorMessage = implode("\n", $errorMessages);
            }

            $history = $this->historyFactory->create();
            $history->setData([
                NotificationHistory::CAMPAIGN_ID => $campaignId,
                NotificationHistory::CUSTOMER_ID => $customerId,
                NotificationHistory::TITLE => mb_substr($title, 0, 255),
                NotificationHistory::BODY => $body,
                NotificationHistory::CLICK_URL => $clickUrl ?: null,
                NotificationHistory::IMAGE_URL => $imageUrl ?: null,
                NotificationHistory::NOTIFICATION_TYPE => $notificationType,
                NotificationHistory::IS_GLOBAL => $isGlobal ? 1 : 0,
                NotificationHistory::TOKEN_COUNT => $tokenCount,
                NotificationHistory::SUCCESS_COUNT => $successCount,
                NotificationHistory::FAILURE_COUNT => $failureCount,
                NotificationHistory::STATUS => $status,
                NotificationHistory::ERROR_MESSAGE => $errorMessage,
            ]);
            $this->historyResource->save($history);
        } catch (\Throwable $e) {
            // Best-effort logging — never break the send flow
        }
    }
}
