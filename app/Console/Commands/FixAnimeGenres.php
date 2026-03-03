<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Anime;
use App\Models\Genre;
use Illuminate\Support\Facades\Http;

class FixAnimeGenres extends Command
{
    protected $signature = 'anime:fix-genres';

    protected $description = 'Récupère et met à jour TOUS les genres/thèmes via l\'API Jikan en écrasant les anciens';

    public function handle()
    {
        $animes = Anime::all();

        if ($animes->isEmpty()) {
            $this->info("Ta base de données est vide, ajoute des animés d'abord !");
            return;
        }

        $this->info("J'ai trouvé {$animes->count()} animés. Démarrage de la mise à jour totale...");

        $bar = $this->output->createProgressBar($animes->count());
        $bar->start();

        foreach ($animes as $anime) {
            if (!$anime->mal_id) {
                $bar->advance();
                continue;
            }

            try {
                $response = Http::withoutVerifying()->get("https://api.jikan.moe/v4/anime/{$anime->mal_id}");

                if ($response->successful()) {
                    $data = $response->json()['data'];

                    $genres = $data['genres'] ?? [];
                    $explicit = $data['explicit_genres'] ?? [];
                    $themes = $data['themes'] ?? [];
                    $demographics = $data['demographics'] ?? [];

                    $allCandidates = array_merge($genres, $explicit, $themes, $demographics);

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

                    $anime->genres()->sync($genreIds);
                }

                usleep(500000);

            } catch (\Exception $e) {
                $this->error("\nErreur pour l'animé ID {$anime->mal_id} : " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Terminé ! Tous tes animés sont maintenant parfaitement catégorisés.");

        $this->call('badges:reset-all');
    }
}