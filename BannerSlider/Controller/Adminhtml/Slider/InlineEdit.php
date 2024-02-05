<?php
declare(strict_types=1);

namespace MageMasani\BannerSlider\Controller\Adminhtml\Slider;

use MageMasani\BannerSlider\Api\SliderRepositoryInterface;
use MageMasani\BannerSlider\Api\Data\SliderInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Slider Inline Class
 */
class InlineEdit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageMasani_BannerSlider::save';

    /**
     * @var JsonFactory
     */
    protected JsonFactory $jsonFactory;

    /**
     * @var SliderRepositoryInterface
     */
    private SliderRepositoryInterface $sliderRepository;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param SliderRepositoryInterface $sliderRepository
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        SliderRepositoryInterface $sliderRepository
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->sliderRepository = $sliderRepository;
    }

    /**
     * Inline edit action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $EntityId) {
                    $model = $this->sliderRepository->getById($EntityId);
                    try {
                        $formData = $postItems[$EntityId];
                        $model->setData($formData);
                        $this->sliderRepository->save($model);
                    } catch (\RuntimeException $e) {
                        $messages[] = $this->getErrorWithCustomFormId($model, $e->getMessage());
                        $error = true;
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithCustomFormId(
                            $model,
                            (string)__('Something went wrong while saving the CustomForm.')
                        );
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add slider ID to error message
     *
     * @param SliderInterface $model
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithCustomFormId(SliderInterface $model, string $errorText): string
    {
        return '[CustomForm ID: ' . $model->getId() . '] ' . $errorText;
    }
}
