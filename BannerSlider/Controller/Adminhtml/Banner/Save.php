<?php

namespace MageMasani\BannerSlider\Controller\Adminhtml\Banner;

use MageMasani\BannerSlider\Api\BannerRepositoryInterface;
use MageMasani\BannerSlider\Model\ImageUploader;
use Magento\Backend\App\Action;
use Magento\Cms\Helper\Wysiwyg\Images as WysiwygImageHelper;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Save extends Action implements HttpPostActionInterface
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
    private DataPersistorInterface $dataPersistor;

    /**
     * @var BannerRepositoryInterface
     */
    private BannerRepositoryInterface $bannerRepository;

    /**
     * @var ImageUploader
     */
    private ImageUploader $imageUploader;

    /**
     * @var WysiwygImageHelper
     */
    private WysiwygImageHelper $wysiwygImageHelper;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @param Action\Context $context
     * @param BannerRepositoryInterface $bannerRepository
     * @param DataPersistorInterface $dataPersistor
     * @param ImageUploader $imageUploader
     * @param WysiwygImageHelper $wysiwygImageHelper
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Action\Context $context,
        BannerRepositoryInterface $bannerRepository,
        DataPersistorInterface $dataPersistor,
        ImageUploader $imageUploader,
        WysiwygImageHelper $wysiwygImageHelper,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->bannerRepository = $bannerRepository;
        $this->imageUploader = $imageUploader;
        $this->wysiwygImageHelper = $wysiwygImageHelper;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        try {
            if ($id) {
                $model = $this->bannerRepository->loadById($id);
            } else {
                $model = $this->bannerRepository->create();
            }
            $this->populateModelWithData($model, [
                'slider_id',
                'title',
                'resource_type',
                'alt_text',
                'link_type',
                'sort_order',
                'is_enabled',
                'start_date',
                'end_date'
            ]);
            $data = $this->getRequest()->getParams();
            if ($model->getResourceType() == 'external_image') {
                if (!empty($externalImage = $this->getRequest()->getParam('resource_path_external_image'))) {
                    $model->setData('resource_path', $externalImage);
                }
            } elseif ($model->getResourceType() == 'youtube_video') {
                if (!empty($youtubeVideo = $this->getRequest()->getParam('resource_path_youtube_video'))) {
                    $model->setData('resource_path', $youtubeVideo);
                }
            } elseif ($model->getResourceType() == 'custom_html') {
                if (!empty($customHtml = $this->getRequest()->getParam('resource_path_custom_html'))) {
                    $model->setData('resource_path', $customHtml);
                }
            } elseif ($model->getResourceType() == 'local_image') {
                if (!empty($localImage = $this->getRequest()->getParam('resource_path_local_image'))) {
                    $image =$this->setLocalImage($localImage);
                    $model->setData('resource_path', $image);
                }
            }

            if ($model->getLinkType() == 'link_type_product') {
                if (!empty($product = $this->getRequest()->getParam('link_type_resource_product'))) {
                    $model->setData('link_type_resource', $product);
                }
            } elseif ($model->getLinkType() == 'link_type_category') {
                if (!empty($category = $this->getRequest()->getParam('link_type_resource_category'))) {
                    $model->setData('link_type_resource', $category);
                }
            } elseif ($model->getLinkType() == 'link_type_custom') {
                if (!empty($custom = $this->getRequest()->getParam('link_type_resource_custom'))) {
                    $model->setData('link_type_resource', $custom);
                }
            }
            $this->dataPersistor->set('bannerslider_banner', $model->getData());
            $model = $this->bannerRepository->save($model);
            $this->dataPersistor->clear('bannerslider_banner');
            $this->messageManager->addSuccessMessage(__('Banner %1 saved successfully', $model->getEntityId()));
            switch ($this->getRequest()->getParam('back')) {
                case 'continue':
                    $url = $this->getUrl('*/*/edit', ['entity_id' => $model->getEntityId()]);
                    break;
                case 'close':
                    $url = $this->getUrl('*/*');
                    break;
                default:
                    $url = $this->getUrl('*/*');
            }
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $url = $this->getUrl('*/*');
        } catch (CouldNotSaveException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $url = $this->getUrl('*/*/edit', ['entity_id' => $id]);
        }
        return $this->resultRedirectFactory->create()->setUrl($url);
    }

    /**
     * Populate model with data
     *
     * @param DataObject $model
     * @param string[] $fields
     */
    protected function populateModelWithData(DataObject $model, array $fields)
    {
        foreach ($fields as $field) {
            $model->setData($field, $this->getRequest()->getParam($field));
        }
    }

    /**
     * Set local image.
     *
     * @param array $localImage
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setLocalImage(array $localImage): string
    {
        $tmpFile = $localImage[0]['tmp_name'] ?? null;
        $imageName = $localImage[0]['name'] ?? null;
        $imageUrl = $localImage[0]['url'] ?? null;
        if ($tmpFile && $imageName) {
            return $this->imageUploader->getBasePath() . '/' . $this->imageUploader->moveFileFromTmp($imageName);
        } elseif ($imageUrl) {
            $store = $this->storeManager->getStore();
            $ds = DIRECTORY_SEPARATOR;
            $wysiwygMediaRoot = trim($this->wysiwygImageHelper->getStorageRoot(), $ds);
            $pubPath = trim($this->filesystem->getDirectoryRead(DirectoryList::PUB)->getAbsolutePath(), $ds);
            $mediaPath = $this->replaceRegex($pubPath, $wysiwygMediaRoot) . $ds;
            $mediaUrl = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
            return $this->replaceRegex(
                $mediaUrl,
                $this->replaceRegex(
                    $mediaPath,
                    $imageUrl
                )
            );
        } else {
            return '';
        }
    }

    /**
     * Replace regex.
     *
     * @param string $pattern
     * @param string $subject
     * @param string $replacement
     * @return string
     */
    protected function replaceRegex(string $pattern, string $subject, string $replacement = ''): string
    {
        return preg_replace('/^' . preg_quote($pattern, '/') . '/', $replacement, $subject);
    }
}
