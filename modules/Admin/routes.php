<?php
/** @var \Core\Router $router */

use Modules\Admin\Http\Controllers\AdminHomeController;
use Modules\Admin\Http\Controllers\LogsController;
// use Modules\Admin\Http\Controllers\JobsController; // varsa aç

$router->group('/admin', ['admin'], function (\Core\Router $r) {
    // Dashboard
    $r->getNamed('admin.dashboard', '/', [AdminHomeController::class, 'index']);

    // Logs list & download
    $r->getNamed('admin.logs.index', '/logs', [LogsController::class, 'index']);
    $r->getNamed('admin.logs.download', '/logs/download', [LogsController::class, 'download']);

    // Cache clear (POST)
    $r->postNamed('admin.cache.clear', '/cache/clear', [AdminHomeController::class, 'clearCache']);
});