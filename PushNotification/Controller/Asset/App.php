<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Controller\Asset;

/**
 * Controller App
 */
class App extends AbstractAsset
{
    /**
     * Get filename
     *
     * @return string
     */
    protected function getFilename(): string
    {
        return 'firebase-app-compat.js';
    }
}
