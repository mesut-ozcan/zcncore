<?php
namespace Core\Support;

use Core\Config;

final class ViewCache
{
    private static function dir(): string
    {
        $rel = Config::get('cache.views_path', 'storage/cache/views');
        $dir = \base_path($rel);
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
        return $dir;
    }

    private static function key(string $name, array $data): string
    {
        return md5($name . '|' . serialize(array_keys($data)) . '|' . serialize(array_values($data)) . '|' . (string)Config::get('app.theme','default'));
    }

    /**
     * Cache’ten getir; yoksa $producer() ile üret ve yaz.
     */
    public static function remember(string $name, array $data, int $ttl, callable $producer): string
    {
        $dir = self::dir();
        $key = self::key($name, $data);
        $file = $dir . '/' . $key . '.html';

        if (is_file($file) && (filemtime($file) + $ttl) > time()) {
            return (string)file_get_contents($file);
        }

        $html = (string)$producer();
        @file_put_contents($file, $html);
        return $html;
    }

    public static function forget(string $name, array $data): void
    {
        $dir = self::dir();
        $key = self::key($name, $data);
        $file = $dir . '/' . $key . '.html';
        if (is_file($file)) @unlink($file);
    }
}
