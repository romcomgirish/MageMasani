<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Controller\Adminhtml\Campaign;

use MageMasani\PushNotification\Api\CampaignRepositoryInterface;
use MageMasani\PushNotification\Model\ResourceModel\Campaign\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

class MassEnable extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageMasani_PushNotification::campaign';

    /**
     * @var Filter
     */
    protected Filter $filter;

    /**
     * @var CollectionFactory
     */
    public CollectionFactory $collectionFactory;

    /**
     * @var CampaignRepositoryInterface
     */
    private CampaignRepositoryInterface $campaignRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CampaignRepositoryInterface $campaignRepository
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CampaignRepositoryInterface $campaignRepository
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->campaignRepository = $campaignRepository;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $itemsSaved = 0;
            foreach ($collection as $item) {
                try {
                    if ($item->getStatus() !== 1) {
                        $item->setStatus(1);
                        $this->campaignRepository->save($item);
                        $itemsSaved++;
                    }
                } catch (CouldNotSaveException $e) {
                    $this->messageManager->addErrorMessage(
                        __('Error saving %1: %2', $item->getEntityId(), $e->getMessage())
                    );
                }
            }
            if ($itemsSaved) {
                $this->messageManager->addSuccessMessage(__('%1 Campaign(s) enabled', $itemsSaved));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred while enabling selected campaigns: %1', $e->getMessage()));
        }
        return $resultRedirect->setPath('*/*/');
    }
}
