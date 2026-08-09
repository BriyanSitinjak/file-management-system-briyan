<?php

use App\Models\User;

it('logs in with valid credentials and returns a token', function () {
    $user = User::factory()->administrator()->create([
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonPath('user.email', $user->email)
        ->assertJsonStructure(['token', 'user' => ['id', 'email', 'role']]);
});

it('rejects invalid login credentials', function () {
    User::factory()->administrator()->create([
        'email' => 'admin@example.com',
        'password' => 'password',
    ]);

    $this->postJson('/api/login', [
        'email' => 'admin@example.com',
        'password' => 'wrong-password',
    ])->assertStatus(422);
});
