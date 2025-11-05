<?php

use App\Models\Event;
use App\Models\Guest;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('can upload photo with valid authentication', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $event = Event::factory()->create([
        'user_id' => $user->id,
        'date' => now(), // Event is happening now
    ]);
    $guest = Guest::factory()->create(['event_id' => $event->id]);
    $token = $guest->createToken('test-token')->plainTextToken;

    $file = UploadedFile::fake()->image('photo.jpg', 1920, 1080)->size(1024); // 1KB file

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->postJson("/api/events/{$event->id}/photos", [
        'file' => $file,
        'taken_at' => now()->format('Y-m-d H:i:s'),
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'quota_remaining' => 14, // Started with 15, used 1
        ]);

    // Verify photo was created
    $this->assertDatabaseHas('photos', [
        'event_id' => $event->id,
        'guest_id' => $guest->id,
    ]);

    $photo = Photo::where('event_id', $event->id)->where('guest_id', $guest->id)->first();
    expect($photo)->not->toBeNull();
    expect($photo->filename)->toContain('.jpg');

    // Verify file was stored
    Storage::disk('local')->assertExists("photos/{$photo->filename}");
});

test('requires authentication for photo upload', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['user_id' => $user->id]);

    $file = UploadedFile::fake()->image('photo.jpg');

    $response = $this->postJson("/api/events/{$event->id}/photos", [
        'file' => $file,
        'taken_at' => now()->format('Y-m-d H:i:s'),
    ]);

    $response->assertStatus(401);
});

test('validates file is required', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['user_id' => $user->id]);
    $guest = Guest::factory()->create(['event_id' => $event->id]);
    $token = $guest->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->postJson("/api/events/{$event->id}/photos", [
        'taken_at' => now()->format('Y-m-d H:i:s'),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['file']);
});

test('validates taken_at is required', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['user_id' => $user->id]);
    $guest = Guest::factory()->create(['event_id' => $event->id]);
    $token = $guest->createToken('test-token')->plainTextToken;

    $file = UploadedFile::fake()->image('photo.jpg');

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->postJson("/api/events/{$event->id}/photos", [
        'file' => $file,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['taken_at']);
});

test('validates file is jpeg only', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['user_id' => $user->id]);
    $guest = Guest::factory()->create(['event_id' => $event->id]);
    $token = $guest->createToken('test-token')->plainTextToken;

    $file = UploadedFile::fake()->image('photo.png'); // PNG instead of JPEG

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->postJson("/api/events/{$event->id}/photos", [
        'file' => $file,
        'taken_at' => now()->format('Y-m-d H:i:s'),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['file']);
});

test('validates file size limit', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['user_id' => $user->id]);
    $guest = Guest::factory()->create(['event_id' => $event->id]);
    $token = $guest->createToken('test-token')->plainTextToken;

    $file = UploadedFile::fake()->image('photo.jpg')->size(11000); // 11MB > 10MB limit

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->postJson("/api/events/{$event->id}/photos", [
        'file' => $file,
        'taken_at' => now()->format('Y-m-d H:i:s'),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['file']);
});

test('validates image dimensions', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create(['user_id' => $user->id]);
    $guest = Guest::factory()->create(['event_id' => $event->id]);
    $token = $guest->createToken('test-token')->plainTextToken;

    $file = UploadedFile::fake()->image('photo.jpg', 3000, 2000); // Too large dimensions

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->postJson("/api/events/{$event->id}/photos", [
        'file' => $file,
        'taken_at' => now()->format('Y-m-d H:i:s'),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['file']);
});

test('validates taken_at is within upload window', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'user_id' => $user->id,
        'date' => now()->addDays(1), // Event tomorrow
    ]);
    $guest = Guest::factory()->create(['event_id' => $event->id]);
    $token = $guest->createToken('test-token')->plainTextToken;

    $file = UploadedFile::fake()->image('photo.jpg');

    // taken_at is way before the event
    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->postJson("/api/events/{$event->id}/photos", [
        'file' => $file,
        'taken_at' => now()->subDays(2)->format('Y-m-d H:i:s'),
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['taken_at']);
});

test('rejects upload when upload window is closed', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'user_id' => $user->id,
        'date' => now()->addDays(2), // Event in 2 days
    ]);
    $guest = Guest::factory()->create(['event_id' => $event->id]);
    $token = $guest->createToken('test-token')->plainTextToken;

    $file = UploadedFile::fake()->image('photo.jpg');

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->postJson("/api/events/{$event->id}/photos", [
        'file' => $file,
        'taken_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
    ]);

    $response->assertStatus(403)
        ->assertSee('Upload window is closed');
});

test('rejects upload when quota is exceeded', function () {
    $user = User::factory()->create();
    $event = Event::factory()->create([
        'user_id' => $user->id,
        'date' => now(),
    ]);
    $guest = Guest::factory()->create(['event_id' => $event->id]);

    // Create 15 photos to reach quota limit
    Photo::factory()->count(15)->create([
        'event_id' => $event->id,
        'guest_id' => $guest->id,
    ]);

    $file = UploadedFile::fake()->image('photo.jpg');
    $token = $guest->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->postJson("/api/events/{$event->id}/photos", [
        'file' => $file,
        'taken_at' => now()->format('Y-m-d H:i:s'),
    ]);

    $response->assertStatus(429)
        ->assertSee('Upload quota exceeded');
});

test('correctly calculates remaining quota after upload', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $event = Event::factory()->create([
        'user_id' => $user->id,
        'date' => now(),
    ]);
    $guest = Guest::factory()->create(['event_id' => $event->id]);
    $token = $guest->createToken('test-token')->plainTextToken;

    // Upload 3 photos first
    for ($i = 0; $i < 3; $i++) {
        $file = UploadedFile::fake()->image('photo.jpg', 1000, 1000)->size(1024);
        $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson("/api/events/{$event->id}/photos", [
            'file' => $file,
            'taken_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    // Upload 4th photo
    $file = UploadedFile::fake()->image('photo.jpg', 1000, 1000)->size(1024);
    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->postJson("/api/events/{$event->id}/photos", [
        'file' => $file,
        'taken_at' => now()->format('Y-m-d H:i:s'),
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'quota_remaining' => 11, // 15 - 4 = 11
        ]);
});
