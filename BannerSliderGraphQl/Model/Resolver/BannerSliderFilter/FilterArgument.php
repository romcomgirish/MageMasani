<?php
declare(strict_types=1);

namespace MageMasani\BannerSliderGraphQl\Model\Resolver\BannerSliderFilter;

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
     * Gather attributes for Category filtering
     * Example format ['attributeNameInGraphQl' => ['type' => 'String'. 'fieldName' => 'attributeNameInSearchCriteria']]
     *
     * @return array
     */
    public function getEntityAttributes() : array
    {
        $FilterType = $this->config->getConfigElement('BannerSliderInfo');

        if (!$FilterType) {
            throw new LogicException((string)__("BannerSlider type not defined in schema."));
        }
        $fields = [];
        foreach ($FilterType->getFields() as $field) {
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
        return $fields;
    }
}
