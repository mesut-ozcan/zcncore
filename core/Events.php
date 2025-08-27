<?php
namespace Core;

final class Events
{
    private static array $listeners = [];

    public static function init(): void
    {
        self::$listeners = [];
    }

    public static function listen(string $event, callable $cb): void
    {
        self::$listeners[$event][] = $cb;
    }

    public static function dispatch(string $event, $payload = null): void
    {
        foreach (self::$listeners[$event] ?? [] as $cb) {
            $cb($payload);
        }
    }
}
