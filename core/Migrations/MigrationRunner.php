<?php
namespace Core\Migrations;

use Core\Database;

final class MigrationRunner
{
    private \PDO $pdo;
    private string $migrationsTable = 'migrations';

    public function __construct()
    {
        $this->pdo = Database::pdo();
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->migrationsTable} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->pdo->exec($sql);
    }

    private function loadExisting(): array
    {
        $rows = $this->pdo->query("SELECT migration, batch FROM {$this->migrationsTable}")->fetchAll();
        $applied = [];
        foreach ($rows as $r) $applied[$r['migration']] = (int)$r['batch'];
        return $applied;
    }

    private function nextBatch(): int
    {
        $row = $this->pdo->query("SELECT MAX(batch) AS b FROM {$this->migrationsTable}")->fetch();
        return (int)($row['b'] ?? 0) + 1;
    }

    private function migrationFiles(): array
    {
        $roots = [
            base_path('app/Migrations'),
            base_path('modules'),
        ];
        $files = [];

        // app/Migrations
        if (is_dir($roots[0])) {
            foreach (glob($roots[0] . '/*.php') as $f) {
                $files[basename($f)] = $f;
            }
        }
        // modules/*/Migrations
        if (is_dir($roots[1])) {
            foreach (scandir($roots[1]) as $mod) {
                if ($mod==='.'||$mod==='..') continue;
                $dir = $roots[1].'/'.$mod.'/Migrations';
                if (is_dir($dir)) {
                    foreach (glob($dir.'/*.php') as $f) {
                        $files[basename($f)] = $f;
                    }
                }
            }
        }
        ksort($files); // 0001_, 0002_ sıralama
        return $files;
    }

    public function status(): array
    {
        $applied = $this->loadExisting();
        $files = $this->migrationFiles();
        $list = [];
        foreach ($files as $name => $path) {
            $list[] = [
                'name' => $name,
                'applied' => array_key_exists($name, $applied),
                'batch' => $applied[$name] ?? null,
            ];
        }
        return $list;
    }

    public function migrate(): void
    {
        $applied = $this->loadExisting();
        $files = $this->migrationFiles();
        $batch = $this->nextBatch();

        foreach ($files as $name => $path) {
            if (isset($applied[$name])) continue; // zaten uygulanmış

            $migration = require $path;
            if (!is_object($migration) || !method_exists($migration, 'up')) {
                throw new \RuntimeException("Invalid migration file: $name");
            }

            // Transaction'ı güvenli başlat (DDL implicit commit riskine karşı guard'la)
            $txStarted = false;
            try {
                if (!$this->pdo->inTransaction()) {
                    $this->pdo->beginTransaction();
                    $txStarted = true;
                }

                $migration->up($this->pdo);

                // migration kaydı
                $stmt = $this->pdo->prepare("INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (?, ?)");
                $stmt->execute([$name, $batch]);

                if ($txStarted && $this->pdo->inTransaction()) {
                    $this->pdo->commit();
                }

                echo "Migrated: $name\n";
            } catch (\Throwable $e) {
                // yalnızca aktif transaction varsa rollback dene
                if ($txStarted && $this->pdo->inTransaction()) {
                    try { $this->pdo->rollBack(); } catch (\Throwable $e2) {}
                }
                throw $e;
            }
        }
    }

    public function rollback(): void
    {
        // son batch
        $row = $this->pdo->query("SELECT MAX(batch) AS b FROM {$this->migrationsTable}")->fetch();
        $last = (int)($row['b'] ?? 0);
        if ($last === 0) { echo "Nothing to rollback.\n"; return; }

        // files map
        $files = $this->migrationFiles();

        // son batch'teki migration'lar (ters sırada)
        $stmt = $this->pdo->prepare("SELECT migration FROM {$this->migrationsTable} WHERE batch = ? ORDER BY id DESC");
        $stmt->execute([$last]);
        $list = $stmt->fetchAll();

        foreach ($list as $row) {
            $name = $row['migration'];
            $path = $files[$name] ?? null;
            if (!$path || !is_file($path)) {
                throw new \RuntimeException("Migration file missing for rollback: $name");
            }
            $migration = require $path;
            if (!is_object($migration) || !method_exists($migration, 'down')) {
                throw new \RuntimeException("Invalid migration file (down missing): $name");
            }

            $txStarted = false;
            try {
                if (!$this->pdo->inTransaction()) {
                    $this->pdo->beginTransaction();
                    $txStarted = true;
                }

                $migration->down($this->pdo);

                $del = $this->pdo->prepare("DELETE FROM {$this->migrationsTable} WHERE migration=? LIMIT 1");
                $del->execute([$name]);

                if ($txStarted && $this->pdo->inTransaction()) {
                    $this->pdo->commit();
                }

                echo "Rolled back: $name\n";
            } catch (\Throwable $e) {
                if ($txStarted && $this->pdo->inTransaction()) {
                    try { $this->pdo->rollBack(); } catch (\Throwable $e2) {}
                }
                throw $e;
            }
        }
    }
}
