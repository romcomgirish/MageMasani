<?php

namespace MageMasani\BannerSlider\Ui\DataProvider\Banner\Form\Modifier;

use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class LocalImage implements ModifierInterface
{
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;
    /**
     * @var Mime
     */
    private Mime $mime;
    /**
     * @var File
     */
    private File $file;

    /**
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param Mime $mime
     * @param File $file
     */
    public function __construct(
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        Mime $mime,
        File $file
    ) {
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->mime = $mime;
        $this->file = $file;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data): array
    {
        foreach ($data as &$item) {
            $item = $this->processRow($item);
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    protected function processRow($data): array
    {
        $resourcePath = $data['resource_path'] ?? null;
        $resourceType = $data['resource_type'];
        if ($resourcePath && $resourceType === 'local_image') {
            $store = $this->storeManager->getStore();
            $url = $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $resourcePath;
            $fileName = $this->filesystem->getDirectoryRead(
                DirectoryList::MEDIA
            )->getAbsolutePath($resourcePath);
            if ($this->file->fileExists($fileName)) {
                $resourcePathData = [
                    'name' => basename($fileName),
                    'url' => $url,
                    'size' => filesize($fileName),
                    'type' => $this->mime->getMimeType($fileName)
                ];
                unset($data['resource_path']);
                $data['resource_path_local_image'][0] = $resourcePathData;
            }
        }
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta): array
    {
        return $meta;
    }
}
