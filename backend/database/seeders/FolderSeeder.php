<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds a small nested folder tree under the demo departments.
 * Exists so browsing and upload flows have parent folders ready without manual setup.
 */
class FolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $departments = Department::query()->whereIn('name', ['IT', 'HR', 'Finance'])->get()->keyBy('name');

        $itRoot = Folder::query()->updateOrCreate(
            [
                'name' => 'Projects',
                'department_id' => $departments['IT']->id,
                'parent_id' => null,
            ],
            ['user_id' => $admin->id],
        );

        Folder::query()->updateOrCreate(
            [
                'name' => 'Infrastructure',
                'department_id' => $departments['IT']->id,
                'parent_id' => $itRoot->id,
            ],
            ['user_id' => $admin->id],
        );

        Folder::query()->updateOrCreate(
            [
                'name' => 'Applications',
                'department_id' => $departments['IT']->id,
                'parent_id' => $itRoot->id,
            ],
            ['user_id' => $admin->id],
        );

        $hrRoot = Folder::query()->updateOrCreate(
            [
                'name' => 'Policies',
                'department_id' => $departments['HR']->id,
                'parent_id' => null,
            ],
            ['user_id' => $admin->id],
        );

        Folder::query()->updateOrCreate(
            [
                'name' => 'Onboarding',
                'department_id' => $departments['HR']->id,
                'parent_id' => $hrRoot->id,
            ],
            ['user_id' => $admin->id],
        );

        Folder::query()->updateOrCreate(
            [
                'name' => 'Budgets',
                'department_id' => $departments['Finance']->id,
                'parent_id' => null,
            ],
            ['user_id' => $admin->id],
        );
    }
}
