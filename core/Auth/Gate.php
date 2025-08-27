<?php
namespace Core\Auth;

/**
 * Basit Gate/Policy yapısı.
 * Kullanım:
 *   Gate::define('edit-user', function($user, $target){ return $user && $user['id']===$target['id']; });
 *   if (!Gate::allows('edit-user', [$currentUser, $targetUser])) { ... }
 */
final class Gate
{
    /** @var array<string, callable> */
    private static array $abilities = [];

    public static function define(string $ability, callable $callback): void
    {
        self::$abilities[$ability] = $callback;
    }

    public static function allows(string $ability, array $args = []): bool
    {
        if (!isset(self::$abilities[$ability])) return false;
        $cb = self::$abilities[$ability];
        try {
            return (bool)($cb)(...$args);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function authorize(string $ability, array $args = []): void
    {
        if (!self::allows($ability, $args)) {
            throw new \RuntimeException('Forbidden', 403);
        }
    }
}
