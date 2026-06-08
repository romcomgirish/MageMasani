<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Controller\Adminhtml\History;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Index extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageMasani_PushNotification::history';

    /**
     * Index action
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('MageMasani_PushNotification::history');
        $resultPage->getConfig()->getTitle()->prepend(__('Notification History'));
        return $resultPage;
    }
}
