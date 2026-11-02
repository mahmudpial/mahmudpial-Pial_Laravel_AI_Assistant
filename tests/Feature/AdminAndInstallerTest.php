<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest can access installer step1', function () {
    $response = $this->get('/install/step1');

    $response->assertStatus(200);
});

test('regular user cannot access admin dashboard', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)->get('/admin');

    $response->assertStatus(403);
});

test('admin user can access admin dashboard', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->get('/admin');

    $response->assertStatus(200);
});
