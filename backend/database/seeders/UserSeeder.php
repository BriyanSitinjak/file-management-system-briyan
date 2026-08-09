<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds the demo Administrator and Viewer login accounts referenced in the README.
 * Exists so local and review environments can authenticate without creating users by hand.
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Demo Administrator',
                'password' => Hash::make('password'),
                'role' => 'Administrator',
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'viewer@example.com'],
            [
                'name' => 'Demo Viewer',
                'password' => Hash::make('password'),
                'role' => 'Viewer',
            ],
        );
    }
}
