<?php
namespace Core\Database\Seeders;

use PDO;
use RuntimeException;

class SeedRunner
{
    private PDO $pdo;
    private string $dir;
    private string $ns;

    public function __construct(PDO $pdo, string $seedersDir, string $namespace = 'App\\Database\\Seeders')
    {
        $this->pdo = $pdo;
        $this->dir = rtrim($seedersDir, DIRECTORY_SEPARATOR);
        $this->ns  = rtrim($namespace, '\\');
    }

    public function runClass(string $class): void
    {
        $fqcn = $this->normalizeClass($class);
        $file = $this->classToFile($fqcn);
        if ($file && is_file($file)) require_once $file;
        if (!class_exists($fqcn)) throw new RuntimeException("Seeder class not found: {$fqcn}");

        $obj = new $fqcn();
        if (!($obj instanceof Seeder) && !method_exists($obj, 'run')) {
            throw new RuntimeException("Seeder must extend Seeder or implement run(PDO): {$fqcn}");
        }
        $obj->run($this->pdo);
    }

    public function runAll(): void
    {
        $dbSeeder = $this->ns . '\\DatabaseSeeder';
        $dbSeederFile = $this->classToFile($dbSeeder);
        if ($dbSeederFile && is_file($dbSeederFile)) require_once $dbSeederFile;

        if (class_exists($dbSeeder)) {
            $this->runClass($dbSeeder);
            return;
        }

        $files = glob($this->dir . DIRECTORY_SEPARATOR . '*Seeder.php') ?: [];
        sort($files, SORT_STRING);
        foreach ($files as $f) {
            require_once $f;
            $name = basename($f, '.php');
            if ($name === 'DatabaseSeeder') continue;
            $fqcn = $this->ns . '\\' . $name;
            if (class_exists($fqcn)) $this->runClass($fqcn);
        }
    }

    private function normalizeClass(string $class): string
    {
        $class = ltrim($class, '\\');
        return str_starts_with($class, $this->ns.'\\') ? $class : $this->ns.'\\'.$class;
    }

    private function classToFile(string $fqcn): ?string
    {
        $prefix = $this->ns . '\\';
        if (!str_starts_with($fqcn, $prefix)) return null;
        $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($fqcn, strlen($prefix)));
        return $this->dir . DIRECTORY_SEPARATOR . $relative . '.php';
    }
}