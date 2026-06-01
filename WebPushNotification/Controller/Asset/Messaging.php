<?php
declare(strict_types=1);

namespace MageMasani\WebPushNotification\Controller\Asset;

class Messaging extends AbstractAsset
{
    protected function getFilename(): string
    {
        return 'firebase-messaging-compat.js';
    }
}
