<?php
/**
 * MageMasani BannerSliderGraphQl Identity Resolver
 *
 * @category  MageMasani
 * @package   MageMasani_BannerSliderGraphQl
 * @author    MageMasani <support@magemasani.com>
 * @copyright Copyright (c) MageMasani (https://www.magemasani.com/)
 * @license   GPL-3.0-or-later
 */

declare(strict_types=1);

namespace MageMasani\BannerSliderGraphQl\Model\Resolver\BannerSlider;

use Magento\Framework\GraphQl\Query\Resolver\IdentityInterface;

/**
 * Identity resolver for cached Banner Slider GraphQL queries
 */
class Identity implements IdentityInterface
{
    /**
     * Cache tag for Banner
     */
    private const CACHE_TAG = 'magemasani_bannerslider_banner';

    /**
     * Get Banner Slider query identities from resolved data
     *
     * @param array $resolvedData
     * @return string[]
     */
    public function getIdentities(array $resolvedData): array
    {
        $identities = [self::CACHE_TAG];
        if (isset($resolvedData['items']) && is_array($resolvedData['items'])) {
            foreach ($resolvedData['items'] as $item) {
                if (isset($item['entity_id'])) {
                    $identities[] = sprintf('%s_%s', self::CACHE_TAG, $item['entity_id']);
                }
            }
        }
        return $identities;
    }
}
