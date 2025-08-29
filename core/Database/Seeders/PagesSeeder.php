<?php
namespace Core\Database\Seeders;

use Core\Database\Seeder;
use PDO;

/**
 * Örnek PagesSeeder (framework demo).
 */
final class PagesSeeder extends Seeder
{
    public function run(PDO $pdo): void
    {
        $this->insert($pdo, 'pages', [
            'slug'       => 'hello-core',
            'title'      => 'Hello from Core',
            'content'    => '<p>Merhaba ZCNCore!</p>',
            'created_at' => $this->now(),
            'updated_at' => $this->now(),
        ]);
    }
}