<?php
declare(strict_types=1);

namespace MageMasani\WebPushNotification\Model;

use Magento\Framework\Model\AbstractModel;

class Token extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\MageMasani\WebPushNotification\Model\ResourceModel\Token::class);
    }
}
