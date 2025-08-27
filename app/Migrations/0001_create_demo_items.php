<?php
return new class {
    public function up(PDO $pdo){
        $pdo->exec("CREATE TABLE IF NOT EXISTS demo_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(120) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    }
    public function down(PDO $pdo){
        $pdo->exec("DROP TABLE IF EXISTS demo_items");
    }
};
