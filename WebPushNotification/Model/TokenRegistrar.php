<?php
declare(strict_types=1);

namespace MageMasani\WebPushNotification\Model;

use MageMasani\WebPushNotification\Model\ResourceModel\Token as TokenResource;

class TokenRegistrar
{
    public const MAX_TOKEN_LENGTH = 1024;
    public const MAX_USER_AGENT_LENGTH = 512;
    public const TOKEN_PATTERN = '/^[A-Za-z0-9_\-:.]+$/';
    public const ALLOWED_DEVICE_TYPES = ['web', 'android', 'ios'];
    public const DEFAULT_DEVICE_TYPE = 'web';

    private TokenResource $tokenResource;

    public function __construct(TokenResource $tokenResource)
    {
        $this->tokenResource = $tokenResource;
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
