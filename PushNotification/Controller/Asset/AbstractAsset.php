<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Controller\Asset;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader as ModuleReader;

/**
 * Controller AbstractAsset
 */
abstract class AbstractAsset implements HttpGetActionInterface
{
    /**
     * @var RawFactory
     */
    private RawFactory $rawFactory;
    /**
     * @var ModuleReader
     */
    private ModuleReader $moduleReader;
    /**
     * @var FileDriver
     */
    private FileDriver $fileDriver;

    /**
     * Initialize dependencies
     *
     * @param RawFactory $rawFactory
     * @param ModuleReader $moduleReader
     * @param FileDriver $fileDriver
     * @return void
     */
    public function __construct(
        RawFactory $rawFactory,
        ModuleReader $moduleReader,
        FileDriver $fileDriver
    ) {
        $this->rawFactory = $rawFactory;
        $this->moduleReader = $moduleReader;
        $this->fileDriver = $fileDriver;
    }

    abstract protected function getFilename(): string;

    /**
     * Execute action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $viewDir = $this->moduleReader->getModuleDir(
            Dir::MODULE_VIEW_DIR,
            'MageMasani_PushNotification'
        );
        $path = $viewDir . '/frontend/web/js/vendor/' . $this->getFilename();

        try {
            $body = $this->fileDriver->fileGetContents($path);
        } catch (\Throwable $e) {
            $body = '/* asset not found: ' . $this->getFilename() . ' */';
        }

        $body = "(function(){var __d=self.define;if(__d&&__d.amd){self.define=undefined;}try{\n"
            . $body
            . "\n}finally{if(__d){self.define=__d;}}})();";

        $response = $this->rawFactory->create();
        $response->setHeader('Content-Type', 'application/javascript', true);
        $response->setHeader('Cache-Control', 'public, max-age=31536000, immutable', true);
        $response->setContents($body);
        return $response;
    }
}
