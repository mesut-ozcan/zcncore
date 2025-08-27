<?php
use Core\Application;
use Modules\Logs\Http\Controllers\LogController;
use Modules\Users\Middleware\AuthMiddleware;
use Modules\Users\Middleware\AdminOnlyMiddleware;

$router = Application::get()->make('router');

// Admin korumalı log viewer
$router->get('/admin/logs', [LogController::class, 'index'], [
    new AuthMiddleware(), new AdminOnlyMiddleware()
]);
$router->get('/admin/logs/download', [LogController::class, 'download'], [
    new AuthMiddleware(), new AdminOnlyMiddleware()
]);
