<?php

namespace MageMasani\BannerSlider\Model\Banner;

use MageMasani\BannerSlider\Model\ResourceModel\Banner\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;

/**
 * Banner DataProvider Class
 */
class DataProvider extends ModifierPoolDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private DataPersistorInterface $dataPersistor;

    /**
     * @var PoolInterface|null
     */
    private ?PoolInterface $pool;

    /**
     * @var array|null
     */
    protected ?array $loadedData = null;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = [],
        ?PoolInterface $pool = null
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->collection = $collectionFactory->create();
        $this->pool = $pool;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        $items = $this->collection->getItems();
        foreach ($items as $item) {
            $this->loadedData[$item->getEntityId()] = $item->getData();
        }
        $data = $this->dataPersistor->get('bannerslider_banner');
        if (!empty($data)) {
            $banner = $this->collection->getNewEmptyItem();
            $banner->setData($data);
            $this->loadedData[$banner->getEntityId()] = $banner->getData();
            $this->dataPersistor->clear('bannerslider_banner');
        }
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $this->loadedData = $modifier->modifyData($this->loadedData);
        }
        return $this->loadedData;
    }
}
