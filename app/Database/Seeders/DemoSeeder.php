<?php
namespace App\Database\Seeders;

use Core\Database\Seeder;
use Core\Database\QueryBuilder as QB;

final class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // örnek:
        // QB::table('demo_items')->insert(['title'=>'Demo','created_at'=>date('Y-m-d H:i:s')]);
    }
}