<?php
return new class {
    public function up(\PDO $db): void
    {
        // tablo
        $db->exec("
            CREATE TABLE IF NOT EXISTS posts (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                content MEDIUMTEXT NOT NULL,
                published_at DATETIME NULL,
                created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        // MySQL'de CREATE INDEX IF NOT EXISTS yok → duplicate ise sessiz geç
        try {
            $db->exec("CREATE INDEX idx_posts_published ON posts (published_at)");
        } catch (\Throwable $e) {
            // 1061 Duplicate key name | 42000/1061 vb. yakalanır
            if (strpos($e->getMessage(), '1061') === false) {
                throw $e;
            }
        }
    }

    public function down(\PDO $db): void
    {
        $db->exec("DROP TABLE IF EXISTS posts");
    }
};