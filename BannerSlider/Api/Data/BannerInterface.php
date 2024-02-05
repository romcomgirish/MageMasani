<?php
declare(strict_types=1);

namespace MageMasani\BannerSlider\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * mageMasani banner  interface
 */
interface BannerInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    public const ENTITY_ID = 'entity_id';
    public const SLIDER_ID = 'slider_id';
    public const TITLE     = 'title';
    public const RESOURCE_TYPE = 'resource_type';
    public const RESOURCE_PATH = 'resource_path';
    public const ALT_TEXT = 'alt_text';
    public const LINK_TYPE = 'link_type';
    public const LINK_TYPE_RESOURCE = 'link_type_resource';
    public const STATUS = 'status';
    public const SORT_ORDER = 'sort_order';
    public const START_DATE = 'start_date';
    public const END_DATE = 'end_date';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * Get entity ID
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set entity ID
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

    /**
     * Get slider ID
     *
     * @return int
     */
    public function getSliderId();

    /**
     * Set slider ID
     *
     * @param int $sliderId
     * @return $this
     */
    public function setSliderId(int $sliderId);

    /**
     * Get resource type
     *
     * @return string|null
     */
    public function getResourceType();

    /**
     * Set resource type
     *
     * @param string|null $resourceType
     * @return $this
     */
    public function setResourceType(?string $resourceType);

    /**
     * Get resource path
     *
     * @return string|null
     */
    public function getResourcePath();

    /**
     * Set resource path
     *
     * @param string|null $resourcePath
     * @return $this
     */
    public function setResourcePath(?string $resourcePath);

    /**
     * Get is enabled.
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set is enabled.
     *
     * @param int $status
     * @return $this
     */
    public function setStatus(int $status);

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set title.
     *
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title);

    /**
     * Get alt text.
     *
     * @return string
     */
    public function getAltText();

    /**
     * Set alt text.
     *
     * @param string $altText
     * @return $this
     */
    public function setAltText(string $altText);

    /**
     * Get linkt_ype.
     *
     * @return string
     */
    public function getLinkType();

    /**
     * Set link_type.
     *
     * @param string $link_type
     * @return $this
     */
    public function setLinkType(string $link_type);

    /**
     * Get link_type_resource.
     *
     * @return string
     */
    public function getLinkTypeResource();

    /**
     * Set link_type_resource.
     *
     * @param string $link_type_resource
     * @return $this
     */
    public function setLinkTypeResource(string $link_type_resource);

    /**
     * Get sort order.
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Set sort order.
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder(int $sortOrder);

    /**
     * Get start date
     *
     * @return string|null
     */
    public function getStartDate();

    /**
     * Set start date
     *
     * @param string $startDate
     * @return $this
     */
    public function setStartDate(string $startDate);

    /**
     * Get end date
     *
     * @return string|null
     */
    public function getEndDate();

    /**
     * Set end date
     *
     * @param string $endDate
     * @return $this
     */
    public function setEndDate(string $endDate);

    /**
     * Get created at.
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created at.
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt);

    /**
     * Get updated at.
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set updated at.
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \MageMasani\BannerSlider\Api\Data\BannerExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \MageMasani\BannerSlider\Api\Data\BannerExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \MageMasani\BannerSlider\Api\Data\BannerExtensionInterface $extensionAttributes
    );
}
