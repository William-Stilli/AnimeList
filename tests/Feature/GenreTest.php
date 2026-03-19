<?php

use App\Models\Anime;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe("Genres : ", function () {
    it('can be created using the factory and fillable attributes', function () {
        $genre = Genre::factory()->create([
            'name' => 'Romance', 
            'mal_id' => 87654,
        ]);
    
        expect($genre)->toBeInstanceOf(Genre::class)
            ->and($genre->name)->toBe('Romance')
            ->and($genre->mal_id)->toBe(87654);
    });
    
    it('belongs to many animes', function () {
        $genre = Genre::factory()->create();
        
        $animes = Anime::factory()->count(3)->create();
        
        $genre->animes()->attach($animes->pluck('id'));
        
        $genre->refresh();
    
        expect($genre->animes)->toHaveCount(3)
            ->and($genre->animes->first())->toBeInstanceOf(Anime::class);
    });
});
