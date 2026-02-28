<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Anime;
use App\Models\Genre;
use Illuminate\Support\Facades\Http;

class FixAnimeGenres extends Command
{
    protected $signature = 'anime:fix-genres';

    protected $description = 'Récupère les genres manquants via l\'API Jikan pour les animés importés';

    public function handle()
    {
        $animes = Anime::doesntHave('genres')->get();

        if ($animes->isEmpty()) {
            $this->info("Tout est propre ! Tous tes animés ont déjà des genres.");
            return;
        }

        $this->info("J'ai trouvé {$animes->count()} animés à traiter. Démarrage...");

        $bar = $this->output->createProgressBar($animes->count());
        $bar->start();

        foreach ($animes as $anime) {
            if (!$anime->mal_id) {
                $bar->advance();
                continue;
            }

            try {
                $response = Http::get("https://api.jikan.moe/v4/anime/{$anime->mal_id}");
                
                if ($response->successful()) {
                    $data = $response->json()['data'];
                    
                    $genres = $data['genres'] ?? [];         
                    $demographics = $data['demographics'] ?? [];
                    $explicit = $data['explicit_genres'] ?? []; 

                    $themes = $data['themes'] ?? []; 
                    
                    $allCandidates = array_merge($genres, $demographics, $explicit, $themes);

                    $blacklist = [
                        'Urban Fantasy', 'Strategy Game', 'High Stakes Game', 
                        'Organized Crime', 'Workplace', 'Detective', 
                        'Showbiz', 'Medical', 'Childcare', 'Pets', 'Educational',
                        
                        'Otaku Culture', 'Idols (Female)', 'Idols (Male)', 
                        'CGDCT', 'Iyashikei', 'Visual Arts', 'Performing Arts',
                        'Team Sports', 'Combat Sports', 
                        'Love Status Quo',
                        'Gag Humor', 
                        'Crossdressing',
                        'Delinquents',
                        'Adult Cast',
                        'Anthropomorphic',
                    ];

                    foreach ($allCandidates as $genreData) {
                        
                        if (in_array($genreData['name'], $blacklist)) {
                            continue;
                        }


                        $genre = Genre::firstOrCreate(
                            ['mal_id' => $genreData['mal_id']],
                            ['name' => $genreData['name']]
                        );

                        $anime->genres()->syncWithoutDetaching([$genre->id]);
                    }
                }

                usleep(500000);

            } catch (\Exception $e) {
                $this->error("Erreur ID {$anime->mal_id} : " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Terminé ! Les genres indésirables ont été filtrés.");
        
        $this->call('badges:reset-all');
    }
}