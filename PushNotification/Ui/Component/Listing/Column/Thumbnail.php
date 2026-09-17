<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * UI component Thumbnail
 */
class Thumbnail extends Column
{
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var UrlInterface
     */
    private UrlInterface $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item[$fieldName]) && $item[$fieldName]) {
                    $imagePath = 'MageMasani/pushnotification/' . ltrim($item[$fieldName], '/');
                    $imageUrl = $mediaUrl . $imagePath;

                    $item[$fieldName . '_src'] = $imageUrl;
                    $item[$fieldName . '_alt'] = $item['title'] ?? $item[$fieldName];
                    $item[$fieldName . '_orig_src'] = $imageUrl;

                    if (isset($item['entity_id'])) {
                        $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                            'magemasani_webpush/campaign/edit',
                            ['entity_id' => $item['entity_id']]
                        );
                    }
                }
            }
        }

        return $dataSource;
    }
}
