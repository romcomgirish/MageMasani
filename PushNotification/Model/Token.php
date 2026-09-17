<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Model Token
 */
class Token extends AbstractModel
{
    /**
     * Resource model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\MageMasani\PushNotification\Model\ResourceModel\Token::class);
    }
}
