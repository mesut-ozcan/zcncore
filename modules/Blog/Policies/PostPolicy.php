<?php
namespace Modules\Blog\Policies;

final class PostPolicy
{
    public static function manage(array $user): bool
    {
        return strtolower($user['role'] ?? '') === 'admin';
    }
}
