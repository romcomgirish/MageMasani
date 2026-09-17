<?php
/**
 * MageMasani BannerSlider Module
 *
 * @category  MageMasani
 * @package   MageMasani_BannerSlider
 * @author    MageMasani <support@magemasani.com>
 * @copyright Copyright (c) MageMasani (https://www.magemasani.com/)
 * @license   GPL-3.0-or-later
 */

declare(strict_types=1);

namespace MageMasani\BannerSlider\Controller\Adminhtml\Slider;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * Slider NewAction Class
 */
class NewAction extends Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'MageMasani_BannerSlider::save';

    /**
     * Forward to edit
     *
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        /** @var Forward $resultForwardFactory */
        $resultForwardFactory = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        return $resultForwardFactory->forward('edit');
    }
}
