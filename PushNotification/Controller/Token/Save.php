<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Controller\Token;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Store\Model\StoreManagerInterface;
use MageMasani\PushNotification\Model\TokenRegistrar;
use Psr\Log\LoggerInterface;

/**
 * Controller Save
 */
class Save implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;
    /**
     * @var CustomerSession
     */
    private CustomerSession $customerSession;
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;
    /**
     * @var TokenRegistrar
     */
    private TokenRegistrar $tokenRegistrar;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Initialize dependencies
     *
     * @param RequestInterface $request
     * @param JsonFactory $jsonFactory
     * @param CustomerSession $customerSession
     * @param StoreManagerInterface $storeManager
     * @param TokenRegistrar $tokenRegistrar
     * @param LoggerInterface $logger
     * @return void
     */
    public function __construct(
        RequestInterface $request,
        JsonFactory $jsonFactory,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        TokenRegistrar $tokenRegistrar,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->jsonFactory = $jsonFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->tokenRegistrar = $tokenRegistrar;
        $this->logger = $logger;
    }

    /**
     * Execute action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result = $this->jsonFactory->create();
        $deviceType = (string)$this->request->getParam('device_type') ?: TokenRegistrar::DEFAULT_DEVICE_TYPE;

        try {
            $this->tokenRegistrar->register(
                (string)$this->request->getParam('token'),
                $deviceType,
                $this->customerSession->isLoggedIn()
                    ? (int)$this->customerSession->getCustomerId()
                    : null,
                (int)$this->storeManager->getStore()->getId(),
                (string)$this->request->getServer('HTTP_USER_AGENT', '')
            );
            return $result->setData(['success' => true]);
        } catch (\InvalidArgumentException $e) {
            return $result->setHttpResponseCode(400)
                ->setData(['success' => false, 'message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            $this->logger->error('[WebPush] Token save failed: ' . $e->getMessage(), ['exception' => $e]);
            return $result->setHttpResponseCode(500)
                ->setData(['success' => false, 'message' => 'Failed to save token.']);
        }
    }

    /**
     * Create CSRF validation exception
     *
     * @param RequestInterface $request
     * @return ?InvalidRequestException
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Validate for CSRF
     *
     * @param RequestInterface $request
     * @return ?bool
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
