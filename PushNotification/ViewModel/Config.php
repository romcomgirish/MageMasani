<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\UrlInterface;
use MageMasani\PushNotification\Model\ConfigInterface;

/**
 * Model Config
 */
class Config implements ArgumentInterface
{
    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;
    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * Initialize dependencies
     *
     * @param ConfigInterface $config
     * @param UrlInterface $urlBuilder
     * @return void
     */
    public function __construct(ConfigInterface $config, UrlInterface $urlBuilder)
    {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Check if enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        if (!$this->config->isEnabled()) {
            return false;
        }
        $allowed = $this->config->getAllowedDeviceTypes();
        if ($allowed === 'api') {
            return false;
        }
        $cfg = $this->config->getFirebaseConfig();
        return !empty($cfg['apiKey']) && !empty($cfg['projectId']);
    }

    /**
     * Get firebaseconfigjson
     *
     * @return string
     */
    public function getFirebaseConfigJson(): string
    {
        return (string) json_encode($this->config->getFirebaseConfig(), JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get vapidkey
     *
     * @return string
     */
    public function getVapidKey(): string
    {
        return $this->config->getVapidKey();
    }

    /**
     * Get firebaseappurl
     *
     * @return string
     */
    public function getFirebaseAppUrl(): string
    {
        return $this->urlBuilder->getUrl('webpush/asset/app');
    }

    /**
     * Get firebasemessagingurl
     *
     * @return string
     */
    public function getFirebaseMessagingUrl(): string
    {
        return $this->urlBuilder->getUrl('webpush/asset/messaging');
    }

    /**
     * Get serviceworkerurl
     *
     * @return string
     */
    public function getServiceWorkerUrl(): string
    {
        return $this->urlBuilder->getUrl('webpush/sw/index');
    }

    /**
     * Get savetokenurl
     *
     * @return string
     */
    public function getSaveTokenUrl(): string
    {
        return $this->urlBuilder->getUrl('webpush/token/save');
    }
}
