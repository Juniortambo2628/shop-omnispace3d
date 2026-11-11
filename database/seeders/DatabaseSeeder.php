<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // AdminUserSeeder is not in git (it holds real people's details):
        // copy AdminUserSeeder.example.php to AdminUserSeeder.php to seed admins.
        if (class_exists(AdminUserSeeder::class)) {
            $this->call(AdminUserSeeder::class);
        } else {
            $this->command?->warn('Admin users not seeded: create database/seeders/AdminUserSeeder.php from AdminUserSeeder.example.php.');
        }

        $this->call([
            SettingsSeeder::class,
            CatalogProductSeeder::class,
            ProductImageSeeder::class,
        ]);
    }
}
