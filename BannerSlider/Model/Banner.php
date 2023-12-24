<?php

namespace MageMasani\BannerSlider\Model;

use MageMasani\BannerSlider\Api\Data\BannerInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractExtensibleModel;
use MageMasani\BannerSlider\Model\ResourceModel\Banner as ResourceModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Banner extends AbstractExtensibleModel implements BannerInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'codilar_bannerslider_banner';

    /**
     * @var string
     */
    protected $_eventObject = 'banner';

    /**
     * @var string
     */
    protected $_cacheTag = 'codilar_bannerslider_banner';

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
    public function getSliderId()
    {
        return (int)$this->getData(self::SLIDER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setSliderId(int $sliderId)
    {
        return $this->setData(self::SLIDER_ID, 'slider_id');
    }

    /**
     * @inheritdoc
     */
    public function getResourceType()
    {
        return $this->getData(self::RESOURCE_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setResourceType(?string $resourceType)
    {
        return $this->setData(self::RESOURCE_TYPE, 'resource_type');
    }

    /**
     * @inheritdoc
     */
    public function getResourcePath()
    {
        return $this->getData(self::RESOURCE_PATH);
    }

    /**
     * @inheritdoc
     */
    public function setResourcePath(?string $resourcePath)
    {
        return $this->setData(self::RESOURCE_PATH, 'resource_path');
    }

    /**
     * @inheritdoc
     */
    public function getIsEnabled()
    {
        return (int)$this->getData(self::IS_ENABLED);
    }

    /**
     * @inheritdoc
     */
    public function setIsEnabled(int $isEnabled)
    {
        return $this->setData(self::IS_ENABLED, 'is_enabled');
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
    public function getAltText()
    {
        return $this->getData(self::ALT_TEXT);
    }

    /**
     * @inheritdoc
     */
    public function setAltText(string $altText)
    {
        return $this->setData(self::ALT_TEXT, 'alt_text');
    }

    /**
     * @inheritdoc
     */
    public function getLinkType()
    {
        return $this->getData(self::LINK_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setLinkType(string $link_type)
    {
        return $this->setData(self::LINK_TYPE, 'link_type');
    }

    /**
     * @inheritdoc
     */
    public function getLinkTypeResource()
    {
        return $this->getData(self::LINK_TYPE_RESOURCE);
    }

    /**
     * @inheritdoc
     */
    public function setLinkTypeResource(string $link_type_resource)
    {
        return $this->setData(self::LINK_TYPE_RESOURCE, 'link_type_resource');
    }

    /**
     * @inheritdoc
     */
    public function getSortOrder()
    {
        return (int)$this->getData(self::SORT_ORDER);
    }

    /**
     * @inheritdoc
     */
    public function setSortOrder(int $sortOrder)
    {
        return $this->setData(self::SORT_ORDER, 'sort_order');
    }

    /**
     * @inheritdoc
     */
    public function getStartDate()
    {
        return $this->getData(self::START_DATE);
    }

    /**
     * @inheritdoc
     */
    public function setStartDate(string $startDate)
    {
        return $this->setData(self::START_DATE, 'sort_order');
    }

    /**
     * @inheritdoc
     */
    public function getEndDate()
    {
        return $this->getData(self::END_DATE);
    }

    /**
     * @inheritdoc
     */
    public function setEndDate(string $endData)
    {
        return $this->setData(self::END_DATE, 'sort_order');
    }

    /**
     * @inheritdoc
     *
     * @return \MageMasani\BannerSlider\Api\Data\BannerExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     *
     * @param \MageMasani\BannerSlider\Api\Data\BannerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \MageMasani\BannerSlider\Api\Data\BannerExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
