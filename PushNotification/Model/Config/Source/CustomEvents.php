<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Model CustomEvents
 */
class CustomEvents implements OptionSourceInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var Json
     */
    private Json $jsonSerializer;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $jsonSerializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $jsonSerializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        $options[] = [
            'value' => '',
            'label' => __('-- Please Select --')
        ];

        $configValue = $this->scopeConfig->getValue(
            'pushnotification/event_settings/custom_events',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($configValue) {
            try {
                $events = is_array($configValue) ? $configValue : $this->jsonSerializer->unserialize($configValue);
                if (is_array($events)) {
                    foreach ($events as $event) {
                        if (isset($event['event_name']) && isset($event['event_constant'])) {
                            $options[] = [
                                'value' => $event['event_constant'],
                                'label' => $event['event_name']
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                // Ignore malformed JSON
            }
        }

        return $options;
    }
}
