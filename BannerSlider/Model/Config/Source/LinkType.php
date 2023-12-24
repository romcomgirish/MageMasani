<?php

namespace MageMasani\BannerSlider\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class LinkType implements OptionSourceInterface
{
    public const LINK_TYPE_PRODUCT = 'link_type_product';
    public const LINK_TYPE_CATEGORY = 'link_type_category';
    public const LINK_TYPE_CUSTOM = 'link_type_custom';

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::LINK_TYPE_PRODUCT => __('Product'),
            self::LINK_TYPE_CATEGORY => __('Category'),
            self::LINK_TYPE_CUSTOM => __('Custom Link')
        ];
    }
}
