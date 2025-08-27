<?php
namespace Core;

final class Config
{
    private static array $items = [];

    public static function init(string $appConfigPath, string $basePath): void
    {
        $defaults = [
            'app' => [
                'env' => Env::get('APP_ENV', 'production'),
                'debug' => Env::get('APP_DEBUG', 'false') === 'true',
                'url' => Env::get('APP_URL', ''),
                'key' => Env::get('APP_KEY', ''),
                'canonical_host' => Env::get('CANONICAL_HOST', ''),
                'trailing_slash' => Env::get('TRAILING_SLASH', 'none'),
                'theme' => Env::get('THEME', 'default'),
            ],
            'database' => [
                'host' => Env::get('DB_HOST', '127.0.0.1'),
                'name' => Env::get('DB_NAME', ''),
                'user' => Env::get('DB_USER', 'root'),
                'pass' => Env::get('DB_PASS', ''),
                'charset' => Env::get('DB_CHARSET', 'utf8mb4'),
            ],
            'cache' => [
                'driver' => Env::get('CACHE_DRIVER', 'file'),
                'path' => $basePath . '/storage/cache',
            ],
            'features' => [
                // feature flags placeholder
            ],
        ];

        self::$items = $defaults;

        if (is_dir($appConfigPath)) {
            foreach (glob($appConfigPath . '/*.php') as $file) {
                $key = basename($file, '.php');
                $arr = require $file;
                if (is_array($arr)) {
                    self::$items[$key] = array_replace_recursive(self::$items[$key] ?? [], $arr);
                }
            }
        }
    }

    public static function get(string $key, $default = null)
    {
        $segments = explode('.', $key);
        $data = self::$items;
        foreach ($segments as $seg) {
            if (!is_array($data) || !array_key_exists($seg, $data)) {
                return $default;
            }
            $data = $data[$seg];
        }
        return $data;
    }
}
