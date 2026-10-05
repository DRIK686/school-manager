<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. Both seeders are insert-only,
     * so this is safe to run on an existing installation.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            WebsiteDefaultsSeeder::class,
        ]);
    }
}
