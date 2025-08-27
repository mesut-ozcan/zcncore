<?php
namespace Core;

final class Application
{
    private static ?Application $instance = null;
    private string $basePath;
    private array $container = [];
    private string $version = '2.7.0';
    private float $startedAt;

    private function __construct(string $basePath)
    {
        // Windows + Unix uyumlu
        $this->basePath = rtrim($basePath, "/\\");
        $this->startedAt = microtime(true);
    }

    public static function boot(string $basePath): Application
    {
        if (!self::$instance) {
            self::$instance = new Application($basePath);

            // Ortam ve config
            Env::load(self::$instance->basePath('.env'));
            Config::init(self::$instance->basePath('app/Config'), self::$instance->basePath());

            // Logger::init(...) KALDIRILDI — v2.7.0'da yok; ilk log çağrısında Logger::boot() otomatik olur.
            // Logger::info('app booted'); // istersen böyle tetikleyebilirsin

            // Diğer servis bootstrap'ları (projene göre mevcutsa)
            if (class_exists(\Core\Cache::class) && method_exists(\Core\Cache::class, 'init')) {
                Cache::init(self::$instance->basePath('storage/cache'));
            }
            if (class_exists(\Core\Events::class) && method_exists(\Core\Events::class, 'init')) {
                Events::init();
            }
            if (class_exists(\Core\Head::class) && method_exists(\Core\Head::class, 'init')) {
                Head::init();
            }
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
        return $this->basePath . ($path ? '/' . ltrim($path, '/\\') : '');
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
