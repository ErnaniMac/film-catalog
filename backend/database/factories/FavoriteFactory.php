<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Favorite>
 */
class FavoriteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'tmdb_id' => fake()->unique()->numberBetween(1, 100000),
            'title' => fake()->sentence(3),
            'overview' => fake()->paragraph(),
            'poster' => 'https://image.tmdb.org/t/p/w500/' . fake()->lexify('???????.jpg'),
            'genre_ids' => fake()->randomElements([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 3),
        ];
    }
}
