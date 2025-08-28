<?php
use Core\Router;
use Core\Response;

/** @var Router $this */
$this->get('/blog', [\Modules\Blog\Http\Controllers\BlogHomeController::class, 'index']);