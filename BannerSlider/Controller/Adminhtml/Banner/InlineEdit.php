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

namespace MageMasani\BannerSlider\Controller\Adminhtml\Banner;

use Exception;
use MageMasani\BannerSlider\Api\BannerRepositoryInterface;
use MageMasani\BannerSlider\Api\Data\BannerInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use RuntimeException;

/**
 * Banner InlineEdit Class
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
     * @var BannerRepositoryInterface
     */
    private BannerRepositoryInterface $bannerRepository;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param BannerRepositoryInterface $bannerRepository
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        BannerRepositoryInterface $bannerRepository
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->bannerRepository = $bannerRepository;
    }

    /**
     * InlineEdit action
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
                        $model = $this->bannerRepository->getById((int) $entityId);
                        $model->setData(array_merge($model->getData(), $formData));
                        $this->bannerRepository->save($model);
                    } catch (NoSuchEntityException $e) {
                        $messages[] = sprintf('[Banner ID: %s] %s', $entityId, $e->getMessage());
                        $error = true;
                    } catch (RuntimeException $e) {
                        $messages[] = sprintf('[Banner ID: %s] %s', $entityId, $e->getMessage());
                        $error = true;
                    } catch (Exception $e) {
                        $messages[] = sprintf('[Banner ID: %s] %s', $entityId, __('Something went wrong while saving the Banner.'));
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
     * Add Banner ID to error message
     *
     * @param BannerInterface $model
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithBannersId(BannerInterface $model, string $errorText): string
    {
        return '[Banner ID: ' . $model->getId() . '] ' . $errorText;
    }
}
