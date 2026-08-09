<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

/**
 * Seeds sample departments used to scope folders and files in demos.
 * Exists so the UI and API have realistic organizational units after a fresh migrate.
 */
class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['IT', 'HR', 'Finance'] as $name) {
            Department::query()->updateOrCreate(
                ['name' => $name],
                ['name' => $name],
            );
        }
    }
}
