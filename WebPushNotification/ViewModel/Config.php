<?php
declare(strict_types=1);

namespace MageMasani\WebPushNotification\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\UrlInterface;
use MageMasani\WebPushNotification\Helper\Data as Helper;

class Config implements ArgumentInterface
{
    private Helper $helper;
    private UrlInterface $urlBuilder;

    public function __construct(Helper $helper, UrlInterface $urlBuilder)
    {
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
    }

    public function isEnabled(): bool
    {
        if (!$this->helper->isEnabled()) {
            return false;
        }
        $cfg = $this->helper->getFirebaseConfig();
        return !empty($cfg['apiKey']) && !empty($cfg['projectId']);
    }

    public function getFirebaseConfigJson(): string
    {
        return (string)json_encode($this->helper->getFirebaseConfig(), JSON_UNESCAPED_SLASHES);
    }

    public function getVapidKey(): string
    {
        return $this->helper->getVapidKey();
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
