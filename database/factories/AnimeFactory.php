<?php

namespace Database\Factories;

use App\Models\Anime;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnimeFactory extends Factory
{
    protected $model = Anime::class;

    public function definition(): array
    {
        return [
            'mal_id' => fake()->unique()->randomNumber(5, true), 
            
            'title' => fake()->words(3, true), 
            
            'image_url' => fake()->imageUrl(225, 350, 'anime', true),
            
            'episodes' => fake()->numberBetween(12, 24), 
            
            'duration' => fake()->numberBetween(22, 24),
        ];
    }
}
