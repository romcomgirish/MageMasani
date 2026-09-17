<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model\Campaign;

use MageMasani\PushNotification\Model\ResourceModel\Campaign\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem\Io\File;
use MageMasani\PushNotification\Model\ImageUploader;
use Magento\Framework\UrlInterface;

/**
 * Model DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private DataPersistorInterface $dataPersistor;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var Mime
     */
    private Mime $mime;

    /**
     * @var File
     */
    private File $file;

    /**
     * @var ImageUploader
     */
    private ImageUploader $imageUploader;

    /**
     * @var array|null
     */
    protected ?array $loadedData = null;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param Mime $mime
     * @param File $file
     * @param ImageUploader $imageUploader
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        Mime $mime,
        File $file,
        ImageUploader $imageUploader,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->mime = $mime;
        $this->file = $file;
        $this->imageUploader = $imageUploader;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        $items = $this->collection->getItems();
        foreach ($items as $item) {
            $campaignData = $item->getData();
            if (isset($campaignData['image_url']) && $campaignData['image_url'] !== '') {
                $imageName = $campaignData['image_url'];
                $store = $this->storeManager->getStore();
                $mediaPath = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
                $folderPath = $this->imageUploader->getBasePath();
                $imageWithFolder = $folderPath . '/' . $imageName;
                $fullImagePath = $mediaPath . $imageWithFolder;
                $fileName = $this->filesystem->getDirectoryRead(
                    DirectoryList::MEDIA
                )->getAbsolutePath($imageWithFolder);

                if ($this->file->fileExists($fileName)) {
                    $campaignData['image_url'] = [
                        [
                            'name' => basename($fileName),
                            'url' => $fullImagePath,
                            'size' => filesize($fileName),
                            'type' => $this->mime->getMimeType($fileName),
                            'is_saved' => true
                        ]
                    ];
                }
            }
            $this->loadedData[$item->getEntityId()] = $campaignData;
        }

        $data = $this->dataPersistor->get('pushnotification_campaign');
        if (!empty($data)) {
            $campaign = $this->collection->getNewEmptyItem();
            $campaign->setData($data);

            $campaignData = $campaign->getData();
            if (isset($campaignData['image_url']) && is_string($campaignData['image_url']) && $campaignData['image_url'] !== '') {
                $imageName = $campaignData['image_url'];
                $store = $this->storeManager->getStore();
                $mediaPath = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
                $folderPath = $this->imageUploader->getBasePath();
                $imageWithFolder = $folderPath . '/' . $imageName;
                $fullImagePath = $mediaPath . $imageWithFolder;
                $fileName = $this->filesystem->getDirectoryRead(
                    DirectoryList::MEDIA
                )->getAbsolutePath($imageWithFolder);

                if ($this->file->fileExists($fileName)) {
                    $campaignData['image_url'] = [
                        [
                            'name' => basename($fileName),
                            'url' => $fullImagePath,
                            'size' => filesize($fileName),
                            'type' => $this->mime->getMimeType($fileName),
                            'is_saved' => true
                        ]
                    ];
                }
            }

            $this->loadedData[$campaign->getEntityId()] = $campaignData;
            $this->dataPersistor->clear('pushnotification_campaign');
        }

        return $this->loadedData;
    }
}
