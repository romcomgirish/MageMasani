<?php

namespace MageMasani\BannerSlider\Ui\DataProvider\Banner\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Banner Product Class
 */
class Product implements ModifierInterface
{
    /**
     * @inheritdoc
     */
    public function modifyData(array $data): array
    {
        foreach ($data as &$item) {
            $resourcePath = $item['link_type_resource'] ?? null;
            $resourceType = $item['link_type'];
            if ($resourcePath && $resourceType === 'product') {
                unset($item['link_type']);
                $item['link_type_resource_product'] = $resourcePath;
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
