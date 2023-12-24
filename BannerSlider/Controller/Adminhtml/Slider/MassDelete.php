<?php

namespace MageMasani\BannerSlider\Controller\Adminhtml\Slider;

use MageMasani\BannerSlider\Api\SliderRepositoryInterface;
use MageMasani\BannerSlider\Model\ResourceModel\Slider\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageMasani_BannerSlider::delete';

    /**
     * @var Filter
     */
    protected Filter $filter;

    /**
     * @var CollectionFactory
     */
    public CollectionFactory $collectionFactory;

    /**
     * @var SliderRepositoryInterface
     */
    private SliderRepositoryInterface $sliderRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param SliderRepositoryInterface $sliderRepository
     */
    public function __construct(
        Action\Context            $context,
        Filter                    $filter,
        CollectionFactory         $collectionFactory,
        SliderRepositoryInterface $sliderRepository
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->sliderRepository = $sliderRepository;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return Redirect
     * @throws LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        try {
            $itemsDeleted = 0;
            foreach ($collection as $item) {
                try {
                    $this->sliderRepository->delete($item);
                    $itemsDeleted++;
                } catch (CouldNotDeleteException $e) {
                    $this->messageManager->addErrorMessage(
                        __('Error Deleting %1: %2', $item->getEntityId(), $e->getMessage())
                    );
                }
            }
            $this->messageManager->addSuccessMessage(__('%1 Banner(s) deleted', $itemsDeleted));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
