<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model;

use MageMasani\PushNotification\Model\ResourceModel\Token as TokenResource;

/**
 * Model TokenRegistrar
 */
class TokenRegistrar
{
    /**
     * Max token length constant
     *
     * @var int
     */
    public const MAX_TOKEN_LENGTH = 1024;
    /**
     * Max user agent length constant
     *
     * @var int
     */
    public const MAX_USER_AGENT_LENGTH = 512;
    /**
     * Token pattern constant
     *
     * @var string
     */
    public const TOKEN_PATTERN = '/^[A-Za-z0-9_\-:.]+$/';
    /**
     * Allowed device types constant
     *
     * @var array
     */
    public const ALLOWED_DEVICE_TYPES = ['web', 'android', 'ios'];
    /**
     * Default device type constant
     *
     * @var string
     */
    public const DEFAULT_DEVICE_TYPE = 'web';

    /**
     * @var TokenResource
     */
    private TokenResource $tokenResource;
    /**
     * @var ConfigInterface
     */
    private ConfigInterface $config;

    /**
     * Initialize dependencies
     *
     * @param TokenResource $tokenResource
     * @param ConfigInterface $config
     * @return void
     */
    public function __construct(
        TokenResource $tokenResource,
        ConfigInterface $config
    ) {
        $this->tokenResource = $tokenResource;
        $this->config = $config;
    }

    /**
     * @throws \InvalidArgumentException on validation failure
     * @throws \RuntimeException on persistence failure
     */
    public function register(
        string $token,
        string $deviceType,
        ?int $customerId,
        ?int $storeId,
        string $userAgent
    ): void {
        $token = trim($token);
        $deviceType = strtolower(trim($deviceType));

        if ($token === '') {
            throw new \InvalidArgumentException('Token is required.');
        }
        if (strlen($token) > self::MAX_TOKEN_LENGTH || !preg_match(self::TOKEN_PATTERN, $token)) {
            throw new \InvalidArgumentException('Invalid token format.');
        }
        if (!in_array($deviceType, self::ALLOWED_DEVICE_TYPES, true)) {
            throw new \InvalidArgumentException('Invalid device_type.');
        }

        $allowed = $this->config->getAllowedDeviceTypes($storeId);
        if ($allowed === 'web' && $deviceType !== 'web') {
            throw new \InvalidArgumentException('Device type ' . $deviceType . ' is not allowed by configuration.');
        }
        if ($allowed === 'api' && $deviceType === 'web') {
            throw new \InvalidArgumentException('Device type web is not allowed by configuration.');
        }

        $connection = $this->tokenResource->getConnection();
        $connection->insertOnDuplicate(
            $this->tokenResource->getMainTable(),
            [
                'token' => $token,
                'customer_id' => $customerId,
                'user_agent' => substr($userAgent, 0, self::MAX_USER_AGENT_LENGTH),
                'store_id' => $storeId,
                'device_type' => $deviceType,
                'is_active' => 1
            ],
            ['customer_id', 'user_agent', 'store_id', 'device_type', 'is_active']
        );
    }
}
