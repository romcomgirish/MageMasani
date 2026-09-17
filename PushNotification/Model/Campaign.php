<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model;

use MageMasani\PushNotification\Api\Data\CampaignInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractExtensibleModel;
use MageMasani\PushNotification\Model\ResourceModel\Campaign as ResourceModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Model Campaign
 */
class Campaign extends AbstractExtensibleModel implements CampaignInterface, IdentityInterface
{
    /**
     * Cache tag constant
     *
     * @var string
     */
    public const CACHE_TAG = 'magemasani_pushnotification_campaign';

    protected $_eventPrefix = 'magemasani_pushnotification_campaign';
    protected $_eventObject = 'campaign';
    protected $_cacheTag = 'magemasani_pushnotification_campaign';

    /**
     * Initialize dependencies
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @return void
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
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
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
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
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @inheritdoc
     */
    public function getBody()
    {
        return $this->getData(self::BODY);
    }

    /**
     * @inheritdoc
     */
    public function setBody(string $body)
    {
        return $this->setData(self::BODY, $body);
    }

    /**
     * @inheritdoc
     */
    public function getClickUrl()
    {
        return $this->getData(self::CLICK_URL);
    }

    /**
     * @inheritdoc
     */
    public function setClickUrl(?string $clickUrl)
    {
        return $this->setData(self::CLICK_URL, $clickUrl);
    }

    /**
     * @inheritdoc
     */
    public function getImageUrl()
    {
        return $this->getData(self::IMAGE_URL);
    }

    /**
     * @inheritdoc
     */
    public function setImageUrl(?string $imageUrl)
    {
        return $this->setData(self::IMAGE_URL, $imageUrl);
    }

    /**
     * @inheritdoc
     */
    public function getCustomEvent()
    {
        return $this->getData(self::CUSTOM_EVENT);
    }

    /**
     * @inheritdoc
     */
    public function setCustomEvent(?string $customEvent)
    {
        return $this->setData(self::CUSTOM_EVENT, $customEvent);
    }

    /**
     * @inheritdoc
     */
    public function getNotificationType()
    {
        return $this->getData(self::NOTIFICATION_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setNotificationType(?string $notificationType)
    {
        return $this->setData(self::NOTIFICATION_TYPE, $notificationType);
    }

    /**
     * @inheritdoc
     */
    public function getScheduleTo()
    {
        return $this->getData(self::SCHEDULE_TO);
    }

    /**
     * @inheritdoc
     */
    public function setScheduleTo(?string $scheduleTo)
    {
        return $this->setData(self::SCHEDULE_TO, $scheduleTo);
    }

    /**
     * @inheritdoc
     */
    public function getOrderStatus()
    {
        return $this->getData(self::ORDER_STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setOrderStatus(?string $orderStatus)
    {
        return $this->setData(self::ORDER_STATUS, $orderStatus);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        $status = $this->getData(self::STATUS);
        return $status === null ? null : (int) $status;
    }

    /**
     * @inheritdoc
     */
    public function setStatus(int $status)
    {
        return $this->setData(self::STATUS, $status);
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
        return $this->setData(self::CREATED_AT, $createdAt);
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
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \MageMasani\PushNotification\Api\Data\CampaignExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG, self::CACHE_TAG . '_' . $this->getEntityId()];
    }
}
