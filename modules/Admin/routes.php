<?php
/** @var \Core\Router $router */

use Modules\Admin\Http\Controllers\AdminHomeController;
use Modules\Admin\Http\Controllers\LogsController;
use Modules\Admin\Http\Controllers\JobsController;

// /admin altı: admin middleware korumalı grup
$router->group('/admin', ['admin'], function (\Core\Router $r) {
    // Dashboard
    $r->get('/', [AdminHomeController::class, 'index']);

    // Logs list & view (tail)
    $r->get('/logs', [LogsController::class, 'index']);              // liste + son N satır
    $r->get('/logs/download', [LogsController::class, 'download']);  // ?file=app-YYYY-MM-DD.log

    // Cache clear (POST)
    $r->post('/cache/clear', [AdminHomeController::class, 'clearCache'], 'admin.cache.clear');

    // Failed Jobs
    $r->get('/jobs/failed', [JobsController::class, 'failed'], 'admin.jobs.failed');
    $r->get('/jobs/failed/detail', [JobsController::class, 'detail'], 'admin.jobs.failed.detail');
    $r->post('/jobs/failed/retry', [JobsController::class, 'retry'], 'admin.jobs.failed.retry');
    $r->post('/jobs/failed/delete', [JobsController::class, 'delete'], 'admin.jobs.failed.delete');
});