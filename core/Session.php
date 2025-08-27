<?php
namespace Core;

final class Session
{
    private static bool $booted = false;

    public static function boot(): void
    {
        if (self::$booted) return;
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        // Flash döngüsü: _flash_next -> _flash
        $_SESSION['_flash'] = $_SESSION['_flash_next'] ?? [];
        $_SESSION['_flash_next'] = [];

        // Old input döngüsü: _old_next -> _old
        $_SESSION['_old'] = $_SESSION['_old_next'] ?? [];
        $_SESSION['_old_next'] = [];

        // Errors döngüsü
        $_SESSION['_errors'] = $_SESSION['_errors_next'] ?? [];
        $_SESSION['_errors_next'] = [];

        self::$booted = true;
    }

    public static function get(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, $value): void {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, $value): void {
        $_SESSION['_flash_next'][$key] = $value;
    }

    public static function flashNow(string $key, $value): void {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function pullFlash(string $key, $default = null) {
        $val = $_SESSION['_flash'][$key] ?? $default;
        // flash bu istek boyunca kalsın; bir dahaki istekte zaten sıfırlanacak
        return $val;
    }

    public static function old(string $key, $default = null) {
        return $_SESSION['_old'][$key] ?? $default;
    }

    public static function setOld(array $data): void {
        $_SESSION['_old_next'] = $data;
    }

    public static function setErrors(array $errors): void {
        $_SESSION['_errors_next'] = $errors;
    }

    public static function errors(): array {
        return $_SESSION['_errors'] ?? [];
    }
}
