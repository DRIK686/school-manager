<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * The roles the application expects. Insert-only (matched on slug), created
 * in a fixed order so IDs are 1..8 on a fresh database.
 */
class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $roles = [
            ['Super Admin', 'super_admin'],
            ['Admin', 'admin'],
            ['Teacher', 'teacher'],
            ['Accountant', 'accountant'],
            ['Librarian', 'librarian'],
            ['Receptionist', 'receptionist'],
            ['Parent', 'parent'],
            ['Student', 'student'],
        ];

        foreach ($roles as [$name, $slug]) {
            if (! DB::table('roles')->where('slug', $slug)->exists()) {
                DB::table('roles')->insert([
                    'name' => $name, 'slug' => $slug, 'description' => null,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }
}
