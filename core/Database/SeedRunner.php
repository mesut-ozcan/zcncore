<?php
namespace Core\Database;

/**
 * Basit seeder runner.
 * CLI iki kolay metot bekliyor:
 *  - SeedRunner::runClass('UsersSeeder')
 *  - SeedRunner::runAll()
 *
 * Aşağıda bu iki metot + ortak run(array $seeders) uygulanıyor.
 */
final class SeedRunner
{
    /**
     * Belirtilen sınıfı (kısa ad veya FQCN) tek başına çalıştır.
     * Ör: 'UsersSeeder' ya da 'App\Database\Seeders\UsersSeeder'
     */
    public static function runClass(string $classOrShort): void
    {
        $cls = self::normalizeSeederClass($classOrShort);
        self::run([$cls]);
    }

    /**
     * Eğer App\Database\Seeders\DatabaseSeeder varsa onu çalıştırır.
     * Yoksa app/Database/Seeders altındaki *Seeder.php dosyalarını sırayla çalıştırır.
     */
    public static function runAll(): void
    {
        $dbSeeder = 'App\\Database\\Seeders\\DatabaseSeeder';
        if (class_exists($dbSeeder) || self::requireIfExists($dbSeeder)) {
            self::run([$dbSeeder]);
            return;
        }

        // Fallback: tüm *Seeder.php dosyalarını sırayla
        $all = self::discoverAllSeeders();
        if (empty($all)) {
            echo "[seed] no seeders found under app/Database/Seeders\n";
            return;
        }
        self::run($all);
    }

    /** @param class-string<Seeder>[] $seeders */
    public static function run(array $seeders): void
    {
        foreach ($seeders as $cls) {
            if (!class_exists($cls)) {
                self::requireByClass($cls);
            }
            if (!class_exists($cls)) {
                echo "[seed] class not found: {$cls}\n";
                continue;
            }
            /** @var Seeder $s */
            $s = new $cls();
            echo "[seed] running: {$cls}\n";
            $s->run();
        }
    }

    // --------- yardımcılar ---------

    /**
     * 'UsersSeeder' gibi kısa adı FQCN'e çevirir.
     */
    private static function normalizeSeederClass(string $name): string
    {
        if (str_contains($name, '\\')) {
            return $name;
        }
        return 'App\\Database\\Seeders\\' . preg_replace('/[^A-Za-z0-9_]/', '', $name);
    }

    /**
     * Verilen FQCN için dosyayı tahmin edip require eder.
     */
    private static function requireByClass(string $fqcn): void
    {
        $rel = str_replace('\\', '/', $fqcn) . '.php';
        $base = \Core\Application::get()->basePath();
        $try  = $base . '/' . $rel;
        if (is_file($try)) {
            require_once $try;
        }
    }

    /**
     * Sınıf yoksa dosyayı deneyip yükler, sonra var mı diye döner.
     */
    private static function requireIfExists(string $fqcn): bool
    {
        if (class_exists($fqcn)) return true;
        self::requireByClass($fqcn);
        return class_exists($fqcn);
    }

    /**
     * app/Database/Seeders altındaki *Seeder.php dosyalarını FQCN listesi olarak döndürür.
     */
    private static function discoverAllSeeders(): array
    {
        $base = \Core\Application::get()->basePath('app/Database/Seeders');
        if (!is_dir($base)) return [];

        $out = [];
        foreach (scandir($base) ?: [] as $f) {
            if ($f === '.' || $f === '..') continue;
            if (!preg_match('/^[A-Za-z0-9_]+Seeder\.php$/', $f)) continue;

            $class = 'App\\Database\\Seeders\\' . substr($f, 0, -4); // .php kırp
            // Dosyayı da yüklemeyi dene
            $full = $base . '/' . $f;
            if (is_file($full)) require_once $full;

            if (class_exists($class)) {
                $out[] = $class;
            }
        }

        // İstiyorsan alfabetik sırala (deterministik olsun)
        sort($out, SORT_STRING);
        return $out;
    }
}