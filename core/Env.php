<?php
namespace Core;

final class Env
{
    public static function load(string $path): void
    {
        if (!is_file($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
            $k = trim($k);
            $v = trim($v);
            if (str_starts_with($v, '"') && str_ends_with($v, '"')) {
                $v = trim($v, '"');
            }
            $_ENV[$k] = $v;
            putenv("$k=$v");
        }
    }

    public static function get(string $key, $default = null)
    {
        $v = $_ENV[$key] ?? getenv($key);
        return $v !== false && $v !== null ? $v : $default;
    }
}
