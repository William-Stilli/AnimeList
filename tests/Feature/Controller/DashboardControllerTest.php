<?php

use App\Models\User;
use App\Models\Anime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('renders the dashboard with empty stats for a brand new user', function () {
    $this->get(route('anime.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('AnimeDashboard')
            ->has('watching', 0)
            ->where('stats.episodes', 0)
            ->where('stats.time_spent', '0j 0h') 
            ->where('stats.completed_count', 0)
            ->has('badges', 0)
            ->where('stuAnime', null)
        );
});

it('calculates complex stats, aggregates time spent, and limits the watching list', function () {

    $stuAnime = Anime::factory()->create(['title' => 'My Dress-Up Darling', 'duration' => 24]);
    $this->user->animes()->attach($stuAnime->id, [
        'status' => 'completed',
        'progress' => 12,    
        'is_stu' => true,
    ]);

    $completedAnime = Anime::factory()->create(['duration' => 30]);
    $this->user->animes()->attach($completedAnime->id, [
        'status' => 'completed',
        'progress' => 20,   
        'is_stu' => false
    ]);

    for ($i = 1; $i <= 6; $i++) {
        $anime = Anime::factory()->create(['duration' => 20]);
        $this->user->animes()->attach($anime->id, [
            'status' => 'watching',
            'progress' => 5,  
            'is_stu' => false,
            'updated_at' => now()->addMinutes($i) 
        ]);
    }

    $this->get(route('anime.dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('AnimeDashboard')
            ->has('watching', 5) 
            ->where('stats.episodes', 62)
            ->where('stats.time_spent', '1j 0h')
            ->where('stats.completed_count', 2)
            ->has('stuAnime')
            ->where('stuAnime.title', 'My Dress-Up Darling')
        );
});