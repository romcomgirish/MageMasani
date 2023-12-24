<?php

namespace MageMasani\BannerSlider\Controller\Adminhtml\Slider;

use MageMasani\BannerSlider\Api\SliderRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use MageMasani\BannerSlider\Model\ResourceModel\Slider\CollectionFactory;

class MassEnable extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageMasani_BannerSlider::save';

    /**
     * @var CollectionFactory
     */
    public CollectionFactory $collectionFactory;

    /**
     * @var Filter
     */
    protected Filter $filter;

    /**
     * @var SliderRepositoryInterface
     */
    private SliderRepositoryInterface $sliderRepository;

    /**
     * @param Action\Context $context
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
        parent::__construct($context);
        $this->sliderRepository = $sliderRepository;
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
            $itemsSaved = 0;
            foreach ($collection as $item) {
                try {
                    if ($item->getIsEnabled()) {
                        $item->setIsEnabled(true);
                        $this->sliderRepository->save($item);
                        $itemsSaved++;
                    }
                } catch (CouldNotSaveException $e) {
                    $this->messageManager->addErrorMessage(
                        __('Error saving %1: %2', $item->getEntityId(), $e->getMessage())
                    );
                }
            }
            $this->messageManager->addSuccessMessage(__('%1 Banner(s) disabled', $itemsSaved));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/');
    }
}
