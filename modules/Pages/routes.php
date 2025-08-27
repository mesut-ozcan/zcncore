<?php
use Core\Application;

$router = Application::get()->make('router');
$router->get('/pages/{slug}', [Modules\Pages\Http\Controllers\PageController::class, 'show']);
