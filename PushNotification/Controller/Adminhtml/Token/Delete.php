<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Controller\Adminhtml\Token;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use MageMasani\PushNotification\Model\ResourceModel\Token as TokenResource;
use MageMasani\PushNotification\Model\TokenFactory;

/**
 * Controller Delete
 */
class Delete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level
     */
    public const ADMIN_RESOURCE = 'MageMasani_PushNotification::token';

    /**
     * @var TokenFactory
     */
    private TokenFactory $tokenFactory;

    /**
     * @var TokenResource
     */
    private TokenResource $tokenResource;

    /**
     * @param Context $context
     * @param TokenFactory $tokenFactory
     * @param TokenResource $tokenResource
     */
    public function __construct(
        Context $context,
        TokenFactory $tokenFactory,
        TokenResource $tokenResource
    ) {
        parent::__construct($context);
        $this->tokenFactory = $tokenFactory;
        $this->tokenResource = $tokenResource;
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $id = (int) $this->getRequest()->getParam('entity_id');

        if ($id) {
            try {
                $token = $this->tokenFactory->create();
                $this->tokenResource->load($token, $id);
                $this->tokenResource->delete($token);
                $this->messageManager->addSuccessMessage(__('The token has been deleted.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
