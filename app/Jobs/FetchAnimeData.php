<?php

namespace App\Jobs;

use App\Models\Anime;
use App\Models\Genre;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\Middleware\RateLimited;

class FetchAnimeData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $anime;

    public $tries = 3;

    public function __construct(Anime $anime)
    {
        $this->anime = $anime;
    }

    public function middleware(): array
    {
        return [new RateLimited('jikan-api')];
    }

    public function handle(): void
    {
        Log::info("JOB DÉMARRÉ : Récupération pour l'ID " . $this->anime->mal_id);

        if ($this->anime->mal_id) {
            $response = Http::withoutVerifying()->timeout(10)->get("https://api.jikan.moe/v4/anime/{$this->anime->mal_id}");

            if ($response->successful()) {
                $data = $response->json()['data'];

                $preferredTitle = $data['title_english'] ?? $data['title'];

                $durationStr = $data['duration'] ?? '';
                $durationMinutes = 24;

                if (preg_match('/(\d+)\s*hr/', $durationStr, $hours)) {
                    $durationMinutes = (int) $hours[1] * 60;
                }

                if (preg_match('/(\d+)\s*min/', $durationStr, $mins)) {
                    if (isset($hours[1])) {
                        $durationMinutes += (int) $mins[1];
                    } else {
                        $durationMinutes = (int) $mins[1];
                    }
                }

                $this->anime->update([
                    'title' => $preferredTitle,
                    'synopsis' => $data['synopsis'] ?? null,
                    'title_english' => $data['title_english'] ?? null,
                    'episodes' => $data['episodes'] ?? null,
                    'type' => $data['type'] ?? null,
                    'source' => $data['source'] ?? null,
                    'status' => $data['status'] ?? null,
                    'season' => $data['season'] ?? null,
                    'year' => $data['year'] ?? null,
                    'duration' => $durationMinutes,
                ]);

                $genres = $data['genres'] ?? [];
                $themes = $data['themes'] ?? [];
                $explicit = $data['explicit_genres'] ?? [];
                $demographics = $data['demographics'] ?? [];

                $allCandidates = array_merge($genres, $themes, $explicit, $demographics);

                $blacklist = [
                    'Urban Fantasy',
                    'Strategy Game',
                    'High Stakes Game',
                    'Organized Crime',
                    'Workplace',
                    'Detective',
                    'Showbiz',
                    'Medical',
                    'Childcare',
                    'Pets',
                    'Educational',
                    'Otaku Culture',
                    'Idols (Female)',
                    'Idols (Male)',
                    'CGDCT',
                    'Iyashikei',
                    'Visual Arts',
                    'Performing Arts',
                    'Team Sports',
                    'Combat Sports',
                    'Love Status Quo',
                    'Gag Humor',
                    'Crossdressing',
                    'Delinquents',
                    'Adult Cast',
                    'Anthropomorphic'
                ];

                if (!empty($allCandidates)) {
                    $genreIds = [];
                    foreach ($allCandidates as $genreData) {

                        if (in_array($genreData['name'], $blacklist)) {
                            continue;
                        }

                        $genre = Genre::firstOrCreate(
                            ['mal_id' => $genreData['mal_id']],
                            ['name' => $genreData['name']]
                        );
                        $genreIds[] = $genre->id;
                    }
                    $this->anime->genres()->sync($genreIds);
                }

                Log::info("JOB SUCCÈS : Données mises à jour pour " . $this->anime->title);
            } else {
                Log::error("JOB ERREUR API : " . $response->status());
            }
        } else {
            Log::warning("JOB SKIP : Pas de mal_id trouvé.");
        }
    }
}