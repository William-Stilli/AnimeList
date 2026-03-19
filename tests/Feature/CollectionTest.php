<?php

use App\Models\Anime;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe("Collection : ", function () {
    it('can be created using the factory', function () {
        $collection = Collection::factory()->create([
            'name' => 'Cosplay Masterpieces',
            'description' => 'La collection ultime dédiée à Marin Kitagawa',
        ]);
    
        expect($collection)->toBeInstanceOf(Collection::class)
            ->and($collection->name)->toBe('Cosplay Masterpieces')
            ->and($collection->description)->toBe('La collection ultime dédiée à Marin Kitagawa')
            ->and($collection->user_id)->not->toBeNull();
    });
    
    it('belongs to a user', function () {
        $collection = Collection::factory()->create();
    
        expect($collection->user)->toBeInstanceOf(User::class);
    });
    
    it('belongs to many animes with timestamps', function () {
        $collection = Collection::factory()->create();
        
        $animes = Anime::factory()->count(3)->create();
    
        $collection->animes()->attach($animes->pluck('id'));
    
        $collection->refresh();
    
        expect($collection->animes)->toHaveCount(3)
            ->and($collection->animes->first())->toBeInstanceOf(Anime::class)
            ->and($collection->animes->first()->pivot->created_at)->not->toBeNull();
    });
});