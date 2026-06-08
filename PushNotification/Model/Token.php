<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model;

use Magento\Framework\Model\AbstractModel;

class Token extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\MageMasani\PushNotification\Model\ResourceModel\Token::class);
    }
}
