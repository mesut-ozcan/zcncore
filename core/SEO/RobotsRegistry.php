<?php
namespace Core\SEO;

final class RobotsRegistry
{
    private static array $lines = [
        'User-agent: *',
        'Disallow: /storage/'
    ];

    public static function add(string $line): void
    {
        self::$lines[] = $line;
    }

    public static function render(): string
    {
        return implode("\n", self::$lines);
    }
}
