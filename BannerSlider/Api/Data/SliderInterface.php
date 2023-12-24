<?php
declare(strict_types=1);

namespace MageMasani\BannerSlider\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface SliderInterface extends ExtensibleDataInterface
{

    public const ENTITY_ID     = 'entity_id';
    public const TITLE         = 'title';
    public const IS_SHOW_TITLE = 'is_show_title';
    public const IS_ENABLED    = 'is_enabled';
    public const CREATED_AT    = 'created_at';
    public const UPDATED_AT    = 'updated_at';

    /**
     * Get entity ID.
     *
     * @return int
     */
    public function getEntityId();

    /**
     * Set entity ID.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId);

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
     * Get is show title.
     *
     * @return int
     */
    public function getIsShowTitle();

    /**
     * Set is show title.
     *
     * @param int $isShowTitle
     * @return $this
     */
    public function setIsShowTitle(int $isShowTitle);

    /**
     * Get is enabled.
     *
     * @return int
     */
    public function getIsEnabled();

    /**
     * Set is enabled.
     *
     * @param int $isEnabled
     * @return $this
     */
    public function setIsEnabled(int $isEnabled);

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
     * @return \MageMasani\BannerSlider\Api\Data\SliderExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \MageMasani\BannerSlider\Api\Data\SliderExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \MageMasani\BannerSlider\Api\Data\SliderExtensionInterface $extensionAttributes
    );
}
