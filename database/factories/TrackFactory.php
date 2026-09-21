<?php

namespace Database\Factories;

use App\Models\Track;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Track>
 */
class TrackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'artist_name' => fake()->name(),
            'genre' => fake()->randomElement([
                'Afrobeats',
                'Hip Hop',
                'R&B',
                'Pop',
                'Gospel',
                'Reggae',
            ]),
            'duration' => fake()->numberBetween(120, 360),
            'release_date' => fake()->date(),
            'publication_status' => fake()->randomElement([
                'draft',
                'published',
            ]),
        ];
    }
}
