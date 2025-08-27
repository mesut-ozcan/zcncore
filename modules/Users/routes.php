<?php
use Core\Application;
use App\Middleware\CsrfMiddleware;

use Modules\Users\Http\Controllers\AuthController;
use Modules\Users\Http\Controllers\AccountController;
use Modules\Users\Http\Controllers\AdminController;

use Modules\Users\Middleware\AuthMiddleware;
use Modules\Users\Middleware\AdminOnlyMiddleware;

/**
 * Users module routes
 */
$router = Application::get()->make('router');

// CSRF middleware (POST istekleri için)
$csrf = [new CsrfMiddleware()];

/**
 * Auth
 */
$router->get('/login',     [AuthController::class, 'showLogin']);
$router->post('/login',    [AuthController::class, 'login'],    $csrf);

$router->get('/register',  [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register'], $csrf);

$router->post('/logout',   [AuthController::class, 'logout'],   $csrf);

/**
 * Protected pages
 */
$router->get('/account', [AccountController::class, 'index'], [new AuthMiddleware()]);

/**
 * Admin-only (requires auth + admin role)
 */
$router->get('/admin', [AdminController::class, 'dashboard'], [
    new AuthMiddleware(),
    new AdminOnlyMiddleware()
]);
