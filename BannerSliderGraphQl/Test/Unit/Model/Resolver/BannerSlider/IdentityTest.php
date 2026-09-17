<?php
/**
 * MageMasani BannerSliderGraphQl Module
 *
 * @category  MageMasani
 * @package   MageMasani_BannerSliderGraphQl
 * @author    MageMasani <support@magemasani.com>
 * @copyright Copyright (c) MageMasani (https://www.magemasani.com/)
 * @license   GPL-3.0-or-later
 */

declare(strict_types=1);

namespace MageMasani\BannerSliderGraphQl\Test\Unit\Model\Resolver\BannerSlider;

use MageMasani\BannerSliderGraphQl\Model\Resolver\BannerSlider\Identity;
use PHPUnit\Framework\TestCase;

/**
 * Unit Test for BannerSlider Identity Cache Resolver
 */
class IdentityTest extends TestCase
{
    /**
     * @var Identity
     */
    private Identity $identity;

    /**
     * Set up test instance
     */
    protected function setUp(): void
    {
        $this->identity = new Identity();
    }

    /**
     * Test getIdentities returns cache tag array
     */
    public function testGetIdentitiesWithEmptyResolvedData(): void
    {
        $result = $this->identity->getIdentities([]);
        $this->assertEquals(['magemasani_bannerslider_banner'], $result);
    }

    /**
     * Test getIdentities returns item-specific cache tags
     */
    public function testGetIdentitiesWithItems(): void
    {
        $resolvedData = [
            'items' => [
                ['entity_id' => 10],
                ['entity_id' => 25]
            ]
        ];

        $result = $this->identity->getIdentities($resolvedData);
        $expected = [
            'magemasani_bannerslider_banner',
            'magemasani_bannerslider_banner_10',
            'magemasani_bannerslider_banner_25'
        ];

        $this->assertEquals($expected, $result);
    }
}
