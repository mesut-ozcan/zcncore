<?php
namespace App\Database\Seeders;

use Core\Database\Seeder;
use Core\Database\Connection;

final class PagesSeeder extends Seeder
{
    public function run(): void
    {
        $pdo = Connection::getInstance()->pdo();

        $pdo->exec("CREATE TABLE IF NOT EXISTS pages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            slug VARCHAR(191) UNIQUE,
            title VARCHAR(191),
            body MEDIUMTEXT,
            created_at DATETIME NULL,
            updated_at DATETIME NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pages WHERE slug=?");
        $stmt->execute(['hello-zcn']);
        if ((int)$stmt->fetchColumn() > 0) {
            echo "[seed] pages already has hello-zcn, skipping\n";
            return;
        }

        $now = date('Y-m-d H:i:s');
        $ins = $pdo->prepare("INSERT INTO pages (slug,title,body,created_at,updated_at) VALUES (?,?,?,?,?)");
        $ins->execute([
            'hello-zcn',
            'Hello ZCN',
            '<p>Welcome to ZCNCore!</p>',
            $now, $now
        ]);

        echo "[seed] pages inserted: 1\n";
    }
}