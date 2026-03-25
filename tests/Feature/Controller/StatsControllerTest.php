<?php

use App\Models\User;
use App\Models\Anime;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('renders the stats page gracefully with empty data', function () {
    $this->get(route('stats'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Stats')
            ->has('statusData', 0)
            ->has('scoreData', 0)
            ->has('genreData', 0)
        );
});

it('aggregates status, score, and cleans franchises with complex regex', function () {
    $romance = Genre::factory()->create(['name' => 'Romance']);
    $action = Genre::factory()->create(['name' => 'Action']);
    $a1 = Anime::factory()->create(['title_english' => 'My Dress-Up Darling The Final Season']);
    $a2 = Anime::factory()->create(['title' => 'My Dress-Up Darling - Perfect Cosplay']);        
    $a3 = Anime::factory()->create(['title' => 'My Dress-Up Darling II']);                       
    
    foreach ([$a1, $a2, $a3] as $anime) {
        $anime->genres()->attach($romance->id);
        $this->user->animes()->attach($anime->id, ['status' => 'completed', 'score' => 10]);
    }

    $a4 = Anime::factory()->create(['title' => 'Boring Anime']);
    $a4->genres()->attach($action->id);
    $this->user->animes()->attach($a4->id, ['status' => 'watching', 'score' => 0]); 

    $a5 = Anime::factory()->create(['title' => 'Okay Anime']);
    $this->user->animes()->attach($a5->id, ['status' => 'completed', 'score' => 8]);

    $this->get(route('stats'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Stats')

            ->has('statusData', 2)
            ->where('statusData.0.status', 'completed')
            ->where('statusData.0.total', 4)
            ->where('statusData.1.status', 'watching')
            ->where('statusData.1.total', 1)
         
            ->has('scoreData', 2)
            ->where('scoreData.0.score', 8)
            ->where('scoreData.0.total', 1)
            ->where('scoreData.1.score', 10)
            ->where('scoreData.1.total', 3) 

            
            ->has('genreData', 1)        
            ->where('genreData.0.name', 'Romance')
            ->where('genreData.0.total', 1) 
        );
});

it('renders the ranking page with user animes properly ordered', function () {
    $anime = Anime::factory()->create(['title' => 'My Dress-Up Darling']);
    $this->user->animes()->attach($anime->id, ['status' => 'completed', 'rank' => 1]);

    $this->get(route('anime.ranking'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Ranking')
            ->has('animes', 1)
            ->where('animes.0.title', 'My Dress-Up Darling')
            ->where('animes.0.pivot.rank', 1)
        );
});