<?php
namespace Core\Database;

use PDO;
use Core\Config;
use Core\Logger;

/**
 * ZCNCore Migration Runner
 *
 * Migration dosyası biçimi:
 *   database/migrations/20250101_120000_create_users.php
 *   <?php
 *   return new class {
 *     public function up(\PDO $db): void { / ... DDL ... / }
 *     public function down(\PDO $db): void { / ... revert ... / }
 *   };
 */
final class Migrator
{
    private static function pdo(): PDO
    {
        // Projede halihazırda Connection sınıfın varsa onu kullan:
        if (class_exists(\Core\Database\Connection::class)) {
            /** @var \Core\Database\Connection $c */
            $c = \Core\Database\Connection::getInstance();
            return $c->pdo();
        }

        // Aksi halde .env'den PDO kur (fallback)
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $name = getenv('DB_NAME') ?: 'zcncore';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    }

    private static function migrationsDir(): string
    {
        $base = self::basePath();
        $dir = $base . '/database/migrations';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        return $dir;
    }

    private static function basePath(): string
    {
        // public/index.php’de Application basePath kullanılıyor; burada basit çözüm:
        $here = realpath(__DIR__ . '/..//..'); // core/Database → proje kökü
        return $here ?: (__DIR__ . '/..//..');
    }

    private static function ensureTable(PDO $db): void
    {
        // MySQL uyumlu tablo
        $db->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    /** @return array<string> dosya adları (tam yol) */
    private static function allMigrationFiles(): array
    {
        $files = glob(self::migrationsDir() . '/*.php') ?: [];
        sort($files, SORT_NATURAL);
        return $files;
    }

    /** @return array<string,int> applied['filename.php'] = batch */
    private static function applied(PDO $db): array
    {
        $rows = $db->query("SELECT migration, batch FROM migrations ORDER BY id ASC")->fetchAll() ?: [];
        $out = [];
        foreach ($rows as $r) {
            $out[$r['migration']] = (int)$r['batch'];
        }
        return $out;
    }

    private static function currentBatch(PDO $db): int
    {
        $row = $db->query("SELECT MAX(batch) AS b FROM migrations")->fetch();
        return (int)($row['b'] ?? 0);
    }

    private static function filename(string $fullPath): string
    {
        return basename($fullPath);
    }

    public static function status(): void
    {
        $db = self::pdo();
        self::ensureTable($db);

        $files = self::allMigrationFiles();
        $applied = self::applied($db);

        foreach ($files as $f) {
            $name = self::filename($f);
            if (isset($applied[$name])) {
                echo "[x] {$name} (batch {$applied[$name]})\n";
            } else {
                echo "[ ] {$name}\n";
            }
        }
    }

    public static function migrate(): void
    {
        $db = self::pdo();
        self::ensureTable($db);

        $files = self::allMigrationFiles();
        $applied = self::applied($db);
        $nextBatch = self::currentBatch($db) + 1;

        foreach ($files as $f) {
            $name = self::filename($f);
            if (isset($applied[$name])) {
                continue; // zaten uygulanmış
            }

            $migration = require $f;
            if (!is_object($migration) || !method_exists($migration, 'up')) {
                echo "Skip (invalid): {$name}\n";
                continue;
            }

            // Not: MySQL DDL'ler implicit commit yapabilir; bu yüzden tek tek çalıştırıyoruz
            try {
                $migration->up($db);
                $stmt = $db->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                $stmt->execute([$name, $nextBatch]);
                echo "Migrated: {$name}\n";
            } catch (\Throwable $e) {
                echo "Migrate error in {$name}: ".$e->getMessage()."\n";
                Logger::error("Migration '{$name}' failed: ".$e->getMessage());
                // Devam etmeyelim; hata durumunda çıkmak daha güvenli
                break;
            }
        }
    }

    public static function rollback(): void
    {
        $db = self::pdo();
        self::ensureTable($db);

        $batch = self::currentBatch($db);
        if ($batch <= 0) {
            echo "No batches to rollback.\n";
            return;
        }

        // Bu batch'teki migration'ları tersten çalıştır
        $stmt = $db->prepare("SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC");
        $stmt->execute([$batch]);
        $rows = $stmt->fetchAll();

        if (!$rows) {
            echo "No migrations in last batch.\n";
            return;
        }

        foreach ($rows as $r) {
            $name = $r['migration'];
            $path = self::migrationsDir() . '/' . $name;

            if (!is_file($path)) {
                // Dosya yok; yine de kayıt silinsin
                $db->prepare("DELETE FROM migrations WHERE migration = ?")->execute([$name]);
                echo "Rolled back (file missing): {$name}\n";
                continue;
            }

            $migration = require $path;
            if (!is_object($migration) || !method_exists($migration, 'down')) {
                // down yoksa sadece kaydı sil
                $db->prepare("DELETE FROM migrations WHERE migration = ?")->execute([$name]);
                echo "Rolled back (no down): {$name}\n";
                continue;
            }

            try {
                $migration->down($db);
                $db->prepare("DELETE FROM migrations WHERE migration = ?")->execute([$name]);
                echo "Rolled back: {$name}\n";
            } catch (\Throwable $e) {
                echo "Rollback error in {$name}: ".$e->getMessage()."\n";
                Logger::error("Rollback '{$name}' failed: ".$e->getMessage());
                // Devam et; diğerlerini de dene
            }
        }
    }

    public static function make(string $name): void
    {
        $dir = self::migrationsDir();

        // Dosya adı: timestamp + temiz ad
        $ts = date('Ymd_His');
        $clean = preg_replace('/[^a-zA-Z0-9_]+/', '_', $name);
        $file = "{$dir}/{$ts}_{$clean}.php";

        $tpl = <<<'PHP'
<?php
return new class {
    public function up(\PDO $db): void
    {
        // Örnek:
        // $db->exec("CREATE TABLE demo_items (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(120) NOT NULL)");
    }

    public function down(\PDO $db): void
    {
        // Örnek:
        // $db->exec("DROP TABLE IF EXISTS demo_items");
    }
};
PHP;

        file_put_contents($file, $tpl);
        echo "Created migration: {$file}\n";
    }
}
