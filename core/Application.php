<?php
namespace Core;

final class Application
{
    private static ?Application $instance = null;
    private string $basePath;
    private array $container = [];
    private string $version = '2.2.0';
    private float $startedAt;

    private function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->startedAt = microtime(true);
    }

    public static function boot(string $basePath): Application
    {
        if (!self::$instance) {
            self::$instance = new Application($basePath);
            Env::load($basePath . '/.env');
            Config::init($basePath . '/app/Config', $basePath);
            Logger::init($basePath . '/storage/logs');
            Cache::init($basePath . '/storage/cache');
            Events::init();
            Head::init();
        }
        return self::$instance;
    }

    public static function get(): Application
    {
        if (!self::$instance) {
            throw new \RuntimeException("Application not booted");
        }
        return self::$instance;
    }

    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? '/' . ltrim($path, '/') : '');
    }

    public function bind(string $id, $service): void
    {
        $this->container[$id] = $service;
    }

    public function make(string $id)
    {
        return $this->container[$id] ?? null;
    }

    public function version(): string
    {
        return $this->version;
    }

    public function startedAt(): float
    {
        return $this->startedAt;
    }
}
