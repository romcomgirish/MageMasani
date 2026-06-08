<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Token extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('magemasani_webpush_token', 'entity_id');
    }
}
