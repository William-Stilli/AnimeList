<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BadgeFactory extends Factory
{
    protected $model = Badge::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'icon' => fake()->randomElement(['🏆', '🌟', '🔥', '👗', '✨']),
            'color' => fake()->randomElement(['gray', 'blue', 'gold', 'pink', 'purple']),
            'condition_type' => fake()->randomElement(['manual', 'anime_count', 'xp_reached', 'genre_master']),
            'condition_value' => fake()->numberBetween(10, 1000),
            'metadata' => [
                'tier' => fake()->randomElement(['B', 'A', 'S', 'SS']),
                'rarity' => fake()->randomElement(['common', 'rare', 'legendary'])
            ],
            'xp_bonus' => fake()->numberBetween(50, 500),
        ];
    }
}