<?php

namespace Database\Factories;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;

class GenreFactory extends Factory
{
    protected $model = Genre::class;

    public function definition(): array
    {
        return [
            'mal_id' => fake()->unique()->randomNumber(5, true), 
            
            'name' => fake()->unique()->word(), 
        ];
    }
}