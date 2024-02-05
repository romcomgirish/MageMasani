<?php

namespace MageMasani\BannerSlider\Controller\Adminhtml\Slider;

use MageMasani\BannerSlider\Api\SliderRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Slider Delete Class
 */
class Delete extends Action implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'MageMasani_BannerSlider::slider';

    /**
     * @var SliderRepositoryInterface
     */
    private SliderRepositoryInterface $sliderRepository;

    /**
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
     * Delete action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        if ($id) {
            try {
                $this->sliderRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('Slider with ID %1 deleted successfully', $id));
                $url = $this->getUrl('*/*');
            } catch (CouldNotDeleteException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $url = $this->getUrl('*/*/edit', ['entity_id' => $id]);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $url = $this->getUrl('*/*');
            }
        } else {
            $url = $this->getUrl('*/*');
        }
        return $this->resultRedirectFactory->create()->setUrl($url);
    }
}
