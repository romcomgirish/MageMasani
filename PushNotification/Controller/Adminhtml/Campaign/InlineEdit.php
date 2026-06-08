<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Controller\Adminhtml\Campaign;

use MageMasani\PushNotification\Api\CampaignRepositoryInterface;
use MageMasani\PushNotification\Api\Data\CampaignInterface;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

class InlineEdit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageMasani_PushNotification::campaign';

    /**
     * @var JsonFactory
     */
    protected JsonFactory $jsonFactory;

    /**
     * @var CampaignRepositoryInterface
     */
    private CampaignRepositoryInterface $campaignRepository;

    /**
     * @param Action\Context $context
     * @param JsonFactory $jsonFactory
     * @param CampaignRepositoryInterface $campaignRepository
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $jsonFactory,
        CampaignRepositoryInterface $campaignRepository
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * InlineEdit action
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $entityId) {
                    $model = $this->campaignRepository->getById($entityId);
                    try {
                        $formData = $postItems[$entityId];
                        $model->setData($formData);
                        $this->campaignRepository->save($model);
                    } catch (\RuntimeException $e) {
                        $messages[] = $this->getErrorWithCampaignId($model, $e->getMessage());
                        $error = true;
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithCampaignId(
                            $model,
                            (string)__('Something went wrong while saving the Campaign.')
                        );
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add Campaign ID to error message
     *
     * @param CampaignInterface $model
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithCampaignId(CampaignInterface $model, string $errorText): string
    {
        return '[Campaign ID: ' . $model->getEntityId() . '] ' . $errorText;
    }
}
