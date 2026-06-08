<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model\Resolver\NotificationHistoryFilter;

use LogicException;
use Magento\Framework\GraphQl\Config\Element\Type;
use Magento\Framework\GraphQl\ConfigInterface;
use Magento\Framework\GraphQl\Query\Resolver\Argument\FieldEntityAttributesInterface;

/**
 * @inheritdoc
 */
class FilterArgument implements FieldEntityAttributesInterface
{
    /**
     * @var array
     */
    private array $fieldMapping = [];

    /**
     * @var array
     */
    private array $additionalFields = [];

    /**
     * @var array|null
     */
    private ?array $cachedFields = null;

    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * @param ConfigInterface $config
     * @param array $additionalFields
     * @param array $attributeFieldMapping
     */
    public function __construct(
        ConfigInterface $config,
        array $additionalFields = [],
        array $attributeFieldMapping = []
    ) {
        $this->config = $config;
        $this->additionalFields = array_merge($this->additionalFields, $additionalFields);
        $this->fieldMapping = array_merge($this->fieldMapping, $attributeFieldMapping);
    }

    /**
     * @inheritdoc
     *
     * Gather attributes for PushNotificationHistoryItem filtering
     *
     * @return array
     */
    public function getEntityAttributes(): array
    {
        if ($this->cachedFields !== null) {
            return $this->cachedFields;
        }

        $filterType = $this->config->getConfigElement('PushNotificationHistoryItem');

        if (!$filterType) {
            throw new LogicException((string) __("PushNotificationHistoryItem type not defined in schema."));
        }
        $fields = [];
        foreach ($filterType->getFields() as $field) {
            $fields[$field->getName()] = [
                'type' => 'String',
                'fieldName' => $this->fieldMapping[$field->getName()] ?? $field->getName(),
            ];
        }

        foreach ($this->additionalFields as $additionalField) {
            $fields[$additionalField] = [
                'type' => 'String',
                'fieldName' => $additionalField,
            ];
        }

        $this->cachedFields = $fields;
        return $fields;
    }
}
