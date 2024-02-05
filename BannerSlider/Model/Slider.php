<?php

namespace MageMasani\BannerSlider\Model;

use MageMasani\BannerSlider\Api\BannerRepositoryInterface;
use MageMasani\BannerSlider\Api\Data\BannerInterface;
use MageMasani\BannerSlider\Api\Data\SliderInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use MageMasani\BannerSlider\Model\ResourceModel\Slider as ResourceModel;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Slider Model Class
 */
class Slider extends AbstractExtensibleModel implements SliderInterface
{

    /**
     * @var string
     */
    protected $_eventPrefix = 'magemasani_bannerslider_slider';

    /**
     * @var string
     */
    protected $_eventObject = 'slider';

    /**
     * @var string
     */
    protected $_cacheTag = 'magemasani_bannerslider_slider';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritdoc
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setEntityId($entity_id)
    {
        return $this->setData(self::ENTITY_ID, 'entity_id');
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setTitle(string $title)
    {
        return $this->setData(self::TITLE, 'title');
    }

    /**
     * @inheritdoc
     */
    public function getIsShowTitle()
    {
        return (int)$this->getData(self::IS_SHOW_TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setIsShowTitle(int $isShowTitle)
    {
        return $this->setData(self::IS_SHOW_TITLE, 'is_show_title');
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return (int)$this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus(int $status)
    {
        return $this->setData(self::STATUS, 'status');
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(string $createdAt)
    {
        return $this->setData(self::CREATED_AT, 'created_at');
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(string $updatedAt)
    {
        return $this->setData(self::UPDATED_AT, 'updated_at');
    }

    /**
     * @inheritdoc
     *
     * @return \MageMasani\BannerSlider\Api\Data\SliderExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param \MageMasani\BannerSlider\Api\Data\SliderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \MageMasani\BannerSlider\Api\Data\SliderExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
