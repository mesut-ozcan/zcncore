<?php
/** @var \Core\Router $router */

use Modules\Blog\Http\Controllers\PostsController;

// 1) Yönetim (önce register et ki /{slug} bunları gölgelemesin)
$router->group('/blog', ['admin'], function (\Core\Router $r) {
    $r->getNamed('create',        '/create',            [PostsController::class, 'create']);                 // blog.create
    $r->postNamed('store',        '/store',             [PostsController::class, 'store'],   ['csrf']);      // blog.store
    $r->getNamed('edit',          '/{slug}/edit',       [PostsController::class, 'edit']);                   // blog.edit
    $r->postNamed('update',       '/{slug}/update',     [PostsController::class, 'update'],  ['csrf']);      // blog.update
    $r->postNamed('destroy',      '/{slug}/delete',     [PostsController::class, 'destroy'], ['csrf']);      // blog.destroy
}, 'blog.');

// 2) Public liste & göster (en sonda ve show EN SONA)
$router->group('/blog', [], function (\Core\Router $r) {
    $r->getNamed('index',         '/',                  [PostsController::class, 'index']);                  // blog.index
    $r->getNamed('show',          '/{slug}',            [PostsController::class, 'show']);                   // blog.show
}, 'blog.');