<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Controller\Adminhtml\Campaign;

use MageMasani\PushNotification\Api\CampaignRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageMasani_PushNotification::campaign';

    /**
     * @var CampaignRepositoryInterface
     */
    private CampaignRepositoryInterface $campaignRepository;

    /**
     * @param Action\Context $context
     * @param CampaignRepositoryInterface $campaignRepository
     */
    public function __construct(
        Action\Context $context,
        CampaignRepositoryInterface $campaignRepository
    ) {
        parent::__construct($context);
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * Delete action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->campaignRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('Campaign with ID %1 deleted successfully', $id));
                return $resultRedirect->setPath('*/*');
            } catch (CouldNotDeleteException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*');
            }
        }
        return $resultRedirect->setPath('*/*');
    }
}
