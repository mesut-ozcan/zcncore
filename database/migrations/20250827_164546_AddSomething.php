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