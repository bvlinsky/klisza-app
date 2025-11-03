<?php

use App\Models\Event;
use App\Models\Guest;
use App\Models\User;

test('can authenticate guest for event', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson("/api/events/{$event->id}/auth", [
        'name' => 'John Doe',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'quota_remaining',
        ]);

    // Verify guest was created
    $this->assertDatabaseHas('guests', [
        'event_id' => $event->id,
        'name' => 'John Doe',
    ]);

    // Verify token is valid
    $guest = Guest::where('event_id', $event->id)->where('name', 'John Doe')->first();
    expect($guest->tokens)->toHaveCount(1);
});

test('reuses existing guest when authenticating with same name', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['user_id' => $user->id]);

    // First authentication
    $this->postJson("/api/events/{$event->id}/auth", ['name' => 'John Doe']);

    $this->assertDatabaseHas('guests', [
        'event_id' => $event->id,
        'name' => 'John Doe',
    ]);
});

test('returns correct quota remaining for new guest', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson("/api/events/{$event->id}/auth", [
        'name' => 'John Doe',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'quota_remaining' => 15, // New guest has full quota
        ]);
});

test('validates guest name is required', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson("/api/events/{$event->id}/auth", []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('validates guest name minimum length', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson("/api/events/{$event->id}/auth", [
        'name' => 'A', // Too short
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('validates guest name maximum length', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['user_id' => $user->id]);

    $response = $this->postJson("/api/events/{$event->id}/auth", [
        'name' => str_repeat('A', 51), // Too long
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});
