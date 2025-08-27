<?php
namespace Core\Auth;

use Core\Config;
use Core\Response;
use Core\Security\Crypto;

final class Remember
{
    /** Tarayıcıya remember cookie yazar (user id + bitiş zamanı + nonce) */
    public static function issue(int $userId): Response
    {
        $cfg = Config::get('auth', []);
        $days = (int)($cfg['remember_ttl_days'] ?? 30);
        $exp  = time() + $days * 86400;
        $nonce = bin2hex(random_bytes(16));

        $payload = ['uid'=>$userId, 'exp'=>$exp, 'n'=>$nonce];
        $cipher  = (string)($cfg['cipher'] ?? 'AES-256-CBC');
        $enc = Crypto::encrypt($payload, $cipher);

        $cookieName = (string)($cfg['remember_cookie'] ?? 'zcn_remember');
        $cookieVal  = $enc['enc'].'.'.$enc['iv'].'.'.$enc['mac'];

        $opts = $cfg['cookie'] ?? [];
        $minutes = $days * 24 * 60;

        // Response döndürüp zincirleme kullanacağız
        return (new Response())->withCookie($cookieName, $cookieVal, $minutes, $opts);
    }

    /** Cookie’yi siler */
    public static function forget(): Response
    {
        $cfg = Config::get('auth', []);
        $name = (string)($cfg['remember_cookie'] ?? 'zcn_remember');
        $opts = $cfg['cookie'] ?? [];
        return (new Response())->withCookie($name, '', -60, $opts);
    }

    /** Cookie’den user id döndürür (geçerli değilse null) */
    public static function userIdFromCookie(): ?int
    {
        $cfg = Config::get('auth', []);
        $name = (string)($cfg['remember_cookie'] ?? 'zcn_remember');
        $raw  = $_COOKIE[$name] ?? '';
        if (!$raw || !str_contains($raw, '.')) return null;
        $parts = explode('.', $raw);
        if (count($parts) !== 3) return null;

        $cipher = (string)($cfg['cipher'] ?? 'AES-256-CBC');
        $data = Crypto::decrypt($parts[0], $parts[1], $parts[2], $cipher);
        if (!$data) return null;
        if (($data['exp'] ?? 0) < time()) return null;

        $uid = (int)($data['uid'] ?? 0);
        return $uid > 0 ? $uid : null;
    }
}
