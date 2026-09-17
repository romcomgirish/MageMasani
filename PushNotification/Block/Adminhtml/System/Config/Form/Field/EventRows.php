<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Block EventRows
 */
class EventRows extends AbstractFieldArray
{
    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('event_name', [
            'label' => __('Event Name'),
            'class' => 'required-entry'
        ]);
        $this->addColumn('event_constant', [
            'label' => __('Event Constant'),
            'class' => 'required-entry'
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Event');
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $html .= $this->getCustomJsAndCss();
        return $html;
    }

    /**
     * Get custom JS and CSS to make default rows non-deletable and read-only
     *
     * @return string
     */
    private function getCustomJsAndCss(): string
    {
        $defaultConstants = [
            'registartion',
            'login',
            'logout',
            'group_changing',
            'birthday',
            'sub_scription',
            'sub_scription_cancelation',
            'order_status',
            'abandoned_cart'
        ];
        $jsonDefaultConstants = json_encode($defaultConstants);

        return '<script>' . PHP_EOL .
            'require([\'jquery\', \'domReady!\'], function($) {' . PHP_EOL .
            '    var defaultEvents = ' . $jsonDefaultConstants . ';' . PHP_EOL .
            '    function protectDefaultRows() {' . PHP_EOL .
            '        $(\'table.admin__control-table tbody tr\').each(function() {' . PHP_EOL .
            '            var $row = $(this);' . PHP_EOL .
            '            var $constantInput = $row.find(\'input[name*="[event_constant]"]\');' . PHP_EOL .
            '            if ($constantInput.length) {' . PHP_EOL .
            '                var val = $constantInput.val();' . PHP_EOL .
            '                if (defaultEvents.indexOf(val) !== -1) {' . PHP_EOL .
            '                    $row.find(\'input\').attr(\'readonly\', \'readonly\').css({' . PHP_EOL .
            '                        \'background-color\': \'#f5f5f5\',' . PHP_EOL .
            '                        \'cursor\': \'not-allowed\'' . PHP_EOL .
            '                    });' . PHP_EOL .
            '                    $row.find(\'button.action-delete\').hide();' . PHP_EOL .
            '                }' . PHP_EOL .
            '            }' . PHP_EOL .
            '        });' . PHP_EOL .
            '    }' . PHP_EOL .
            '    protectDefaultRows();' . PHP_EOL .
            '    var targetNode = document.querySelector(\'table.admin__control-table tbody\');' . PHP_EOL .
            '    if (targetNode && window.MutationObserver) {' . PHP_EOL .
            '        var observer = new MutationObserver(protectDefaultRows);' . PHP_EOL .
            '        observer.observe(targetNode, { childList: true, subtree: true });' . PHP_EOL .
            '    }' . PHP_EOL .
            '});' . PHP_EOL .
            '</script>' . PHP_EOL;
    }
}
