<?php
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
