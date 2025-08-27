<?php
namespace Core;

final class Cache
{
    private static string $path;

    public static function init(string $path): void
    {
        self::$path = $path;
        if (!is_dir($path)) @mkdir($path, 0777, true);
    }

    private static function fileFor(string $key): string
    {
        return self::$path . '/' . sha1($key) . '.cache';
    }

    public static function get(string $key, $default = null)
    {
        $f = self::fileFor($key);
        if (!is_file($f)) return $default;
        $payload = @json_decode((string)file_get_contents($f), true);
        if (!$payload) return $default;
        if ($payload['ttl'] !== 0 && $payload['time'] + $payload['ttl'] < time()) {
            @unlink($f);
            return $default;
        }
        return $payload['value'];
    }

    public static function set(string $key, $value, int $ttl = 0): void
    {
        $payload = ['time'=>time(), 'ttl'=>$ttl, 'value'=>$value];
        file_put_contents(self::fileFor($key), json_encode($payload));
    }

    public static function remember(string $key, int $ttl, callable $cb)
    {
        $val = self::get($key, null);
        if ($val !== null) return $val;
        $val = $cb();
        self::set($key, $val, $ttl);
        return $val;
    }

    public static function forget(string $key): void
    {
        $f = self::fileFor($key);
        if (is_file($f)) @unlink($f);
    }
}
