<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Controller\Adminhtml\Campaign;

use MageMasani\PushNotification\Api\CampaignRepositoryInterface;
use MageMasani\PushNotification\Model\CampaignFactory;
use MageMasani\PushNotification\Model\ImageUploader;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Controller Save
 */
class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageMasani_PushNotification::campaign';

    /**
     * @var DataPersistorInterface
     */
    private DataPersistorInterface $dataPersistor;

    /**
     * @var CampaignRepositoryInterface
     */
    private CampaignRepositoryInterface $campaignRepository;

    /**
     * @var CampaignFactory
     */
    private CampaignFactory $campaignFactory;

    /**
     * @var ImageUploader
     */
    private ImageUploader $imageUploader;

    /**
     * @var \MageMasani\PushNotification\Model\Sender
     */
    private \MageMasani\PushNotification\Model\Sender $sender;

    /**
     * @param Context $context
     * @param CampaignRepositoryInterface $campaignRepository
     * @param DataPersistorInterface $dataPersistor
     * @param CampaignFactory $campaignFactory
     * @param ImageUploader $imageUploader
     * @param \MageMasani\PushNotification\Model\Sender $sender
     */
    public function __construct(
        Context $context,
        CampaignRepositoryInterface $campaignRepository,
        DataPersistorInterface $dataPersistor,
        CampaignFactory $campaignFactory,
        ImageUploader $imageUploader,
        \MageMasani\PushNotification\Model\Sender $sender
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->campaignRepository = $campaignRepository;
        $this->campaignFactory = $campaignFactory;
        $this->imageUploader = $imageUploader;
        $this->sender = $sender;
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            if ($id) {
                $model = $this->campaignRepository->getById($id);
            } else {
                $model = $this->campaignFactory->create();
            }

            $data = $this->getRequest()->getParams();

            // Flatten or map nested fields if sent by UI component
            if (isset($data['general']) && is_array($data['general'])) {
                $data = array_merge($data, $data['general']);
            }

            if (empty($data['entity_id'])) {
                unset($data['entity_id']);
            }

            if (!empty($data['schedule_to']) && is_string($data['schedule_to'])) {
                try {
                    $date = new \DateTime($data['schedule_to']);
                    $data['schedule_to'] = $date->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    // Ignore parsing error or fallback to default
                }
            }

            if (isset($data['image_url'])) {
                if (is_array($data['image_url'])) {
                    $imageName = $this->setCampaignImage($data['image_url']);
                    $data['image_url'] = $imageName;
                } elseif (is_string($data['image_url']) && $data['image_url'] !== '') {
                    $data['image_url'] = basename($data['image_url']);
                } else {
                    $data['image_url'] = null;
                }
            } else {
                $data['image_url'] = null;
            }
            $model->setData($data);
            $this->dataPersistor->set('pushnotification_campaign', $model->getData());
            $model = $this->campaignRepository->save($model);
            $this->dataPersistor->clear('pushnotification_campaign');
            $this->messageManager->addSuccessMessage(__('Campaign "%1" saved successfully', $model->getTitle()));

            // Send instant notification if type is instant, active, and not yet sent
            if ($model->getNotificationType() === 'instant' && (int) $model->getStatus() === 1 && !$model->getData('sent_at')) {
                try {
                    $this->sender->send(
                        (string) $model->getTitle(),
                        (string) $model->getBody(),
                        (string) $model->getClickUrl() ?: '/',
                        (string) $model->getImageUrl(),
                        'instant',
                        (int) $model->getEntityId()
                    );
                    $model->setData('sent_at', (new \DateTime())->format('Y-m-d H:i:s'));
                    $this->campaignRepository->save($model);
                    $this->messageManager->addSuccessMessage(__('Instant notification sent successfully.'));
                } catch (\Exception $e) {
                    $this->messageManager->addWarningMessage(__('Campaign saved, but instant notification could not be sent: %1', $e->getMessage()));
                }
            }

            if ($this->getRequest()->getParam('back') === 'edit') {
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getEntityId()]);
            }
            return $resultRedirect->setPath('*/*/');
        } catch (NoSuchEntityException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $resultRedirect->setPath('*/*/');
        } catch (CouldNotSaveException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('An error occurred while saving the campaign: %1', $exception->getMessage()));
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
        }
    }

    /**
     * Move campaign image from temp folder.
     *
     * @param array $campaignImage
     * @return string
     */
    public function setCampaignImage(array $campaignImage): string
    {
        $imageName = '';
        if (isset($campaignImage) && is_array($campaignImage)) {
            if (isset($campaignImage[0]['name'])) {
                if (isset($campaignImage[0]['is_saved'])) {
                    $imageName = $campaignImage[0]['name'];
                } else {
                    $imageName = $this->imageUploader->moveFileFromTmp($campaignImage[0]['name']);
                }
            }
        }
        return $imageName;
    }
}
