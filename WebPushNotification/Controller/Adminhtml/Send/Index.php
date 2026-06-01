<?php
declare(strict_types=1);

namespace MageMasani\WebPushNotification\Controller\Adminhtml\Send;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    public const ADMIN_RESOURCE = 'MageMasani_WebPushNotification::send';

    private PageFactory $resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MageMasani_WebPushNotification::send');
        $resultPage->getConfig()->getTitle()->prepend(__('Send Web Push Notification'));
        return $resultPage;
    }
}
