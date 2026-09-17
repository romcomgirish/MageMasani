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

namespace MageMasani\BannerSlider\Block\Adminhtml\Slider\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Slider ResetButton Class
 */
class ResetButton extends GenericButton implements ButtonProviderInterface
{

    /**
     * Retrieve button-specified settings
     *
     * @return array
     */
    public function getButtonData(): array
    {
        $data = [];
        if (!$this->getSliderId()) {
            $data = [
                'label' => __('Reset'),
                'class' => 'reset',
                'on_click' => 'location.reload();',
                'sort_order' => 30
            ];
        }
        return $data;
    }
}
