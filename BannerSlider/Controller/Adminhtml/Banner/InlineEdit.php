<?php
declare(strict_types=1);

namespace MageMasani\BannerSlider\Controller\Adminhtml\Banner;

use MageMasani\BannerSlider\Api\BannerRepositoryInterface;
use MageMasani\BannerSlider\Api\Data\BannerInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

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
     * @param Action\Context $context
     * @param JsonFactory $jsonFactory
     * @param BannerRepositoryInterface $bannerRepository
     */
    public function __construct(
        Action\Context $context,
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
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $EntityId) {
                    $model = $this->bannerRepository->getById($EntityId);
                    try {
                        $formData = $postItems[$EntityId];
                        $model->setData($formData);
                        $this->bannerRepository->save($model);
                    } catch (\RuntimeException $e) {
                        $messages[] = $this->getErrorWithBannersId($model, $e->getMessage());
                        $error = true;
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithBannersId(
                            $model,
                            (string)__('Something went wrong while saving the Banner.')
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
     * Add Banner ID to error message
     *
     * @param BannerInterface $model
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithBannersId(BannerInterface $model, string $errorText): string
    {
        return '[Banners ID: ' . $model->getId() . '] ' . $errorText;
    }
}
