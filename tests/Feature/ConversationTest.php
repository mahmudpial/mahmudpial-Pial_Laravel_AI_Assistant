<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can view chat page and conversation list', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/chat');

    $response->assertStatus(200);
});

test('authenticated user can rename their conversation', function () {
    $user = User::factory()->create();
    $conversation = Conversation::create([
        'user_id' => $user->id,
        'title' => 'Original Title',
    ]);

    $response = $this->actingAs($user)->postJson("/chat/{$conversation->id}/rename", [
        'title' => 'Updated Title',
    ]);

    $response->assertStatus(200)
        ->assertJson(['success' => true, 'title' => 'Updated Title']);

    $this->assertDatabaseHas('conversations', [
        'id' => $conversation->id,
        'title' => 'Updated Title',
    ]);
});

test('authenticated user can delete their conversation', function () {
    $user = User::factory()->create();
    $conversation = Conversation::create([
        'user_id' => $user->id,
        'title' => 'To Delete',
    ]);

    $response = $this->actingAs($user)->deleteJson("/chat/{$conversation->id}");

    $response->assertStatus(200)
        ->assertJson(['success' => true]);

    $this->assertDatabaseMissing('conversations', [
        'id' => $conversation->id,
    ]);
});
