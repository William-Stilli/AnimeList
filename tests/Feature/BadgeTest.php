<?php

use App\Models\Badge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe("Badge : ", function () {
    it('can be created using the factory and casts metadata to an array', function () {
        $badge = Badge::factory()->create([
            'name' => 'Cosplayer',
            'metadata' => ['tier' => 'SS', 'event' => 'School Festival'], 
        ]);
    
        expect($badge)->toBeInstanceOf(Badge::class)
            ->and($badge->name)->toBe('Cosplayer')
            ->and($badge->metadata)->toBeArray()
            ->and($badge->metadata['tier'])->toBe('SS');
    });
    
    it('belongs to many users with unlocked_at pivot data', function () {
        $badge = Badge::factory()->create();
        $user = User::factory()->create();
    
        $badge->users()->attach($user->id, [
            'unlocked_at' => now(),
        ]);
    
        $badge->refresh();
    
        expect($badge->users)->toHaveCount(1)
            ->and($badge->users->first()->pivot->unlocked_at)->not->toBeNull();
    });
});