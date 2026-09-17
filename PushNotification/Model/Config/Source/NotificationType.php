<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Model NotificationType
 */
class NotificationType implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'instant', 'label' => __('Instant Notification')],
            ['value' => 'scheduled', 'label' => __('Scheduled Notification')],
            ['value' => 'event', 'label' => __('Event Notification')]
        ];
    }
}
