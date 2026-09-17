<?php
/**
 * MageMasani BannerSlider Module
 *
 * @category  MageMasani
 * @package   MageMasani_BannerSlider
 * @author    MageMasani <support@magemasani.com>
 * @copyright Copyright (c) MageMasani (https://www.magemasani.com/)
 * @license   GPL-3.0-or-later
 */

declare(strict_types=1);

namespace MageMasani\BannerSlider\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * System Configuration model Class
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
