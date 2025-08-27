<?php
namespace Core;

final class Logger
{
    private static bool $booted = false;
    private static string $dir;
    private static string $pattern;
    private static int $retention;
    private static int $levelNum;

    private const LEVELS = [
        'debug'   => 0,
        'info'    => 1,
        'warning' => 2,
        'error'   => 3,
    ];

    public static function boot(): void
    {
        if (self::$booted) return;
        $cfg = Config::get('log', []);
        $rel = $cfg['path'] ?? 'storage/logs';
        self::$dir      = base_path($rel);
        self::$pattern  = (string)($cfg['filename'] ?? 'app-{Y-m-d}.log');
        self::$retention= (int)($cfg['retention_days'] ?? 14);

        $level = strtolower((string)($cfg['level'] ?? 'debug'));
        self::$levelNum = self::LEVELS[$level] ?? 0;

        if (!is_dir(self::$dir)) @mkdir(self::$dir, 0777, true);
        self::purgeOld();
        self::$booted = true;
    }

    private static function filePath(): string
    {
        $name = self::$pattern;
        $name = preg_replace_callback('/\{([^}]+)\}/', fn($m) => date($m[1]), $name);
        return rtrim(self::$dir, '/\\') . DIRECTORY_SEPARATOR . $name;
    }

    private static function write(string $level, string $message): void
    {
        self::boot();
        $lvlNum = self::LEVELS[$level] ?? 0;
        if ($lvlNum < self::$levelNum) return;

        $line = sprintf("[%s] %s.%s: %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            getmypid(),
            $message
        );
        @file_put_contents(self::filePath(), $line, FILE_APPEND);
    }

    private static function purgeOld(): void
    {
        if (self::$retention <= 0) return;
        $files = glob(self::$dir . '/*.log') ?: [];
        $threshold = time() - (self::$retention * 86400);
        foreach ($files as $f) {
            if (@filemtime($f) < $threshold) @unlink($f);
        }
    }

    public static function debug(string $m): void { self::write('debug', $m); }
    public static function info(string $m): void { self::write('info', $m); }
    public static function warning(string $m): void { self::write('warning', $m); }
    public static function error(string $m): void { self::write('error', $m); }
}
