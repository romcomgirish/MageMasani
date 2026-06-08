<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Controller\Sw;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader as ModuleReader;
use Magento\Framework\UrlInterface;
use MageMasani\PushNotification\Model\ConfigInterface;

class Index implements HttpGetActionInterface
{
    private RawFactory $rawFactory;
    private ModuleReader $moduleReader;
    private FileDriver $fileDriver;
    private UrlInterface $urlBuilder;
    private ConfigInterface $config;

    public function __construct(
        RawFactory $rawFactory,
        ModuleReader $moduleReader,
        FileDriver $fileDriver,
        UrlInterface $urlBuilder,
        ConfigInterface $config
    ) {
        $this->rawFactory = $rawFactory;
        $this->moduleReader = $moduleReader;
        $this->fileDriver = $fileDriver;
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;
    }

    public function execute(): ResultInterface
    {
        $viewDir = $this->moduleReader->getModuleDir(
            Dir::MODULE_VIEW_DIR,
            'MageMasani_PushNotification'
        );
        $path = $viewDir . '/frontend/web/js/sw.js';

        try {
            $swBody = $this->fileDriver->fileGetContents($path);
        } catch (\Throwable $e) {
            $swBody = '/* sw.js not found */';
        }

        $cfg = $this->config->getFirebaseConfig();
        $appUrl = $this->urlBuilder->getUrl('webpush/asset/app');
        $msgUrl = $this->urlBuilder->getUrl('webpush/asset/messaging');

        $banner = '';
        if (empty($cfg['projectId']) || empty($cfg['apiKey'])) {
            $banner = "/* [WebPush] firebase_config_json is empty or invalid — paste the firebaseConfig "
                . "object from Firebase Console (Project Settings > Your apps > SDK setup) into "
                . "Stores > Configuration > MageMasani > Push Notification. */\n";
        }

        $prefix = $banner
            . 'self.FIREBASE_CONFIG = ' . json_encode($cfg, JSON_UNESCAPED_SLASHES) . ";\n"
            . 'self.FIREBASE_APP_URL = ' . json_encode($appUrl, JSON_UNESCAPED_SLASHES) . ";\n"
            . 'self.FIREBASE_MESSAGING_URL = ' . json_encode($msgUrl, JSON_UNESCAPED_SLASHES) . ";\n\n";

        $response = $this->rawFactory->create();
        $response->setHeader('Content-Type', 'application/javascript', true);
        $response->setHeader('Service-Worker-Allowed', '/', true);
        $response->setHeader('Cache-Control', 'no-store', true);
        $response->setContents($prefix . $swBody);
        return $response;
    }
}
