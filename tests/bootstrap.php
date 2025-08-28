<?php
// Basit bootstrap: Env/Config yükle, autoload composer (varsa)
$base = dirname(__DIR__);
require_once $base . '/core/Env.php';
require_once $base . '/core/Helpers.php';
require_once $base . '/core/Config.php';
require_once $base . '/core/Request.php';
require_once $base . '/core/Response.php';
require_once $base . '/core/Router.php';
if (is_file($base . '/vendor/autoload.php')) {
    require $base . '/vendor/autoload.php';
}
// .env örnek yükle (varsayılanları okusun)
\Core\Env::load($base . '/.env');
\Core\Config::init($base . '/app/Config', $base);