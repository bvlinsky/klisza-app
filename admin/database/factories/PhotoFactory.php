<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Photo>
 */
class PhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => \App\Models\Event::factory(),
            'guest_id' => \App\Models\Guest::factory(),
            'filename' => fake()->uuid().'.jpg',
            'taken_at' => fake()->optional()->dateTime(),
            'created_at' => fake()->dateTime(),
        ];
    }
}
