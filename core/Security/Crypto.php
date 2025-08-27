<?php
namespace Core\Security;

use Core\Env;

final class Crypto
{
    private static function keyBytes(): string
    {
        // .env: APP_KEY=base64:xxxxx
        $raw = Env::get('APP_KEY', '');
        if (str_starts_with($raw, 'base64:')) {
            $b = base64_decode(substr($raw, 7), true);
            if ($b !== false) return $b;
        }
        // fallback
        return hash('sha256', $raw ?: 'zcncore-fallback-key', true);
    }

    /** @return array{enc:string, iv:string, mac:string} */
    public static function encrypt(array $payload, string $cipher = 'AES-256-CBC'): array
    {
        $key = self::keyBytes();
        $iv  = random_bytes(openssl_cipher_iv_length($cipher));
        $plain = json_encode($payload, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $enc = openssl_encrypt($plain, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        if ($enc === false) throw new \RuntimeException('Encrypt failed');
        $mac = hash_hmac('sha256', $enc.$iv, $key, true);
        return [
            'enc' => base64_encode($enc),
            'iv'  => base64_encode($iv),
            'mac' => base64_encode($mac),
        ];
    }

    /** @return array|null */
    public static function decrypt(string $encB64, string $ivB64, string $macB64, string $cipher = 'AES-256-CBC'): ?array
    {
        $key = self::keyBytes();
        $enc = base64_decode($encB64, true);
        $iv  = base64_decode($ivB64, true);
        $mac = base64_decode($macB64, true);
        if ($enc===false || $iv===false || $mac===false) return null;

        $calc = hash_hmac('sha256', $enc.$iv, $key, true);
        if (!hash_equals($mac, $calc)) return null;

        $plain = openssl_decrypt($enc, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        if ($plain === false) return null;

        $arr = json_decode($plain, true);
        return is_array($arr) ? $arr : null;
    }
}
