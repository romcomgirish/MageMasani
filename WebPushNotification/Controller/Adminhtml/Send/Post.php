<?php
declare(strict_types=1);

namespace MageMasani\WebPushNotification\Controller\Adminhtml\Send;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use MageMasani\WebPushNotification\Model\Sender;
use Psr\Log\LoggerInterface;

class Post extends Action
{
    public const ADMIN_RESOURCE = 'MageMasani_WebPushNotification::send';

    private const MAX_ERROR_BODY_DISPLAY = 500;
    private const MAX_ERROR_MESSAGES_SHOWN = 5;
    private const ALLOWED_IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    private Sender $sender;
    private UploaderFactory $uploaderFactory;
    private Filesystem $filesystem;
    private StoreManagerInterface $storeManager;
    private Escaper $escaper;
    private LoggerInterface $logger;

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

    public function execute()
    {
        $title = trim((string)$this->getRequest()->getParam('title'));
        $body = trim((string)$this->getRequest()->getParam('body'));

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
                $result = $this->sender->send($title, $body, '/', $imageUrl);
            } catch (\Throwable $e) {
                throw new \RuntimeException('FCM send failed: ' . $e->getMessage(), 0, $e);
            }

            $this->messageManager->addSuccessMessage(
                __('Push sent. Success: %1, Failure: %2', $result['success'], $result['failure'])
            );

            if (!empty($result['errors'])) {
                foreach (array_slice($result['errors'], 0, self::MAX_ERROR_MESSAGES_SHOWN) as $err) {
                    $tok = (string)($err['token'] ?? '');
                    $tokDisplay = strlen($tok) > 24
                        ? substr($tok, 0, 12) . '…' . substr($tok, -8)
                        : $tok;
                    $this->messageManager->addErrorMessage(sprintf(
                        'Token %s HTTP %d — %s',
                        $this->escaper->escapeHtml($tokDisplay),
                        (int)($err['status'] ?? 0),
                        $this->escaper->escapeHtml(substr((string)($err['body'] ?? ''), 0, self::MAX_ERROR_BODY_DISPLAY))
                    ));
                }
            }
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage($this->escaper->escapeHtml($e->getMessage()));
            $this->logger->error('[WebPush] send failed: ' . $e->getMessage(), ['exception' => $e]);
        }

        return $this->resultRedirectFactory->create()->setPath('magemasani_webpush/send/index');
    }

    private function handleImageUpload(): string
    {
        if (empty($_FILES['image']['name'])) {
            return '';
        }

        $errorCode = (int)($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE);
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
