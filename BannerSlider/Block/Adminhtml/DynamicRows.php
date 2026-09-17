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

namespace MageMasani\BannerSlider\Block\Adminhtml;

use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use MageMasani\BannerSlider\Block\Adminhtml\Widget\DynamicRows as CoreDynamicRows;

/**
 * BannerSlider DynamicRows Class
 */
class DynamicRows extends Widget
{
    /**
     * @var string
     */
    protected string $_blockClassName = CoreDynamicRows::class;

    /**
     * Prepare chooser element HTML
     *
     * @param AbstractElement $element
     * @return AbstractElement
     * @throws LocalizedException
     */
    public function prepareElementHtml(AbstractElement $element): AbstractElement
    {
        $uniqId = $this->_mathRandom->getUniqueHash($element->getId());

        $dynamicRows = $this->getLayout()->createBlock(
            $this->_blockClassName
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setUniqId(
            $uniqId
        );

        if ($element->getValue()) {
            $values = $element->getValue();
            $values = array_filter($values);
            $element->setValue($values);
        }

        $element->setData('after_element_html', $dynamicRows->toHtml());
        return $element;
    }
}
