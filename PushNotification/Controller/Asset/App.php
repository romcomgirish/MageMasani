<?php
declare(strict_types=1);

namespace MageMasani\PushNotification\Controller\Asset;

class App extends AbstractAsset
{
    protected function getFilename(): string
    {
        return 'firebase-app-compat.js';
    }
}
