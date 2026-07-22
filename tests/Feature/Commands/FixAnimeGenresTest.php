<?php

use App\Models\Anime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

describe("FixAnimeGenres : ", function () {
    it('fixes missing genres for animes in the database', function () {
        $anime = Anime::factory()->create([
            'mal_id' => 48736,
            'title' => 'My Dress-Up Darling'
        ]);
        
        expect($anime->genres)->toHaveCount(0);
    
        Http::fake([
            'api.tenrai.org/*' => Http::response([
                'data' => [
                    'genres' => [
                        ['mal_id' => 1, 'name' => 'Romance'],
                        ['mal_id' => 2, 'name' => 'Slice of Life'],
                    ]
                ]
            ], 200)
        ]);
    
        $this->artisan('anime:fix-genres')
            ->assertSuccessful();
    
        $anime->refresh();
        
        expect($anime->genres)->toHaveCount(2)
            ->and($anime->genres->pluck('name')->toArray())->toContain('Romance', 'Slice of Life');
    });

    it('stops the command if DB is empty', function () {

        $this->artisan('anime:fix-genres')
            ->expectsOutput("Ta base de données est vide, ajoute des animés d'abord !")
            ->assertSuccessful();
    });

    it('ignore the animes which does not have mal_id', function () {

        Artisan::command('badges:reset-all', fn() => true);

        Anime::factory()->create(['mal_id' => '']);

        $this->artisan('anime:fix-genres')->assertSuccessful();
    });

    it('ignore every genre inside blacklist', function () {
        Artisan::command('badges:reset-all', fn() => true);
        $anime = Anime::factory()->create(['mal_id' => 11757]);

        Http::fake([
            '*' => Http::response(['data' => [
                'genres' => [['mal_id' => 99, 'name' => 'Organized Crime']]
            ]])
        ]);

        $this->artisan('anime:fix-genres')->assertSuccessful();
        
        expect($anime->fresh()->genres)->toHaveCount(0);
    });

    it('handles crashs from API without breaking the application', function () {
        Artisan::command('badges:reset-all', fn() => true);
        Anime::factory()->create(['mal_id' => 48736]);

        Http::fake(fn() => throw new \Exception("Jikan API is down"));

        $this->artisan('anime:fix-genres')
            ->expectsOutputToContain("Erreur pour l'animé ID 48736 : Jikan API is down")
            ->assertSuccessful();
    });
});