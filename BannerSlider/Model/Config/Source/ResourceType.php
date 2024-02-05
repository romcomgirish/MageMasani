<?php

namespace MageMasani\BannerSlider\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Source Resource Class
 */
class ResourceType implements OptionSourceInterface
{
    public const LOCAL_IMAGE = 'local_image';
    public const EXTERNAL_IMAGE = 'external_image';
    public const YOUTUBE_VIDEO = 'youtube_video';
    public const CUSTOM_HTML = 'custom_html';

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
            self::LOCAL_IMAGE => __('Local Image'),
            self::EXTERNAL_IMAGE => __('External Image'),
            self::YOUTUBE_VIDEO => __('Youtube Video'),
            self::CUSTOM_HTML => __('Custom HTML')
        ];
    }
}
