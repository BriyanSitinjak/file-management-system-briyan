<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Entry point that runs all demo seeders in dependency order.
 * Exists to bootstrap a usable local dataset with one artisan db:seed command.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DepartmentSeeder::class,
            FolderSeeder::class,
        ]);
    }
}
