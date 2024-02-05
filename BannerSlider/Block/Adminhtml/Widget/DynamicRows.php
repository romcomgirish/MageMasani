<?php

namespace MageMasani\BannerSlider\Block\Adminhtml\Widget;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * BannerSlider Widget DynamicRows Class
 */
class DynamicRows extends AbstractFieldArray
{

    /**
     * Prepare rendering the new field by adding all the needed columns
     *
     * @return void
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn('break_point', [
            'label' => __('BreakPoint'),
            'class' => 'required-entry validate-digits'
        ]);
        $this->addColumn('slide_to_show', [
            'label' => __('Slide To Show'),
            'class' => 'required-entry validate-digits'
        ]);
        $this->addColumn('slide_to_scroll', [
            'label' => __('Slide To Scroll'),
            'class' => 'required-entry validate-digits'
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];
        $dynamicColumn = $row->getTemplete();
        if ($dynamicColumn !== null) {
            $options['option_' . $this->getColumnRenderer()->calcOptionHash($dynamicColumn)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}
