<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\UrlInterface;
use MageMasani\PushNotification\Model\ConfigInterface;

class Config implements ArgumentInterface
{
    private ConfigInterface $config;
    private UrlInterface $urlBuilder;

    public function __construct(ConfigInterface $config, UrlInterface $urlBuilder)
    {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
    }

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

    public function getFirebaseConfigJson(): string
    {
        return (string) json_encode($this->config->getFirebaseConfig(), JSON_UNESCAPED_SLASHES);
    }

    public function getVapidKey(): string
    {
        return $this->config->getVapidKey();
    }

    public function getFirebaseAppUrl(): string
    {
        return $this->urlBuilder->getUrl('webpush/asset/app');
    }

    public function getFirebaseMessagingUrl(): string
    {
        return $this->urlBuilder->getUrl('webpush/asset/messaging');
    }

    public function getServiceWorkerUrl(): string
    {
        return $this->urlBuilder->getUrl('webpush/sw/index');
    }

    public function getSaveTokenUrl(): string
    {
        return $this->urlBuilder->getUrl('webpush/token/save');
    }
}
