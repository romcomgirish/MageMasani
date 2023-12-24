<?php

namespace MageMasani\BannerSlider\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * DiscountFilter module configuration
 */
class Config implements ConfigInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;
    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $localeDate;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param TimezoneInterface $localeDate
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface $localeDate
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->localeDate = $localeDate;
    }

    /**
     * @inheritdoc
     */
    public function isModuleEnable(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::MODULE_ENABLE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritdoc
     */
    public function getSliderBreakPoints()
    {
        return $this->scopeConfig->getValue(
            self::SLIDER_BREAK_POINTS,
            ScopeInterface::SCOPE_STORE
        );
    }
}
