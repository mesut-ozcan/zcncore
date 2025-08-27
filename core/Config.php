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

        // Eğer cache dosyası varsa onu kullan
        if (is_file(self::$cacheFile)) {
            $all = require self::$cacheFile;
            if (is_array($all)) {
                self::$data = $all;
                return;
            }
        }

        // Aksi halde app/Config altındaki tüm *.php dosyalarını yükle
        self::$data = self::loadAll(self::$configDir);
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
            $name = basename($file, '.php'); // app, database, modules vs.
            $val  = require $file;
            if (is_array($val)) {
                $all[$name] = $val;
            }
        }
        return $all;
    }

    /** Dot-notation destekli getter: get('app.debug', false) */
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

    /** Dot-notation setter */
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

    /** Tüm config’i tek dosyaya cache’ler */
    public static function cache(): void
    {
        // Güncel dosya sisteminden topla (cache'li olabilir; sıfırdan alalım)
        $all = self::loadAll(self::$configDir);

        // storage/cache dizini varsa yaz
        $export = "<?php\nreturn " . var_export($all, true) . ";\n";
        @file_put_contents(self::$cacheFile, $export);
        // Belleğe de yükle
        self::$data = $all;
    }

    /** Cache dosyasını temizler */
    public static function clearCache(): void
    {
        if (is_file(self::$cacheFile)) {
            @unlink(self::$cacheFile);
        }
        // Yeniden yükle
        self::$data = self::loadAll(self::$configDir);
    }
}
