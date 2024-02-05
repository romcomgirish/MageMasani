<?php
/**
 * MageINIC
 * Copyright (C) 2023 MageINIC <support@mageinic.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see https://opensource.org/licenses/gpl-3.0.html.
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category MageINIC
 * @package MageINIC_DiscountFilter
 * @copyright Copyright (c) 2023 MageINIC (https://www.mageinic.com/)
 * @license https://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MageINIC <support@mageinic.com>
 */

namespace MageMasani\BannerSlider\Model;

/**
 * System Configuration Interface
 */
interface ConfigInterface
{
    public const MODULE_ENABLE = 'magenasani_bannerslider/general/enabled';
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
