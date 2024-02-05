<?php

namespace MageMasani\BannerSlider\Ui\DataProvider\Banner\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Banner Product Class
 */
class Category implements ModifierInterface
{
    /**
     * @inheritdoc
     */
    public function modifyData(array $data): array
    {
        foreach ($data as &$item) {
            $resourcePath = $item['link_type'] ?? null;
            $resourceType = $item['link_type_resource'];
            if ($resourcePath && $resourceType === 'link_type_resource_category') {
                unset($item['link_type']);
                $item['link_type_resource_category'] = $resourcePath;
            }
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
