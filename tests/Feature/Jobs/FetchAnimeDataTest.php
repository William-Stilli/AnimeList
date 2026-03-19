<?php

use App\Jobs\FetchAnimeData;
use App\Models\Anime;
use App\Models\Genre;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('FetchAnimeData Job : ', function () {

    it('returns the correct middleware for rate limiting', function () {
        $anime = Anime::factory()->create();
        $job = new FetchAnimeData($anime);
        
        $middleware = $job->middleware();
        
        // Vérifie qu'on retourne bien le middleware RateLimited ('jikan-api')
        expect($middleware)->toBeArray()
            ->and($middleware[0])->toBeInstanceOf(RateLimited::class);
    });

    it('skips execution and logs a warning if mal_id is missing', function () {
        // On utilise make() pour créer un anime en mémoire sans déclencher l'erreur SQL
        $anime = Anime::factory()->make(['mal_id' => null]);
        
        Log::shouldReceive('info')->once(); // Intercepte le log de démarrage
        Log::shouldReceive('warning')->once()->with("JOB SKIP : Pas de mal_id trouvé.");

        $job = new FetchAnimeData($anime);
        $job->handle();
    });

    it('logs an error if the Jikan API request fails', function () {
        $anime = Anime::factory()->create(['mal_id' => 99999]);

        // On simule un crash 500 de l'API externe
        Http::fake([
            'api.jikan.moe/*' => Http::response([], 500)
        ]);

        Log::shouldReceive('info')->once(); 
        Log::shouldReceive('error')->once()->with("JOB ERREUR API : 500");

        $job = new FetchAnimeData($anime);
        $job->handle();
    });

    it('updates anime, parses complex duration and filters blacklisted genres', function () {
        $anime = Anime::factory()->create(['mal_id' => 87654]);

        Http::fake([
            'api.jikan.moe/*' => Http::response([
                'data' => [
                    'title' => 'My Dress-Up Darling',
                    'title_english' => 'My Dress-Up Darling',
                    'synopsis' => 'absolute perfection.',
                    'episodes' => 12,
                    'type' => 'TV',
                    'source' => 'Manga',
                    'status' => 'Finished Airing',
                    'season' => 'winter',
                    'year' => 2022,
                    'duration' => '1 hr 15 min',
                    'genres' => [
                        ['mal_id' => 1, 'name' => 'Romance'],
                        ['mal_id' => 2, 'name' => 'Slice of Life'],
                        ['mal_id' => 3, 'name' => 'CGDCT']
                    ],
                    'themes' => [
                        ['mal_id' => 4, 'name' => 'Otaku Culture']
                    ],
                    'explicit_genres' => [],
                    'demographics' => [
                        ['mal_id' => 5, 'name' => 'Seinen']
                    ]
                ]
            ], 200)
        ]);

        Log::shouldReceive('info')->twice(); 

        $job = new FetchAnimeData($anime);
        $job->handle();

        $anime->refresh();

        expect($anime->title)->toBe('My Dress-Up Darling') 
            ->and($anime->synopsis)->toContain('perfection')
            ->and($anime->duration)->toBe(75)
            ->and($anime->genres)->toHaveCount(3);

        $genreNames = $anime->genres->pluck('name')->toArray();
        expect($genreNames)->not->toContain('CGDCT')
            ->and($genreNames)->not->toContain('Otaku Culture');
    });

    it('handles duration with only minutes and missing english title', function () {
        $anime = Anime::factory()->create(['mal_id' => 11111]);

        Http::fake([
            'api.jikan.moe/*' => Http::response([
                'data' => [
                    'title' => 'Fate/Zero',
                    'duration' => '24 min', 
                ]
            ], 200)
        ]);

        Log::shouldReceive('info')->twice();

        $job = new FetchAnimeData($anime);
        $job->handle();

        $anime->refresh();

        expect($anime->title)->toBe('Fate/Zero')
            ->and($anime->duration)->toBe(24);
    });

});