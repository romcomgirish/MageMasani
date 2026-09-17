<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Controller\Adminhtml\Send;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Controller Index
 */
class Index extends Action
{
    /**
     * Admin resource constant
     *
     * @var string
     */
    public const ADMIN_RESOURCE = 'MageMasani_PushNotification::send';

    /**
     * @var PageFactory
     */
    private PageFactory $resultPageFactory;

    /**
     * Initialize dependencies
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @return void
     */
    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute action
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('MageMasani_PushNotification::send');
        $resultPage->getConfig()->getTitle()->prepend(__('Send Push Notification'));
        return $resultPage;
    }
}
