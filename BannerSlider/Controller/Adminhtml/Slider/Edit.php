<?php

namespace MageMasani\BannerSlider\Controller\Adminhtml\Slider;

use MageMasani\BannerSlider\Api\SliderRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\Page;

class Edit extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageMasani_BannerSlider::save';

    /**
     * @var SliderRepositoryInterface
     */
    private SliderRepositoryInterface $sliderRepository;

    /**
     * Edit constructor.
     * @param Action\Context $context
     * @param SliderRepositoryInterface $sliderRepository
     */
    public function __construct(
        Action\Context $context,
        SliderRepositoryInterface $sliderRepository
    ) {
        $this->sliderRepository = $sliderRepository;
        parent::__construct($context);
    }

    /**
     * Edit banner.
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
                $slider = $this->sliderRepository->getById($id);
                $page->getConfig()->getTitle()->set(
                    __('Edit Slider "%1" (%2)', $slider->getTitle(), $slider->getEntityId())
                );
            } else {
                $page->getConfig()->getTitle()->set(__('Create New Slider'));
            }
            return $page;
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('The slider you\'re looking for does not exist'));
            return $this->resultRedirectFactory->create()->setPath('*/*');
        }
    }
}
