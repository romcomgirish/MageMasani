<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model\Resolver;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use MageMasani\PushNotification\Model\TokenRegistrar;
use Psr\Log\LoggerInterface;

/**
 * Model SavePushNotificationToken
 */
class SavePushNotificationToken implements ResolverInterface
{
    /**
     * @var TokenRegistrar
     */
    private TokenRegistrar $tokenRegistrar;
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Initialize dependencies
     *
     * @param TokenRegistrar $tokenRegistrar
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     * @return void
     */
    public function __construct(
        TokenRegistrar $tokenRegistrar,
        RequestInterface $request,
        LoggerInterface $logger
    ) {
        $this->tokenRegistrar = $tokenRegistrar;
        $this->request = $request;
        $this->logger = $logger;
    }

    /**
     * Resolve
     *
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array $value
     * @param array $args
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $input = $args['input'] ?? [];

        $customerId = null;
        $storeId = 0;
        if ($context instanceof ContextInterface) {
            $extension = $context->getExtensionAttributes();
            if ($extension->getIsCustomer()) {
                $customerId = (int) $context->getUserId();
            }
            $store = $extension->getStore();
            if ($store !== null) {
                $storeId = (int) $store->getId();
            }
        }

        try {
            $this->tokenRegistrar->register(
                (string) ($input['token'] ?? ''),
                (string) ($input['device_type'] ?? ''),
                $customerId,
                $storeId,
                (string) $this->request->getServer('HTTP_USER_AGENT', '')
            );
            return ['success' => true, 'message' => null];
        } catch (\InvalidArgumentException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        } catch (\Throwable $e) {
            $this->logger->error('[WebPush] GraphQL token save failed: ' . $e->getMessage(), ['exception' => $e]);
            return ['success' => false, 'message' => 'Failed to save token.'];
        }
    }
}
