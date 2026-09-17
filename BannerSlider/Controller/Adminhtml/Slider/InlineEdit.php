<?php
/**
 * MageMasani BannerSlider Module
 *
 * @category  MageMasani
 * @package   MageMasani_BannerSlider
 * @author    MageMasani <support@magemasani.com>
 * @copyright Copyright (c) MageMasani (https://www.magemasani.com/)
 * @license   GPL-3.0-or-later
 */

declare(strict_types=1);

namespace MageMasani\BannerSlider\Controller\Adminhtml\Slider;

use Exception;
use MageMasani\BannerSlider\Api\SliderRepositoryInterface;
use MageMasani\BannerSlider\Api\Data\SliderInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use RuntimeException;

/**
 * Slider InlineEdit Class
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
        Context $context,
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
            if (!is_array($postItems) || !count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach ($postItems as $entityId => $formData) {
                    try {
                        $model = $this->sliderRepository->getById((int) $entityId);
                        $model->setData(array_merge($model->getData(), $formData));
                        $this->sliderRepository->save($model);
                    } catch (NoSuchEntityException $e) {
                        $messages[] = sprintf('[Slider ID: %s] %s', $entityId, $e->getMessage());
                        $error = true;
                    } catch (RuntimeException $e) {
                        $messages[] = sprintf('[Slider ID: %s] %s', $entityId, $e->getMessage());
                        $error = true;
                    } catch (Exception $e) {
                        $messages[] = sprintf('[Slider ID: %s] %s', $entityId, __('Something went wrong while saving the Slider.'));
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
    protected function getErrorWithSliderId(SliderInterface $model, string $errorText): string
    {
        return '[Slider ID: ' . $model->getId() . '] ' . $errorText;
    }
}
