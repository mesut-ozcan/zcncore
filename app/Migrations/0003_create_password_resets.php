<?php
return new class {
    public function up(PDO $pdo){
        $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(160) NOT NULL,
            token VARCHAR(120) NOT NULL,
            used TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (email),
            UNIQUE KEY token_unique (token)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }
    public function down(PDO $pdo){
        $pdo->exec("DROP TABLE IF EXISTS password_resets");
    }
};
