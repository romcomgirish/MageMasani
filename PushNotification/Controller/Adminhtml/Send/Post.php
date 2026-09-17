<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Controller\Adminhtml\Send;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use MageMasani\PushNotification\Model\Sender;
use Psr\Log\LoggerInterface;

/**
 * Controller Post
 */
class Post extends Action
{
    /**
     * Admin resource constant
     *
     * @var string
     */
    public const ADMIN_RESOURCE = 'MageMasani_PushNotification::send';

    /**
     * Max error body display constant
     *
     * @var int
     */
    private const MAX_ERROR_BODY_DISPLAY = 500;
    /**
     * Max error messages shown constant
     *
     * @var int
     */
    private const MAX_ERROR_MESSAGES_SHOWN = 5;
    /**
     * Allowed image extensions constant
     *
     * @var array
     */
    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * @var Sender
     */
    private Sender $sender;
    /**
     * @var UploaderFactory
     */
    private UploaderFactory $uploaderFactory;
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;
    /**
     * @var Escaper
     */
    private Escaper $escaper;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Initialize dependencies
     *
     * @param Context $context
     * @param Sender $sender
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param Escaper $escaper
     * @param LoggerInterface $logger
     * @return void
     */
    public function __construct(
        Context $context,
        Sender $sender,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        Escaper $escaper,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->sender = $sender;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
        $this->logger = $logger;
    }

    /**
     * Execute action
     */
    public function execute()
    {
        $title = trim((string) $this->getRequest()->getParam('title'));
        $body = trim((string) $this->getRequest()->getParam('body'));
        $sendTo = $this->getRequest()->getParam('send_to', 'all');
        $customerId = $this->getRequest()->getParam('customer_id');

        try {
            if ($title === '' || $body === '') {
                throw new \RuntimeException('Title and body are required.');
            }

            try {
                $imageUrl = $this->handleImageUpload();
            } catch (\Throwable $e) {
                throw new \RuntimeException('Image upload failed: ' . $e->getMessage(), 0, $e);
            }

            if ($imageUrl !== '') {
                $this->messageManager->addNoticeMessage(__('Image URL: %1', $imageUrl));
            }

            try {
                if ($sendTo === 'customer') {
                    if (empty($customerId)) {
                        throw new \RuntimeException('Customer ID is required when sending to a specific customer.');
                    }
                    $result = $this->sender->sendToCustomer((int) $customerId, $title, $body, '/', $imageUrl, 'manual');
                } else {
                    $result = $this->sender->send($title, $body, '/', $imageUrl, 'manual');
                }
            } catch (\Throwable $e) {
                throw new \RuntimeException('FCM send failed: ' . $e->getMessage(), 0, $e);
            }

            if ($result['success'] === 0 && $result['failure'] === 0) {
                $this->messageManager->addWarningMessage(
                    __('No active push tokens found for the recipient.')
                );
            } else {
                $this->messageManager->addSuccessMessage(
                    __('Push sent. Success: %1, Failure: %2', $result['success'], $result['failure'])
                );
            }

            if (!empty($result['errors'])) {
                foreach (array_slice($result['errors'], 0, self::MAX_ERROR_MESSAGES_SHOWN) as $err) {
                    $tok = (string) ($err['token'] ?? '');
                    $tokDisplay = strlen($tok) > 24
                        ? substr($tok, 0, 12) . '…' . substr($tok, -8)
                        : $tok;
                    $this->messageManager->addErrorMessage(sprintf(
                        'Token %s HTTP %d — %s',
                        $this->escaper->escapeHtml($tokDisplay),
                        (int) ($err['status'] ?? 0),
                        $this->escaper->escapeHtml(substr((string) ($err['body'] ?? ''), 0, self::MAX_ERROR_BODY_DISPLAY))
                    ));
                }
            }
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage($this->escaper->escapeHtml($e->getMessage()));
            $this->logger->error('[WebPush] send failed: ' . $e->getMessage(), ['exception' => $e]);
        }

        return $this->resultRedirectFactory->create()->setPath('magemasani_webpush/send/index');
    }

    /**
     * Handleimageupload
     *
     * @return string
     */
    private function handleImageUpload(): string
    {
        $file = $this->getRequest()->getFiles('image');
        if (empty($file['name'])) {
            return '';
        }

        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('PHP upload error code ' . $errorCode
                . ' (check php.ini upload_max_filesize / post_max_size).');
        }

        $uploader = $this->uploaderFactory->create(['fileId' => 'image']);
        $uploader->setAllowedExtensions(self::ALLOWED_IMAGE_EXTENSIONS);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);

        $mediaDir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $targetPath = $mediaDir->getAbsolutePath('webpush');
        $result = $uploader->save($targetPath);

        if (empty($result['file'])) {
            throw new \RuntimeException('Uploader returned no file path.');
        }

        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return rtrim($mediaUrl, '/') . '/webpush/' . ltrim($result['file'], '/');
    }
}
