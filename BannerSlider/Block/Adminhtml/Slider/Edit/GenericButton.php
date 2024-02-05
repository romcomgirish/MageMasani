<?php
declare(strict_types=1);

namespace MageMasani\BannerSlider\Block\Adminhtml\Slider\Edit;

use Magento\Backend\Block\Widget\Context;

/**
 * Slider GenericButton Class
 */
abstract class GenericButton
{
    /**
     * @var Context
     */
    protected Context $context;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * Return model ID
     *
     * @return mixed
     */
    public function getSliderId(): mixed
    {
        return $this->context->getRequest()->getParam('entity_id');
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
