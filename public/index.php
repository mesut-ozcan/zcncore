<?php
declare(strict_types=1);

/**
 * ZCNCore front controller (robust autoload)
 */

$base = __DIR__ . '/..';

// 1) Composer autoload (varsa)
$vendorAutoload = $base . '/vendor/autoload.php';
if (is_file($vendorAutoload)) {
    require $vendorAutoload;
}

// 2) Fallback PSR-4 autoload (her durumda kaydet)
spl_autoload_register(function ($class) use ($base) {
    $prefixes = [
        'Core\\'    => $base . '/core/',
        'App\\'     => $base . '/app/',
        'Modules\\' => $base . '/modules/',
    ];
    foreach ($prefixes as $p => $dir) {
        if (strncmp($class, $p, strlen($p)) === 0) {
            $relative = substr($class, strlen($p));
            $file = $dir . str_replace('\\', '/', $relative) . '.php';
            if (is_file($file)) {
                require $file;
                return;
            }
        }
    }
});

// 3) Global helper'lar
require_once $base . '/core/Helpers.php';

// 4) (Emniyet) Application sınıfını garanti altına al
if (!class_exists(\Core\Application::class)) {
    // core/Application.php dosyasının YOLU:
    require_once $base . '/core/Application.php';
}

session_start();

use Core\Kernel;
use Core\Application;

$app = Application::boot($base);
$kernel = new Kernel($app);
$response = $kernel->handle();
$response->send();
