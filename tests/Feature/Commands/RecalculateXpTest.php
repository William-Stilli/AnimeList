<?php

use App\Models\Anime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

describe("RecalculateXp: ", function () {
    it("recalculates xp and levels up the user correctly", function () {
        $user = User::factory()->create([
            'xp' => 0,
        ]);

        $anime1 = Anime::factory()->create(['title' => 'Fate/Zero', 'episodes' => 25]);
        $anime2 = Anime::factory()->create(['title' => '86', 'episodes' => 23]);
        $anime3 = Anime::factory()->create(['title' => 'Black Clover', 'episodes' => 170]);

        $user->animes()->attach($anime1, ['status' => 'completed', 'progress' => 25]);
        $user->animes()->attach($anime2, ['status' => 'completed', 'progress' => 23]);
        $user->animes()->attach($anime3, ['status' => 'completed', 'progress' => 170]);

        $this->artisan('app:recalculate-xp')->assertSuccessful();

        $user->refresh();

        expect($user->xp)->toBeGreaterThan(0)->and($user->level)->toBeGreaterThan(1);
    });

    it('recalculate just for a precise user when specified in argument', function () {
        $user1 = User::factory()->create(['name' => 'Kirito']);
        $user2 = User::factory()->create(['name' => 'Asuna']);

        $this->artisan('app:recalculate-xp', ['user_id' => $user1->id])
            ->expectsOutputToContain("User [Kirito]")
            ->doesntExpectOutputToContain("User [Asuna]")
            ->assertSuccessful();
    });

    it('catches errors when badges are verified', function () {
        $user = User::factory()->create();

        Schema::dropIfExists('badges');

        $this->artisan('app:recalculate-xp', ['user_id' => $user->id])
            ->expectsOutputToContain("Erreur lors du check des badges")
            ->assertSuccessful();
    });
});