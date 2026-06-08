<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Controller\Adminhtml\Campaign;

use MageMasani\PushNotification\Api\CampaignRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Page;

class Edit extends Action implements HttpGetActionInterface
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
        $this->campaignRepository = $campaignRepository;
        parent::__construct($context);
    }

    /**
     * Edit action.
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        try {
            /** @var Page $page */
            $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $page->setActiveMenu('MageMasani_PushNotification::campaign');
            $id = $this->getRequest()->getParam('entity_id');
            if ($id) {
                $campaign = $this->campaignRepository->getById($id);
                $page->getConfig()->getTitle()->set(
                    __('Edit Campaign "%1" (%2)', $campaign->getTitle(), $campaign->getEntityId())
                );
            } else {
                $page->getConfig()->getTitle()->set(__('Create New Campaign'));
            }
            return $page;
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('The campaign you\'re looking for does not exist'));
            return $this->resultRedirectFactory->create()->setPath('*/*');
        }
    }
}
