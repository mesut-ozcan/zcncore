<?php
/** @var \Core\Router $router */
$router->get('/blog', [\Modules\Blog\Http\Controllers\BlogHomeController::class, 'index']);
