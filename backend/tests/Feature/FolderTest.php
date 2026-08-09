<?php

use App\Models\Department;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('allows an administrator to create a folder', function () {
    $admin = User::factory()->administrator()->create();
    $department = Department::query()->create(['name' => 'IT']);

    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/folders', [
        'name' => 'Projects',
        'department_id' => $department->id,
        'parent_id' => null,
    ]);

    $response->assertCreated()
        ->assertJsonPath('name', 'Projects')
        ->assertJsonPath('department_id', $department->id);

    $this->assertDatabaseHas('folders', [
        'name' => 'Projects',
        'department_id' => $department->id,
        'user_id' => $admin->id,
    ]);
});

it('prevents a viewer from creating a folder', function () {
    $viewer = User::factory()->viewer()->create();
    $department = Department::query()->create(['name' => 'HR']);

    Sanctum::actingAs($viewer);

    $this->postJson('/api/folders', [
        'name' => 'Secret',
        'department_id' => $department->id,
    ])->assertForbidden();

    $this->assertDatabaseMissing('folders', [
        'name' => 'Secret',
    ]);
});
