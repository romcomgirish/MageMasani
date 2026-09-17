<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Block\Adminhtml\Campaign\Edit;

use Magento\Backend\Block\Widget\Context;

/**
 * Block GenericButton
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
     * Return campaign ID
     *
     * @return mixed
     */
    public function getCampaignId(): mixed
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
