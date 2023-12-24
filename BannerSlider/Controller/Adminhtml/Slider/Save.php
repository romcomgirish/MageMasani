<?php
declare(strict_types=1);

namespace MageMasani\BannerSlider\Controller\Adminhtml\Slider;

use MageMasani\BannerSlider\Api\SliderRepositoryInterface;
use MageMasani\BannerSlider\Model\SliderFactory;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageMasani_BannerSlider::save';

    /**
     * @var DataPersistorInterface
     */
    protected DataPersistorInterface $dataPersistor;

    /**
     * @var SliderFactory
     */
    private SliderFactory $sliderFactory;

    /**
     * @var SliderRepositoryInterface
     */
    private SliderRepositoryInterface $sliderRepository;

    /**
     * @param Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param SliderFactory $sliderFactory
     * @param SliderRepositoryInterface $sliderRepository
     */
    public function __construct(
        Action\Context $context,
        DataPersistorInterface $dataPersistor,
        SliderFactory $sliderFactory,
        SliderRepositoryInterface $sliderRepository
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->sliderFactory = $sliderFactory;
        $this->sliderRepository = $sliderRepository;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('entity_id');
            if (empty($data['entity_id'])) {
                $data['entity_id'] = null;
            }
            try {
                $model = $id ? $this->sliderRepository->getById($id): $this->sliderFactory->create();
            } catch (LocalizedException|\Exception $e) {
                $this->messageManager->addErrorMessage(__('This testimonial no longer exists.'), $e);
                return $resultRedirect->setPath('*/*/');
            }
            $model->setData($data);

            try {
                $this->sliderRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the CustomForm.'));
                $this->dataPersistor->clear('bannerslider_slider');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getEntityId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the CustomForm.'));
            }
            $this->dataPersistor->set('bannerslider_slider', $data);
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
