<?php
return new class {
    public function up(\PDO $pdo): void
    {
        // 1) Taban tabloyu oluştur (varsa dokunma)
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `sessions` (
                `id`          VARCHAR(128)  NOT NULL,
                `payload`     MEDIUMBLOB    NOT NULL,
                `created_at`  DATETIME      NOT NULL,
                `expires_at`  DATETIME      NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        // 2) Eksik kolonları ekle (mevcut tabloları ileriye taşır)
        $this->ensureColumn($pdo, 'sessions', 'user_id',    "INT NULL AFTER `payload`");
        $this->ensureColumn($pdo, 'sessions', 'ip_address', "VARCHAR(45) NULL AFTER `user_id`");
        $this->ensureColumn($pdo, 'sessions', 'user_agent', "VARCHAR(255) NULL AFTER `ip_address`");

        // 3) İndeksleri güvenli kur
        $this->ensureIndex($pdo, 'sessions', 'idx_sessions_expires', '(`expires_at`)');
        $this->ensureIndex($pdo, 'sessions', 'idx_sessions_user',    '(`user_id`)');
    }

    public function down(\PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS `sessions`;");
    }

    private function ensureColumn(\PDO $pdo, string $table, string $col, string $definition): void
    {
        // Kolon var mı?
        $stmt = $pdo->prepare("
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :t
              AND COLUMN_NAME = :c
            LIMIT 1
        ");
        $stmt->execute([':t' => $table, ':c' => $col]);
        if ($stmt->fetch()) {
            return;
        }

        // Yoksa ekle
        $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$definition}";
        $pdo->exec($sql);
    }

    private function ensureIndex(\PDO $pdo, string $table, string $index, string $cols): void
    {
        // Zaten var mı?
        $stmt = $pdo->prepare("SHOW INDEX FROM `{$table}` WHERE Key_name = :k");
        $stmt->execute([':k' => $index]);
        if ($stmt->fetch()) {
            return;
        }

        // Yoksa oluştur
        try {
            $pdo->exec("CREATE INDEX `{$index}` ON `{$table}` {$cols};");
        } catch (\PDOException $e) {
            // 1061 = Duplicate key name — zaten varsa göz ardı et
            $info = $e->errorInfo;
            if (!isset($info[1]) || (int)$info[1] !== 1061) {
                throw $e;
            }
        }
    }
};