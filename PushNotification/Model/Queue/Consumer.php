<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model\Queue;

use MageMasani\PushNotification\Model\Sender;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class Consumer
{
    /**
     * @var Sender
     */
    private Sender $sender;

    /**
     * @var Json
     */
    private Json $serializer;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param Sender $sender
     * @param Json $serializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        Sender $sender,
        Json $serializer,
        LoggerInterface $logger
    ) {
        $this->sender = $sender;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * Process message from queue
     *
     * @param string $message
     * @return void
     */
    public function process(string $message): void
    {
        try {
            $data = $this->serializer->unserialize($message);
            if (!is_array($data)) {
                throw new \InvalidArgumentException('Queue message must be a serialized array.');
            }

            $title = $data['title'] ?? '';
            $body = $data['body'] ?? '';
            $clickUrl = $data['click_url'] ?? '/';
            $imageUrl = $data['image_url'] ?? '';
            $notificationType = $data['notification_type'] ?? 'manual';
            $campaignId = isset($data['campaign_id']) ? (int) $data['campaign_id'] : null;
            $customerId = isset($data['customer_id']) ? (int) $data['customer_id'] : null;

            if ($customerId !== null) {
                $this->sender->sendDirectToCustomer(
                    $customerId,
                    $title,
                    $body,
                    $clickUrl,
                    $imageUrl,
                    $notificationType,
                    $campaignId
                );
            } else {
                $this->sender->sendDirect(
                    $title,
                    $body,
                    $clickUrl,
                    $imageUrl,
                    $notificationType,
                    $campaignId
                );
            }
        } catch (\Throwable $e) {
            $this->logger->error('PushNotification Queue Consumer Error: ' . $e->getMessage(), [
                'exception' => $e,
                'message' => $message
            ]);
        }
    }
}
