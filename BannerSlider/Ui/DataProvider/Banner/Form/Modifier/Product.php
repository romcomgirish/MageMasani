<?php
/**
 * MageMasani BannerSlider Module
 *
 * @category  MageMasani
 * @package   MageMasani_BannerSlider
 * @author    MageMasani <support@magemasani.com>
 * @copyright Copyright (c) MageMasani (https://www.magemasani.com/)
 * @license   GPL-3.0-or-later
 */

declare(strict_types=1);

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
            $linkTypeResource = $item['link_type_resource'] ?? null;
            $linkType = $item['link_type'] ?? null;
            if ($linkTypeResource && $linkType === 'link_type_product') {
                $item['link_type_resource_product'] = $linkTypeResource;
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
