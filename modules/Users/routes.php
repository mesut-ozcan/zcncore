<?php
use Core\Application;

use App\Middleware\CsrfMiddleware;
use App\Middleware\RateLimitMiddleware;

use Modules\Users\Http\Controllers\AuthController;
use Modules\Users\Http\Controllers\AccountController;
use Modules\Users\Http\Controllers\AdminController;
use Modules\Users\Http\Controllers\PasswordResetController;

use Modules\Users\Middleware\AuthMiddleware;
use Modules\Users\Middleware\AdminOnlyMiddleware;

$router = Application::get()->make('router');

$csrf     = [new CsrfMiddleware()];
$throttle = [new RateLimitMiddleware()];

// Auth
$router->getNamed('login.show',    '/login',    [AuthController::class, 'showLogin']);
$router->postNamed('login.attempt','/login',    [AuthController::class, 'login'],    array_merge($throttle,$csrf));

$router->getNamed('register.show','/register',  [AuthController::class, 'showRegister']);
$router->postNamed('register.do', '/register',  [AuthController::class, 'register'], $csrf);

$router->postNamed('logout',      '/logout',    [AuthController::class, 'logout'],   $csrf);

// Password reset
$router->getNamed('password.forgot.show',  '/password/forgot',          [PasswordResetController::class, 'showForgot']);
$router->postNamed('password.forgot.send', '/password/forgot',          [PasswordResetController::class, 'send'],   array_merge($throttle,$csrf));
$router->getNamed('password.reset.show',   '/password/reset/{token}',   [PasswordResetController::class, 'showReset']);
$router->postNamed('password.reset.do',    '/password/reset/{token}',   [PasswordResetController::class, 'reset'],  array_merge($throttle,$csrf));

// Protected
$router->getNamed('account', '/account', [AccountController::class, 'index'], [new AuthMiddleware()]);

// Admin GROUP (prefix + alias middleware)
$router->group('/admin', ['auth','admin'], function($r){
    $r->getNamed('admin.dashboard', '/', [\Modules\Users\Http\Controllers\AdminController::class, 'dashboard']);
    // ileride: $r->getNamed('admin.users', '/users', [...]);
});
