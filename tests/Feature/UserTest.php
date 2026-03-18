<?php

use App\Models\Anime;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe("Users: ", function () {
    it('is able to create a user', function () {
        $user = User::factory()->create([
            'name' => 'Stu',
            'email' => 'stu@test.com',
        ]);

        expect($user->name)->toBe('Stu');
        expect($user->email)->toBe('stu@test.com');
    });

    it('allows a user to have animes', function () {
        $user = User::factory()->create();

        $animes = Anime::factory()->count(10)->create();

        $user->animes()->attach($animes);

        $user->refresh();

        expect($user->animes)->toHaveCount(10);
        expect($user->animes->first())->toBeInstanceOf(Anime::class);
    });

    it('allows a user to have collections', function () {
        $user = User::factory()->create();

        Collection::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        $user->load('collections');

        expect($user->collections)->toHaveCount(2);
        expect($user->collections->first())->toBeInstanceOf(Collection::class);
    });
});