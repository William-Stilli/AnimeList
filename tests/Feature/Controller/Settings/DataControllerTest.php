<?php

use App\Models\User;
use App\Models\Anime;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('renders the data settings page', function () {
    $this->get(route('settings.data'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('settings/Data')
        );
});

it('exports user animes to a formatted JSON file', function () {
    $anime = Anime::factory()->create(['mal_id' => 48736, 'title' => 'My Dress-Up Darling']);
    $this->user->animes()->attach($anime->id, ['status' => 'completed', 'score' => 10]);

    $response = $this->get(route('settings.data.export'));

    $response->assertSuccessful();
    $response->assertHeader('Content-Disposition', 'attachment; filename=anime-list-' . date('Y-m-d') . '.json');

    $content = json_decode($response->streamedContent(), true);
    
    expect($content)->toHaveCount(1)
        ->and($content[0]['mal_id'])->toBe(48736)
        ->and($content[0]['status'])->toBe('completed')
        ->and($content[0]['score'])->toBe(10);
});

it('imports animes correctly from a valid JSON file', function () {
    $jsonContent = json_encode([
        [
            'mal_id' => 48736,
            'title' => 'My Dress-Up Darling',
            'image_url' => 'https://marin.jpg',
            'total_episodes' => 12,
            'status' => 'completed',
            'score' => 10,
            'progress' => 12,
            'review' => 'Chef-d\'œuvre absolu.',
            'rank' => 1
        ]
    ]);

    $file = UploadedFile::fake()->createWithContent('backup.json', $jsonContent)->mimeType('application/json');

    $this->post(route('settings.data.import'), [
        'file' => $file
    ])->assertRedirect()
      ->assertSessionHas('success', '1 animés ont été importés avec succès.');

    $this->user->refresh();
    expect($this->user->animes)->toHaveCount(1)
        ->and($this->user->animes->first()->mal_id)->toBe(48736)
        ->and($this->user->animes->first()->pivot->score)->toBe(10)
        ->and($this->user->animes->first()->pivot->rank)->toBe(1);
});

it('fails to import and returns an error if the JSON is corrupted or invalid', function () {
    $badContent = "Ceci n'est pas du JSON valide, c'est un piège !";
    $file = UploadedFile::fake()->createWithContent('bad_backup.json', $badContent)->mimeType('application/json');

    $this->post(route('settings.data.import'), [
        'file' => $file
    ])->assertSessionHasErrors(['file' => 'Le fichier JSON est corrompu ou invalide.']);
    
    expect($this->user->animes)->toHaveCount(0);
});
