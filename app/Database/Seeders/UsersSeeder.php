<?php
namespace App\Database\Seeders;

use Core\Database\Seeder;
use Core\Database\Connection;

final class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $pdo = Connection::getInstance()->pdo();

        // basit örnek: varsa tabloyu doldur
        $stmt = $pdo->query("SELECT COUNT(*) FROM users");
        $count = (int)$stmt->fetchColumn();
        if ($count > 0) {
            echo "[seed] users already has {$count} rows, skipping\n";
            return;
        }

        $now = date('Y-m-d H:i:s');
        $ins = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES (?,?,?,?,?,?)");

        $users = [
            ['Admin', 'admin@example.com', password_hash('secret', PASSWORD_BCRYPT), 'admin', $now, $now],
            ['Demo',  'demo@example.com',  password_hash('secret', PASSWORD_BCRYPT), 'user',  $now, $now],
        ];

        foreach ($users as $u) {
            $ins->execute($u);
        }

        echo "[seed] users inserted: ".count($users)."\n";
    }
}