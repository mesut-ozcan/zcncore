<?php
return new class {
    public function up(PDO $pdo){
        $pdo->exec("CREATE TABLE IF NOT EXISTS pages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(160) NOT NULL UNIQUE,
            title VARCHAR(160) NOT NULL,
            body TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }
    public function down(PDO $pdo){
        $pdo->exec("DROP TABLE IF EXISTS pages");
    }
};
