<?php
namespace Core\Database\Seeders;

use Core\Database\Seeder;
use PDO;

/**
 * Örnek UsersSeeder (framework demo).
 */
final class UsersSeeder extends Seeder
{
    public function run(PDO $pdo): void
    {
        $this->insert($pdo, 'users', [
            'name'       => 'Core Admin',
            'email'      => 'coreadmin@example.com',
            'password'   => password_hash('secret', PASSWORD_BCRYPT),
            'role'       => 'admin',
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ]);
    }
}