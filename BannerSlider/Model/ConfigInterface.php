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

namespace MageMasani\BannerSlider\Model;

/**
 * System Configuration Interface
 */
interface ConfigInterface
{
    public const MODULE_ENABLE = 'magenasani_bannerslider/general/module_status';
    public const SLIDER_BREAK_POINTS = 'magenasani_bannerslider/general/break_points';

    /**
     * Check if module is enabled
     *
     * @return bool
     * @since 100.2.0
     */
    public function isModuleEnable(): bool;

    /**
     * Get Slider Break Point's
     *
     * @since 100.2.0
     */
    public function getSliderBreakPoints();
}
