<?php
namespace Core;

final class Logger
{
    private static string $dir;

    public static function init(string $dir): void
    {
        self::$dir = $dir;
        if (!is_dir($dir)) @mkdir($dir, 0777, true);
    }

    public static function log(string $level, string $msg): void
    {
        $file = self::$dir . '/app.log';
        $line = sprintf("[%s] %s: %s\n", date('Y-m-d H:i:s'), strtoupper($level), $msg);
        file_put_contents($file, $line, FILE_APPEND);
    }

    public static function debug($m){ self::log('debug', (string)$m); }
    public static function info($m){ self::log('info', (string)$m); }
    public static function warning($m){ self::log('warning', (string)$m); }
    public static function error($m){ self::log('error', (string)$m); }
}
