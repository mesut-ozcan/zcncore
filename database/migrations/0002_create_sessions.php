<?php
return new class {
    public function up(\PDO $db): void
    {
        $db->exec("
            CREATE TABLE IF NOT EXISTS sessions (
                id VARCHAR(80) PRIMARY KEY,
                payload MEDIUMBLOB NOT NULL,
                last_activity INT UNSIGNED NOT NULL,
                expires_at INT UNSIGNED NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $db->exec("CREATE INDEX IF NOT EXISTS idx_sessions_expires ON sessions (expires_at)");
    }
    public function down(\PDO $db): void
    {
        $db->exec("DROP TABLE IF EXISTS sessions");
    }
};
