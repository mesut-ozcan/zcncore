<?php
use Core\Auth\Gate;
use Modules\Blog\Policies\PostPolicy;

return function (): void {
    // posts.manage -> admin
    if (class_exists(Gate::class)) {
        Gate::define('posts.manage', function ($user) {
            return PostPolicy::manage((array)$user);
        });
    }
};
