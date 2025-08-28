<?php
/** @var \Core\Router $router */
$router->get('/test', [\Modules\Test\Http\Controllers\TestHomeController::class, 'index']);