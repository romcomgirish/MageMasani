<?php

namespace MageMasani\BannerSlider\Controller\Adminhtml\Banner;

use MageMasani\BannerSlider\Api\BannerRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\Page;

/**
 * Banner Edit Class
 */
class Edit extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageMasani_BannerSlider::save';

    /**
     * @var BannerRepositoryInterface
     */
    private BannerRepositoryInterface $bannerRepository;

    /**
     * @param Action\Context $context
     * @param BannerRepositoryInterface $bannerRepository
     */
    public function __construct(
        Action\Context $context,
        BannerRepositoryInterface $bannerRepository
    ) {
        $this->bannerRepository = $bannerRepository;
        parent::__construct($context);
    }

    /**
     * Edit action.
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        try {
            /** @var Page $page */
            $page = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $id = $this->getRequest()->getParam('entity_id');
            if ($id) {
                $slider = $this->bannerRepository->getById($id);
                $page->getConfig()->getTitle()->set(
                    __('Edit Banner "%1" (%2)', $slider->getTitle(), $slider->getEntityId())
                );
            } else {
                $page->getConfig()->getTitle()->set(__('Create New Banner'));
            }
            return $page;
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('The banner you\'re looking for does not exist'));
            return $this->resultRedirectFactory->create()->setPath('*/*');
        }
    }
}
