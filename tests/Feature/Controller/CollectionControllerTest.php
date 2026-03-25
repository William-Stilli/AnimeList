<?php

use App\Models\User;
use App\Models\Anime;
use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('creates a new collection successfully', function () {
    $this->post(route('collections.store'), [
        'name' => 'Les chefs-d\'œuvre absolus',
        'description' => 'La liste où trône la GOAT Marin Kitagawa.'
    ])->assertRedirect()
      ->assertSessionHas('success', 'Collection créée avec succès !');

    expect($this->user->collections)->toHaveCount(1)
        ->and($this->user->collections->first()->name)->toBe('Les chefs-d\'œuvre absolus');
});

it('fails to create a collection if name is missing', function () {
    $this->post(route('collections.store'), [
        'description' => 'Une collection sans nom'
    ])->assertSessionHasErrors('name');
});


it('toggles an anime inside a collection', function () {
    $collection = Collection::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Top Tiers'
    ]);
    $anime = Anime::factory()->create(['title' => 'My Dress-Up Darling']);

    $this->post(route('collections.toggle', $collection), [
        'anime_id' => $anime->id
    ])->assertRedirect()
      ->assertSessionHas('success', 'Playlist mise à jour');

    expect($collection->animes)->toHaveCount(1);

    $this->post(route('collections.toggle', $collection), [
        'anime_id' => $anime->id
    ])->assertRedirect();

    $collection->refresh();
    expect($collection->animes)->toHaveCount(0);
});

it('aborts with 403 when trying to toggle anime in someone else collection', function () {
    $otherUser = User::factory()->create();
    $otherCollection = Collection::factory()->create(['user_id' => $otherUser->id]);
    $anime = Anime::factory()->create();

    $this->post(route('collections.toggle', $otherCollection), [
        'anime_id' => $anime->id
    ])->assertStatus(403);
});


it('deletes a collection successfully', function () {
    $collection = Collection::factory()->create(['user_id' => $this->user->id]);

    $this->delete(route('collections.destroy', $collection))
        ->assertRedirect()
        ->assertSessionHas('success', 'Collection supprimée.');

    expect(Collection::where('id', $collection->id)->exists())->toBeFalse();
});

it('aborts with 403 when trying to delete someone else collection', function () {
    $otherUser = User::factory()->create();
    $otherCollection = Collection::factory()->create(['user_id' => $otherUser->id]);

    $this->delete(route('collections.destroy', $otherCollection))
        ->assertStatus(403);

    expect(Collection::where('id', $otherCollection->id)->exists())->toBeTrue();
});