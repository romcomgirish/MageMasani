<?php
declare(strict_types=1);

namespace MageMasani\BannerSlider\Block\Adminhtml\Banner\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Banner ResetButton Class
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
        if (!$this->getBannerId()) {
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
