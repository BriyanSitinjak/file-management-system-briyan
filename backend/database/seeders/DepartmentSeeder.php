<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
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
