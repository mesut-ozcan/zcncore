<?php
namespace Core;

final class Config
{
    private static array $data = [];
    private static string $basePath = '';
    private static string $configDir = '';
    private static string $cacheFile = '';

    public static function init(string $configDir, string $basePath): void
    {
        self::$basePath  = rtrim($basePath, '/');
        self::$configDir = rtrim($configDir, '/');
        self::$cacheFile = self::path('storage/cache/config.cache.php');

        // Cache varsa doğrudan yükle
        if (is_file(self::$cacheFile)) {
            $all = require self::$cacheFile;
            if (is_array($all)) {
                self::$data = $all;
                return;
            }
        }

        // Normal yükleme + env override
        $base = self::loadAll(self::$configDir);

        $env = Env::get('APP_ENV', '');
        if ($env) {
            $base = self::applyEnvOverrides($base, self::$configDir, $env);
        }

        self::$data = $base;
    }

    private static function path(string $p): string
    {
        return self::$basePath . '/' . ltrim($p, '/');
    }

    private static function loadAll(string $dir): array
    {
        $all = [];
        if (!is_dir($dir)) return $all;
        foreach (glob($dir.'/*.php') as $file) {
            // env dosyalarını burada değil override aşamasında ele alacağız
            if (preg_match('/\.(local|dev|prod|stage)\.php$/', $file)) continue;
            $name = basename($file, '.php');
            $val  = require $file;
            if (is_array($val)) $all[$name] = $val;
        }
        return $all;
    }

    private static function applyEnvOverrides(array $base, string $dir, string $env): array
    {
        // app.php -> app.{env}.php gibi dosyaları bul, array_replace_recursive ile override et
        foreach (glob($dir.'/*.'.$env.'.php') as $file) {
            $name = basename($file, '.'.$env.'.php'); // örn: app
            $val  = require $file;
            if (is_array($val)) {
                $base[$name] = array_replace_recursive($base[$name] ?? [], $val);
            }
        }
        return $base;
    }

    public static function get(string $key, $default = null)
    {
        if ($key === '') return self::$data;
        $seg = explode('.', $key);
        $ref = self::$data;
        foreach ($seg as $s) {
            if (!is_array($ref) || !array_key_exists($s, $ref)) {
                return $default;
            }
            $ref = $ref[$s];
        }
        return $ref;
    }

    public static function set(string $key, $value): void
    {
        $seg = explode('.', $key);
        $ref =& self::$data;
        foreach ($seg as $i => $s) {
            if ($i === count($seg)-1) {
                $ref[$s] = $value;
            } else {
                if (!isset($ref[$s]) || !is_array($ref[$s])) $ref[$s] = [];
                $ref =& $ref[$s];
            }
        }
    }

    public static function cache(): void
    {
        $env = Env::get('APP_ENV', '');
        $all = self::loadAll(self::$configDir);
        if ($env) $all = self::applyEnvOverrides($all, self::$configDir, $env);

        $export = "<?php\nreturn " . var_export($all, true) . ";\n";
        @file_put_contents(self::$cacheFile, $export);
        self::$data = $all;
    }

    public static function clearCache(): void
    {
        if (is_file(self::$cacheFile)) @unlink(self::$cacheFile);
        $base = self::loadAll(self::$configDir);
        $env  = Env::get('APP_ENV', '');
        if ($env) $base = self::applyEnvOverrides($base, self::$configDir, $env);
        self::$data = $base;
    }
}
