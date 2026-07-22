<?php

use App\Jobs\FetchAnimeData;
use App\Models\Anime;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['xp' => 1000]);
    $this->actingAs($this->user);
});

describe("AnimeController : ", function () {
    it('show anime details from an anime inside the DB', function () {
        $anime = Anime::factory()->create([
            'mal_id' => 48736,
            'title' => 'My Dress-Up Darling'
        ]);

        $this->user->animes()->attach($anime->id, ['status' => 'completed']);

        $this->get(route('animes.show', $anime->mal_id))
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AnimeDetails')
                ->has('anime')
                ->where('anime.mal_id', 48736)
                ->where('anime.title', 'My Dress-Up Darling')
            );
    });

    it('fetch Jikan API and show details if anime is not in DB', function () {
        Http::fake([
            'https://api.tenrai.org/v1/anime/48736' => Http::response([
                'data' => [
                    'mal_id' => 48736,
                    'title' => 'My Dress-Up Darling',
                    'images' => ['jpg' => ['large_image_url' => 'marin.jpg']],
                    'episodes' => 12,
                    'score' => 10,
                    'type' => 'TV',
                    'duration' => '23 min per ep',
                ]
            ], 200)
        ]);

        $this->get(route('animes.show', 48736))
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AnimeDetails')
                ->has('anime')
                ->where('anime.mal_id', 48736)
                ->where('anime.duration', 23)
                ->where('anime.is_saved', false)
            );
    });


    it('add new anime to list and launch synchronisation job', function () {
        Queue::fake();

        $payload = [
            'mal_id' => 11757,
            'title' => 'Sword Art Online',
            'image_url' => 'sao.jpg',
            'episodes' => 25,
        ];

        $this->post(route('animes.store'), $payload)
            ->assertRedirect()
            ->assertSessionHas('success', 'Animé ajouté ! Les détails arrivent...');

        expect($this->user->animes)->toHaveCount(1)
            ->and($this->user->animes->first()->pivot->status)->toBe('plan_to_watch');

        Queue::assertPushed(FetchAnimeData::class);
    });

    it('unallow to add an anime already in the list', function () {
        $anime = Anime::factory()->create(['mal_id' => 11757]);
        $this->user->animes()->attach($anime->id);

        $this->post(route('animes.store'), [
            'mal_id' => 11757,
            'title' => 'Sword Art Online',
            'image_url' => 'sao.jpg'
        ])
            ->assertRedirect()
            ->assertSessionHas('warning', 'Cet animé est déjà dans ta liste !');
    });


    it('updates progression and give XP', function () {
        $anime = Anime::factory()->create(['episodes' => 12]);
        $this->user->animes()->attach($anime->id, ['progress' => 0, 'status' => 'watching']);
        $initialXp = $this->user->xp;

        $this->put(route("animes.update", $anime->id), [
            'progress' => 2,
        ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->user->refresh();

        expect($this->user->animes->first()->pivot->progress)->toBe(2)
            ->and($this->user->xp)->toBe($initialXp + 20);
    });

    it('auto complete anime and give bonus XP if anime is completed', function () {
        $anime = Anime::factory()->create(['episodes' => 12]);
        $this->user->animes()->attach($anime->id, ['progress' => 11, 'status' => 'watching']);
        $initialXp = $this->user->xp;

        $this->put(route("animes.update", $anime->id), [
            'progress' => 12,
        ])->assertRedirect();

        $this->user->refresh();

        expect($this->user->animes->first()->pivot->status)->toBe('completed')
            ->and($this->user->xp)->toBe($initialXp + 110);
    });


    it('delete an anime from the list and delete XP related to the anime', function () {
        $anime = Anime::factory()->create();
        
        $this->user->animes()->attach($anime->id, ['progress' => 5, 'status' => 'completed']);
        
        $initialXp = 1000;
        $this->user->update(['xp' => $initialXp]);

        $this->delete(route("anime.destroy", $anime->id))
            ->assertSuccessful()
            ->assertJson(['message' => 'Animé supprimé. Tu as perdu 150 XP.']);

        $this->user->refresh();

        expect($this->user->animes)->toHaveCount(0)
            ->and($this->user->xp)->toBe($initialXp - 150);
    });


    it('define an anime as STU', function () {
        $anime1 = Anime::factory()->create(['title' => 'Anime Basique']);
        $anime2 = Anime::factory()->create(['title' => 'My Dress-Up Darling']);

        $this->user->animes()->attach($anime1->id, ['is_stu' => true]);
        $this->user->animes()->attach($anime2->id, ['is_stu' => false]);

        $this->post(route("animes.toggleStu", $anime2->mal_id))
            ->assertRedirect()
            ->assertSessionHas('success', "Parfait, My Dress-Up Darling est maintenant ton S.T.U. !");

        expect($this->user->animes()->where('animes.id', $anime1->id)->first()->pivot->is_stu)->toBe(0)
            ->and($this->user->animes()->where('animes.id', $anime2->id)->first()->pivot->is_stu)->toBe(1);
    });

    it('define an anime as STU and reset the old stu anime', function () {
        $anime1 = Anime::factory()->create(['title' => 'My Dress-Up Darling']);

        $this->user->animes()->attach($anime1->id, ['is_stu' => true]);

        $this->post(route("animes.toggleStu", $anime1->mal_id))
            ->assertRedirect()
            ->assertSessionHas('success', "My Dress-Up Darling a abdiqué. Le place est libre.");

        expect($this->user->animes()->where('animes.id', $anime1->id)->first()->pivot->is_stu)->toBe(0);
    });

    it('aborts with 404 if anime is not found in database and Jikan API fails', function () {
        Http::fake([
            'https://api.tenrai.org/v1/anime/*' => Http::response(null, 404)
        ]);

        $this->get(route('animes.show', 999999))
            ->assertStatus(404);
    });

    it('returns the user anime list sorted correctly on index', function () {
        $anime = Anime::factory()->create();
        $this->user->animes()->attach($anime->id, ['status' => 'watching']);

        $this->get(route('animes.index'))
            ->assertSuccessful()
            ->assertJsonFragment(['status' => 'watching']);
    });

    it('handles custom image upload and reset', function () {
        Storage::fake('public');
        $anime = Anime::factory()->create();
        $this->user->animes()->attach($anime->id);

        $file = UploadedFile::fake()->image('marin_custom.jpg');

        $this->put(route('animes.update', $anime->id), [
            'custom_image' => $file
        ])->assertRedirect();

        Storage::disk('public')->assertExists('covers/' . $file->hashName());

        
        $this->put(route('animes.update', $anime->id), [
            'reset_image' => true
        ])->assertRedirect();

        expect($this->user->animes->first()->pivot->custom_image_path)->toBeNull();
    });

    it('decreases xp when changing status from completed to watching', function () {
        $anime = Anime::factory()->create();
        $this->user->animes()->attach($anime->id, ['status' => 'completed', 'progress' => 12]);
        $this->user->update(['xp' => 500]);

        $this->put(route('animes.update', $anime->id), [
            'status' => 'watching'
        ])->assertRedirect();

        $this->user->refresh();
        expect($this->user->xp)->toBe(400);
    });

    it('updates the pantheon rank and clears the previous anime at this rank', function () {
        $anime1 = Anime::factory()->create(['title' => 'Fate/Zero']);
        $anime2 = Anime::factory()->create(['title' => 'Fate/stay night: Heaven\'s Feel']);

        $this->user->animes()->attach($anime1->id, ['pantheon_rank' => 1]);
        $this->user->animes()->attach($anime2->id, ['pantheon_rank' => null]);

        $this->post(route('animes.pantheon', $anime2), [
            'rank' => 1
        ])->assertRedirect();

        expect($this->user->animes()->withPivot('pantheon_rank')->where('animes.id', $anime1->id)->first()->pivot->pantheon_rank)->toBeNull()
            ->and($this->user->animes()->withPivot('pantheon_rank')->where('animes.id', $anime2->id)->first()->pivot->pantheon_rank)->toBe(1);
    });

    it('displays the public library with radar chart data', function () {
        $anime = Anime::factory()->create(['title' => 'Sword Art Online']);
        $genre = Genre::factory()->create(['name' => 'Action']);
        $anime->genres()->attach($genre->id);
        
        $this->user->animes()->attach($anime->id, ['status' => 'completed']);

        $this->get(route('user.list', $this->user))
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('PublicLibrary')
                ->has('radarData')
                ->has('targetUser')
            );
    });

    it('displays the community page with other users', function () {
        User::factory()->create()->count(2);

        $this->get(route('community.index'))
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Community')
                ->has('users')
            );
    });

    it('searches for animes using Jikan API and returns results', function () {
        Http::fake([
            'https://api.tenrai.org/v1/anime*' => Http::response([
                'data' => [
                    ['mal_id' => 11757, 'title' => 'Sword Art Online']
                ]
            ], 200)
        ]);

        $this->get(route('anime.search', ['search' => 'Sword Art']))
            ->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('AnimeSearch')
                ->has('animes')
                ->where('animes.0.mal_id', 11757)
            );
    });

    it('returns 404 when trying to destroy an anime not in user list', function () {
        $anime = Anime::factory()->create(); 

        $this->delete(route('anime.destroy', $anime->id))
            ->assertStatus(404)
            ->assertJson(['message' => 'Animé introuvable dans ta liste']);
    });

    it('stores a new anime with null episodes if episodes data is missing', function () {
        Queue::fake(); 
    
        $this->post(route('animes.store'), [
            'mal_id' => 99999,
            'title' => 'Anime Inconnu',
            'image_url' => 'https://marin-kitagawa.jpg',
        ])->assertRedirect()
          ->assertSessionHas('success');
    
        expect(Anime::where('mal_id', 99999)->first()->episodes)->toBeNull();
    });

    it('returns an error when trying to update an anime not present in user list', function () {
        $anime = Anime::factory()->create();

        $this->put(route('animes.update', $anime->id), [
            'progress' => 5
        ])->assertRedirect()
          ->assertSessionHas('error', 'Cet animé n\'est pas dans ta liste.');
    });

    it('updates pantheon rank via the main update method without breaking other fields', function () {
        $anime = Anime::factory()->create();
        $this->user->animes()->attach($anime->id, ['pantheon_rank' => null, 'score' => 8]);
    
        $this->put(route('animes.update', $anime->id), [
            'pantheon_rank' => 3
        ])->assertRedirect();
    
        $pivot = $this->user->animes()->withPivot(['pantheon_rank', 'score'])->where('animes.id', $anime->id)->first()->pivot;
        
        expect($pivot->pantheon_rank)->toBe(3)
            ->and($pivot->score)->toBe(8);
    });
    
    it('allows partial updates for score and status independently', function () {
        $anime = Anime::factory()->create();
        $this->user->animes()->attach($anime->id, ['score' => 0, 'status' => 'plan_to_watch']);
    
        $this->put(route('animes.update', $anime->id), [
            'score' => 10 
        ])->assertRedirect();
    
        expect($this->user->animes->first()->pivot->score)->toBe(10)
            ->and($this->user->animes->first()->pivot->status)->toBe('plan_to_watch');
    });

    it('forces status to completed and caps progress when progress exceeds max episodes', function () {
        $anime = Anime::factory()->create(['episodes' => 12]);
        $this->user->animes()->attach($anime->id, ['progress' => 10, 'status' => 'watching']);
    
        $this->put(route('animes.update', $anime->id), [
            'progress' => 15
        ])->assertRedirect();
    
        $pivot = $this->user->animes->first()->pivot;
        expect($pivot->status)->toBe('completed')
            ->and($pivot->progress)->toBe(12); 
    });
    
    it('forces progress to max episodes when status is manually set to completed', function () {
        $anime = Anime::factory()->create(['episodes' => 24]);
        $this->user->animes()->attach($anime->id, ['progress' => 5, 'status' => 'watching']);
    
        $this->put(route('animes.update', $anime->id), [
            'status' => 'completed'
        ])->assertRedirect();
    
        expect($this->user->animes->first()->pivot->progress)->toBe(24);
    });

    it('resets the custom image when reset_image is explicitly requested as a boolean', function () {
        $anime = Anime::factory()->create();
        $this->user->animes()->attach($anime->id, ['custom_image_path' => 'old_cover.jpg']);
    
        $this->put(route('animes.update', $anime->id), [
            'reset_image' => 1 
        ])->assertRedirect()
          ->assertSessionHas('success');
    
        expect($this->user->animes->first()->pivot->custom_image_path)->toBeNull();
    });

    it('builds a temporary anime with all array keys from Jikan API when not in database', function () {
        Http::fake([
            'https://api.tenrai.org/v1/anime/88888' => Http::response([
                'data' => [
                    'mal_id' => 88888,
                    'title' => 'Test Anime',
                    'title_english' => 'Test Anime English',
                    'images' => ['jpg' => ['large_image_url' => 'image.jpg']],
                    'episodes' => 24,
                    'score' => 8.5,
                    'type' => 'TV',
                    'year' => 2026,
                    'synopsis' => 'Test synopsis'
                ]
            ], 200)
        ]);
    
        $this->get(route('animes.show', 88888))->assertSuccessful();
    });

    it('clears the old pantheon rank when using the main update endpoint', function () {
        $anime1 = Anime::factory()->create(['title' => 'Fate/Zero']);
        $anime2 = Anime::factory()->create(['title' => 'Fate/stay night: Heaven\'s Feel']);
    
        $this->user->animes()->attach($anime1->id, ['pantheon_rank' => 1]);
        $this->user->animes()->attach($anime2->id, ['pantheon_rank' => null]);
    
        $this->put(route('animes.update', $anime2->id), [
            'pantheon_rank' => 1
        ])->assertRedirect();
    
        expect($this->user->animes()->withPivot('pantheon_rank')->where('animes.id', $anime1->id)->first()->pivot->pantheon_rank)->toBeNull();
    });

    it('hits the continue block and strips complex titles with regex in public list', function () {
        $anime1 = Anime::factory()->create(['title' => 'Fate/Zero 2nd Season']);
        $anime2 = Anime::factory()->create(['title' => '86 - Eighty Six']);     
        $anime3 = Anime::factory()->create(['title' => 'Sword Art Online II']);  
        $anime4 = Anime::factory()->create(['title' => 'Lycoris Recoil']);
    
        $genre = Genre::factory()->create(['name' => 'Action']);
        foreach ([$anime1, $anime2, $anime3, $anime4] as $anime) {
            $anime->genres()->attach($genre->id);
        }
    
        $this->user->animes()->attach($anime1->id, ['status' => 'completed']);
        $this->user->animes()->attach($anime2->id, ['status' => 'completed']);
        $this->user->animes()->attach($anime3->id, ['status' => 'completed']);
        
        $this->user->animes()->attach($anime4->id, ['status' => 'watching']); 
    
        $this->get(route('user.list', $this->user))->assertSuccessful();
    });

    it('parses hours and minutes perfectly from Jikan API duration string', function () {
        Http::fake([
            'https://api.tenrai.org/v1/anime/55555' => Http::response([
                'data' => [
                    'mal_id' => 55555,
                    'title' => 'Long Movie',
                    'images' => ['jpg' => ['large_image_url' => 'test.jpg']],
                    'episodes' => 1,
                    'score' => 9,
                    'type' => 'Movie',
                    'duration' => '1 hr 35 min',
                ]
            ], 200)
        ]);
    
        $this->get(route('animes.show', 55555))->assertSuccessful();
    });

    it('fetches manual ranking and reorders animes via API', function () {
        $anime1 = Anime::factory()->create();
        $anime2 = Anime::factory()->create();
    
        
        $this->user->animes()->attach($anime1->id, ['status' => 'completed', 'rank' => 2]);
        $this->user->animes()->attach($anime2->id, ['status' => 'completed', 'rank' => 1]);
    
    
        $this->get('/api/manual-ranking')
            ->assertSuccessful()
            ->assertJsonCount(2);
    
        
        $this->post('/api/reorder', [
            'animes' => [$anime1->id, $anime2->id] 
        ])->assertSuccessful()
          ->assertJson(['message' => 'Ordre sauvegardé']);
    
        
        expect($this->user->animes()->where('animes.id', $anime1->id)->first()->pivot->rank)->toBe(1)
            ->and($this->user->animes()->where('animes.id', $anime2->id)->first()->pivot->rank)->toBe(2);
    });

    it('updates the custom image via selected_image_url and sets a review', function () {
        $anime = Anime::factory()->create();
        $this->user->animes()->attach($anime->id, ['status' => 'watching']);
    
        $this->put(route('animes.update', $anime->id), [
            'selected_image_url' => 'https://test.jpg',
            'review' => 'Un chef-d\'oeuvre.'
        ])->assertRedirect();
    
        $pivot = $this->user->animes()->withPivot(['custom_image_path', 'review'])->first()->pivot;
        expect($pivot->custom_image_path)->toBe('https://test.jpg')
            ->and($pivot->review)->toBe('Un chef-d\'oeuvre.');
    });

    it('updates status and score directly without triggering side effects', function () {
        $anime = Anime::factory()->create(['episodes' => 100]);
        $this->user->animes()->attach($anime->id, ['status' => 'plan_to_watch', 'score' => 0]);
    
        $this->put(route('animes.update', $anime->id), [
            'status' => 'watching',
            'score' => 10          
        ])->assertRedirect();
    
        $pivot = $this->user->animes->first()->pivot;
        expect($pivot->status)->toBe('watching')
            ->and($pivot->score)->toBe(10);
    });

    it('updates and resets the custom rank explicitly using array_key_exists', function () {
        $anime = Anime::factory()->create();
        
        $this->user->animes()->attach($anime->id, ['rank' => 10]);
    
        $this->put(route('animes.update', $anime->id), [
            'rank' => null
        ])->assertRedirect();
    
        $pivot = $this->user->animes()->withPivot('rank')->where('animes.id', $anime->id)->first()->pivot;
        
        expect($pivot->rank)->toBeNull();
    });

    it('syncs collections when updating an anime', function () {
        $anime = Anime::factory()->create(['title' => 'My Dress-Up Darling']);
        $this->user->animes()->attach($anime->id, ['status' => 'completed']);
    
        $collection = $this->user->collections()->create([
            'name' => 'Chefs-d\'œuvre absolus',
            'description' => 'Uniquement du rang Masters'
        ]);
    
        $this->put(route('animes.update', $anime->id), [
            'collections' => [$collection->id]
        ])->assertRedirect();
    
        expect($anime->collections)->toHaveCount(1)
            ->and($anime->collections->first()->id)->toBe($collection->id);
    });
});