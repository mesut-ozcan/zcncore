<?php
/** @var \Core\Router $router */

use Modules\Blog\Http\Controllers\PostsController;

// Public liste & göster
$router->group('/blog', [], function (\Core\Router $r) {
    $r->get('/',           [PostsController::class, 'index']);         // name: blog.index
    $r->get('/{slug}',     [PostsController::class, 'show']);          // name: blog.show
}, 'blog.');

// Yönetim (admin + csrf)
$router->group('/blog', ['admin'], function (\Core\Router $r) {
    $r->get('/create',     [PostsController::class, 'create']);        // blog.create
    $r->post('/store',     [PostsController::class, 'store'], ['csrf']);   // blog.store
    $r->get('/{slug}/edit',[PostsController::class, 'edit']);          // blog.edit
    $r->post('/{slug}/update',[PostsController::class, 'update'], ['csrf']); // blog.update
    $r->post('/{slug}/delete',[PostsController::class, 'destroy'], ['csrf']); // blog.destroy
}, 'blog.');
