<?php

namespace MageMasani\BannerSlider\Controller\Adminhtml\Banner;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Banner NewAction Class
 */
class NewAction extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageMasani_BannerSlider::save';

    /**
     * Forward to edit
     *
     * @return ResponseInterface|Forward|ResultInterface
     */
    public function execute()
    {
        /** @var Forward $resultForwardFactory */
        $resultForwardFactory = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        return $resultForwardFactory->forward('edit');
    }
}
