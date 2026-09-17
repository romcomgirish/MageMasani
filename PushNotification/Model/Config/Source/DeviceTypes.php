<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Model DeviceTypes
 */
class DeviceTypes implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'both', 'label' => __('Both (Web & API)')],
            ['value' => 'web', 'label' => __('Web Only')],
            ['value' => 'api', 'label' => __('API/Mobile Only')]
        ];
    }
}
