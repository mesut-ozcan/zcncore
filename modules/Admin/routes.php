<?php
/** @var \Core\Router $router */

use Modules\Admin\Http\Controllers\AdminHomeController;
use Modules\Admin\Http\Controllers\LogsController;

// /admin altı: admin middleware korumalı grup
$router->group('/admin', ['admin'], function (\Core\Router $r) {
    // Dashboard
    $r->get('/', [AdminHomeController::class, 'index']);

    // Logs list & view (tail)
    $r->get('/logs', [LogsController::class, 'index']);              // liste + son N satır
    $r->get('/logs/download', [LogsController::class, 'download']);  // ?file=app-YYYY-MM-DD.log

    // Cache clear (POST)
    $r->post('/cache/clear', [AdminHomeController::class, 'clearCache']);
});