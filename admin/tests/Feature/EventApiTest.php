<?php

use App\Models\Event;
use App\Models\User;

test('can retrieve event metadata', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'user_id' => $user->id,
        'name' => 'Test Wedding',
        'date' => now()->addDays(7),
        'gallery_published' => true,
    ]);

    $response = $this->getJson("/api/events/{$event->id}");

    $response->assertStatus(200)
        ->assertJson([
            'id' => $event->id,
            'name' => 'Test Wedding',
            'date' => $event->date->toDateString(),
            'gallery_published' => true,
        ]);
});

test('returns 404 for non-existent event', function () {
    $fakeUuid = (string) \Illuminate\Support\Str::uuid();

    $response = $this->getJson("/api/events/{$fakeUuid}");

    $response->assertStatus(404);
});

test('event metadata is publicly accessible without authentication', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'user_id' => $user->id,
    ]);

    $response = $this->getJson("/api/events/{$event->id}");

    $response->assertStatus(200);
    // No authentication headers were sent, but request succeeded
});
