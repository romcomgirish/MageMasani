<?php

namespace MageMasani\BannerSlider\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Slider SliderActions Class
 */
class SliderActions extends Column
{

    public const ENTITY_ID = 'entity_id';

    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param UrlInterface $url
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Escaper $escaper,
        UrlInterface $url,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->escaper = $escaper;
        $this->url = $url;
    }

    /**
     * @inheritDoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item[self::ENTITY_ID])) {
                    $item[$name]['edit'] = [
                        'href' => $this->url->getUrl(
                            'magemasanibs/slider/edit',
                            [self::ENTITY_ID => $item[self::ENTITY_ID]]
                        ),
                        'label' => __('Edit'),
                        '__disableTmpl' => true,
                    ];
                    $title = $this->escaper->escapeHtml($item['title']);
                    $item[$name]['delete'] = [
                        'href' => $this->url->getUrl(
                            'magemasanibs/slider/delete',
                            [self::ENTITY_ID => $item[self::ENTITY_ID]]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete %1', $title),
                            'message' => __('Are you sure you want to delete a %1 record?', $title),
                            '__disableTmpl' => true,
                        ],
                        'post' => true,
                        '__disableTmpl' => true,
                    ];
                }
            }
        }
        return $dataSource;
    }
}
