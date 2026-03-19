<?php

use App\Models\Anime;
use App\Models\User;
use App\Models\Genre;
use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe("Anime : ", function () {
    it('can be created using the factory', function () {
        $anime = Anime::factory()->create([
            'title' => 'Fate/Zero'
        ]);
    
        expect($anime)->toBeInstanceOf(Anime::class)
            ->and($anime->title)->toBe('Fate/Zero');
    });
    
    it('returns title_english as display_title if it exists', function () {
        $anime = Anime::factory()->make([
            'title' => 'SAO',
            'title_english' => 'Sword Art Online'
        ]);
    
        expect($anime->display_title)->toBe('Sword Art Online')
            ->and($anime->toArray())->toHaveKey('display_title');
    });
    
    it('falls back to title for display_title if title_english is missing', function () {
        $anime = Anime::factory()->make([
            'title' => 'Violet Evergarden',
            'title_english' => null
        ]);
    
        expect($anime->display_title)->toBe('Violet Evergarden');
    });
    
    it('belongs to many users with detailed pivot data', function () {
        $anime = Anime::factory()->create();
        $user = User::factory()->create();
    
        $anime->users()->attach($user->id, [
            'status' => 'completed',
            'score' => 10, 
            'progress' => 12,
            'review' => 'Masterpiece',
            'is_stu' => true,
            'rank' => 1
        ]);
    
        $anime->refresh();
    
        expect($anime->users)->toHaveCount(1)
            ->and($anime->users->first()->pivot->score)->toBe(10)
            ->and($anime->users->first()->pivot->is_stu)->toEqual(1) 
            ->and($anime->users->first()->pivot->review)->toBe('Masterpiece');
    });
    
    it('belongs to many genres', function () {
        $anime = Anime::factory()->create();
        
        $genres = Genre::factory()->count(3)->create();
        
        $anime->genres()->attach($genres);
        $anime->refresh();
    
        expect($anime->genres)->toHaveCount(3)
            ->and($anime->genres->first())->toBeInstanceOf(Genre::class);
    });
    
    it('belongs to many collections', function () {
        $anime = Anime::factory()->create();
        
        $collection = Collection::factory()->create();
        
        $anime->collections()->attach($collection);
        $anime->refresh();
    
        expect($anime->collections)->toHaveCount(1)
            ->and($anime->collections->first())->toBeInstanceOf(Collection::class);
    });
});