<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Controller\Asset;

/**
 * Controller Messaging
 */
class Messaging extends AbstractAsset
{
    /**
     * Get filename
     *
     * @return string
     */
    protected function getFilename(): string
    {
        return 'firebase-messaging-compat.js';
    }
}
