<?php

namespace MageMasani\BannerSlider\Controller\Adminhtml\Banner;

use MageMasani\BannerSlider\Api\BannerRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use MageMasani\BannerSlider\Model\ResourceModel\Banner\CollectionFactory;

class MassDisable extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageMasani_BannerSlider::save';

    /**
     * @var Filter
     */
    protected Filter $filter;

    /**
     * @var CollectionFactory
     */
    public CollectionFactory $collectionFactory;

    /**
     * @var BannerRepositoryInterface
     */
    private BannerRepositoryInterface $bannerRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param BannerRepositoryInterface $bannerRepository
     */
    public function __construct(
        Action\Context           $context,
        Filter                    $filter,
        CollectionFactory         $collectionFactory,
        BannerRepositoryInterface $bannerRepository
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->bannerRepository = $bannerRepository;
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
            $itemsSaved = 0;
            foreach ($collection as $item) {
                try {
                    if ($item->getIsEnabled()) {
                        $item->setIsEnabled(false);
                        $this->bannerRepository->save($item);
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
