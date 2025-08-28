<?php
namespace Core;

final class Config
{
    /** Tüm config verisi (dosya adı => array) */
    private static array $items = [];

    /** Config dizini (app/Config) */
    private static string $dir = '';

    /** Proje kökü */
    private static string $base = '';

    /** Cache dosyası (storage/cache/config.cache.php) */
    private static ?string $cacheFile = null;

    /** Init: configleri yükle (önce cache, yoksa dosyalardan) */
    public static function init(string $configDir, string $basePath): void
    {
        self::$dir  = rtrim($configDir, "/\\");
        self::$base = rtrim($basePath, "/\\");
        self::$cacheFile = self::$base . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'config.cache.php';

        // 1) Cache varsa oku
        if (is_file(self::$cacheFile)) {
            $data = require self::$cacheFile;
            if (is_array($data)) {
                self::$items = $data;
                return;
            }
        }

        // 2) Config dizininden *.php dosyalarını yükle
        self::$items = [];
        if (is_dir(self::$dir)) {
            foreach (scandir(self::$dir) as $f) {
                if ($f === '.' || $f === '..') continue;
                $path = self::$dir . DIRECTORY_SEPARATOR . $f;
                if (is_file($path) && str_ends_with($f, '.php')) {
                    $key = substr($f, 0, -4); // "app.php" -> "app"
                    $val = require $path;
                    if (is_array($val)) self::$items[$key] = $val;
                }
            }
        }
    }

    /** Dot-notation ile okuma: get('app.url') */
    public static function get(string $key, $default = null)
    {
        $parts = explode('.', $key);
        $cur = self::$items;
        foreach ($parts as $p) {
            if (is_array($cur) && array_key_exists($p, $cur)) {
                $cur = $cur[$p];
            } else {
                return $default;
            }
        }
        return $cur;
    }

    /** Dot-notation ile yazma: set('app.debug', true) */
    public static function set(string $key, $value): void
    {
        $parts = explode('.', $key);
        $ref =& self::$items;
        foreach ($parts as $p) {
            if (!isset($ref[$p]) || !is_array($ref[$p])) {
                $ref[$p] = [];
            }
            $ref =& $ref[$p];
        }
        $ref = $value;
    }

    /** Tüm config dizisini döndür (gerekiyorsa) */
    public static function all(): array
    {
        return self::$items;
    }

    /** Config cache yaz: storage/cache/config.cache.php */
    public static function cacheWrite(): void
    {
        if (!self::$cacheFile) {
            throw new \RuntimeException('Config not initialized.');
        }
        $dir = dirname(self::$cacheFile);
        if (!is_dir($dir)) @mkdir($dir, 0777, true);

        $export = var_export(self::$items, true);
        $php = "<?php\nreturn " . $export . ";\n";
        if (@file_put_contents(self::$cacheFile, $php) === false) {
            throw new \RuntimeException('Could not write config cache: ' . self::$cacheFile);
        }
    }

    /** Config cache sil */
    public static function cacheClear(): void
    {
        if (!self::$cacheFile) return;
        if (is_file(self::$cacheFile)) {
            @unlink(self::$cacheFile);
        }
    }
}