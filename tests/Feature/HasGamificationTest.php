<?php

use App\Models\Badge;
use App\Models\User;
use App\Models\Anime;
use App\Models\Genre;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('HasGamification : ', function () {

    it('calculates the level correctly based on xp', function () {
        $user = User::factory()->create(['xp' => 0]);
        expect($user->level)->toEqual(1);

        $user->xp = 5;
        expect($user->level)->toEqual(1);

        $user->xp = 20;
        expect($user->level)->toEqual(2);

        $user->xp = 500; 
        expect($user->level)->toEqual(10);
    });

    it('returns the correct level title', function () {
        $user = User::factory()->create(['xp' => 0]);
        expect($user->level_title)->toBe('Novice');

        $user->xp = 500; 
        expect($user->level_title)->toBe('Weeb');

        $user->xp = 3125; 
        expect($user->level_title)->toBe('Otaku');

        $user->xp = 8000; 
        expect($user->level_title)->toBe('Sage');

        $user->xp = 12500; 
        expect($user->level_title)->toBe('Divinité');

        $user->xp = 50000; 
        expect($user->level_title)->toBe('S.T.U. (Souverain Titanesque Universel)');
    });

    it('calculates the next level xp', function () {
        $user = User::factory()->create(['xp' => 20]);
        expect($user->next_level_xp)->toEqual(45);
    });

    it('has a badges relationship', function () {
        $user = User::factory()->create();
        $badge = Badge::factory()->create();
        
        $user->badges()->attach($badge->id, ['unlocked_at' => now()]);

        expect($user->badges)->toHaveCount(1)
            ->and($user->badges->first()->id)->toBe($badge->id)
            ->and($user->badges->first()->pivot->unlocked_at)->not->toBeNull();
    });

    it('gains xp and detects title changes', function () {
        $user = User::factory()->create(['xp' => 499]);

        $changed = $user->gainXp(1);

        expect($changed)->toBeTrue()
            ->and($user->xp)->toBe(500)
            ->and($user->level_title)->toBe('Weeb');

        $changed = $user->gainXp(10);
        expect($changed)->toBeFalse();
    });

    it('unlocks badges via the private method', function () {
        $user = User::factory()->create();
        $badge = Badge::factory()->create(['slug' => 'cosplay']);

        $reflection = new ReflectionMethod($user, 'unlockBadge');
        $reflection->setAccessible(true);
        $reflection->invoke($user, 'cosplay');

        expect($user->badges)->toHaveCount(1)
            ->and($user->badges->first()->slug)->toBe('cosplay');
            
        $reflection->invoke($user, 'cosplay');
        expect($user->badges)->toHaveCount(1);
    });

    it('revokes badges and flashes the session', function () {
        $user = User::factory()->create();
        $badge = Badge::factory()->create(['slug' => 'noob', 'name' => 'Noob']);
        $user->badges()->attach($badge->id);

        Session::start();
        $user->revokeBadge('noob');

        expect($user->badges()->count())->toBe(0)
            ->and(Session::get('error'))->toBe('Badge PERDU : Noob (Condition plus remplie).');
    });

    it('checks achievements and grants genre badges', function () {
        $user = User::factory()->create();
        
        $badge = Badge::factory()->create([
            'condition_type' => 'genre_count',
            'metadata' => ['genre_name' => 'Romance'],
            'condition_value' => 2,
            'name' => 'Romance Expert'
        ]);

        $genre = Genre::factory()->create(['name' => 'Romance']);

        $anime1 = Anime::factory()->create(['title' => 'My Dress-Up Darling', 'duration' => 24]);
        $anime2 = Anime::factory()->create(['title' => 'Horimiya', 'duration' => 24]);
        
        $anime1->genres()->attach($genre);
        $anime2->genres()->attach($genre);

        $user->animes()->attach($anime1, ['status' => 'completed', 'progress' => 12]);
        $user->animes()->attach($anime2, ['status' => 'completed', 'progress' => 13]);

        $reflection = new ReflectionClass(app());
        $property = $reflection->getProperty('isRunningInConsole');
        $property->setAccessible(true);
        $property->setValue(app(), false);
        
        Session::start();
        Session::start();

        $user->checkAchievements();

        expect($user->badges->contains($badge->id))->toBeTrue()
            ->and(Session::get('success')[0])->toBe('Nouveau Badge débloqué : Romance Expert !');
            
        $badge->update(['condition_value' => 5]);
        $user->checkAchievements();
        
        expect($user->fresh()->badges->contains($badge->id))->toBeFalse()
            ->and(Session::get('info')[0])->toBe('Badge perdu : Romance Expert');
    });

    it('checks achievements for No-Life and NGNL specific logic', function () {
        $user = User::factory()->create();
        
        $noLifeBadge = Badge::factory()->create(['name' => 'No-Life', 'condition_type' => 'manual']);
        $ngnlBadge = Badge::factory()->create(['name' => 'No Game No Life', 'condition_type' => 'manual']);

        $anime = Anime::factory()->create([
            'title' => 'Long Anime Series', 
            'duration' => 60000,
            'episodes' => 1
        ]);
        
        $user->animes()->attach($anime, ['status' => 'completed', 'progress' => 1]);

        $reflection = new ReflectionClass(app());
        $property = $reflection->getProperty('isRunningInConsole');
        $property->setAccessible(true);
        $property->setValue(app(), true);

        $user->checkAchievements();
        $user->checkAchievements();

        expect($user->badges->contains($noLifeBadge->id))->toBeTrue()
            ->and($user->badges->contains($ngnlBadge->id))->toBeFalse();

        $ngnlAnime = Anime::factory()->create(['title' => 'No Game No Life']);
        $user->animes()->attach($ngnlAnime, ['status' => 'completed', 'progress' => 12]);

        $user->checkAchievements();
        
        expect($user->fresh()->badges->contains($ngnlBadge->id))->toBeTrue()
            ->and($user->fresh()->badges->contains($noLifeBadge->id))->toBeFalse();
    });

    it('calculates total minutes using episodes fallback when progress is 0', function () {
        $user = User::factory()->create();

        $animeWithEpisodes = Anime::factory()->create([
            'episodes' => 12,
            'duration' => 24
        ]);

        $animeWithoutEpisodes = Anime::factory()->create([
            'episodes' => null,
            'duration' => 24
        ]);

        $user->animes()->attach($animeWithEpisodes, ['status' => 'completed', 'progress' => 0]);
        $user->animes()->attach($animeWithoutEpisodes, ['status' => 'completed', 'progress' => 0]);

        $user->checkAchievements();

        expect($user->animes)->toHaveCount(2);
    });

    it('revokes badges and formats existing string errors in session to array', function () {
        $user = User::factory()->create();
        $badge = Badge::factory()->create(['slug' => 'badge', 'name' => 'Badge']);
        $user->badges()->attach($badge->id);

        Session::start();
        Session::put('error', 'Une erreur précédente sous forme de chaine de caractères.');

        $user->revokeBadge('badge');

        expect($user->badges()->count())->toBe(0)
            ->and(Session::get('error'))->toBe('Badge PERDU : Badge (Condition plus remplie).');
    });
});