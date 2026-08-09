<?php

use App\Models\ActivityLog;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('allows an administrator to list activity logs', function () {
    $admin = User::factory()->administrator()->create();

    ActivityLog::query()->create([
        'user_id' => $admin->id,
        'action' => 'folder.created',
        'description' => 'Created folder Projects',
    ]);

    Sanctum::actingAs($admin);

    $this->getJson('/api/activity-logs')
        ->assertOk()
        ->assertJsonFragment(['action' => 'folder.created']);
});

it('prevents a viewer from listing activity logs', function () {
    $viewer = User::factory()->viewer()->create();

    Sanctum::actingAs($viewer);

    $this->getJson('/api/activity-logs')->assertForbidden();
});
