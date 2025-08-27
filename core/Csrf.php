<?php
namespace Core;

final class Csrf
{
    private const SESSION_KEY = '_token';
    private const COOKIE_NAME = 'XSRF-TOKEN';

    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /** XSRF-TOKEN çerezini (SameSite=Lax) gönderir */
    public static function ensureCookie(): void
    {
        $token = self::token(); // var/yok oluştur
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

        // PHP ≥7.3 options array
        @setcookie(self::COOKIE_NAME, $token, [
            'expires'  => 0,
            'path'     => '/',
            'domain'   => '',            // default
            'secure'   => $secure,       // prod'da true önerilir
            'httponly' => false,         // JS okuyabilsin (SPA'ler için)
            'samesite' => 'Lax',
        ]);
    }

    public static function check(?string $token): bool
    {
        if (!$token) {
            // Header: X-CSRF-TOKEN veya X-XSRF-TOKEN
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_XSRF_TOKEN'] ?? '';
        }
        $session = $_SESSION[self::SESSION_KEY] ?? '';
        return is_string($token) && $token !== '' && hash_equals((string)$session, $token);
    }
}
