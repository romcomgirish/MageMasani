<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Controller\Adminhtml\Campaign\Image;

use MageMasani\PushNotification\Model\ImageUploader;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Upload extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'MageMasani_PushNotification::campaign';

    /**
     * @var ImageUploader
     */
    private ImageUploader $imageUploader;

    /**
     * @param Action\Context $context
     * @param ImageUploader $imageUploader
     */
    public function __construct(
        Action\Context $context,
        ImageUploader $imageUploader
    ) {
        $this->imageUploader = $imageUploader;
        parent::__construct($context);
    }

    /**
     * Upload file controller action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $imageId = $this->getRequest()->getParam('param_name', 'image_url');
        if (!isset($_FILES[$imageId]) && !empty($_FILES)) {
            $imageId = (string) key($_FILES);
        }
        try {
            $result = $this->imageUploader->saveFileToTmpDir($imageId);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $resultJson->setData($result);
    }
}
