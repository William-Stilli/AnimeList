<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StatsController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // 1. Stats de Statut (Watching, Completed...)
        $statusStats = DB::table('anime_user')
            ->where('user_id', $userId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // 2. Stats de Score
        $scoreStats = DB::table('anime_user')
            ->where('user_id', $userId)
            ->whereNotNull('score')
            ->where('score', '>', 0)
            ->select('score', DB::raw('count(*) as total'))
            ->groupBy('score')
            ->orderBy('score')
            ->get();

        // 3. Stats de Genres (AVEC NETTOYAGE SNEAKY BASTARD) 🧹
        $rawGenres = DB::table('genres')
            ->join('anime_genre', 'genres.id', '=', 'anime_genre.genre_id')
            ->join('animes', 'anime_genre.anime_id', '=', 'animes.id')
            ->join('anime_user', 'animes.id', '=', 'anime_user.anime_id')
            ->where('anime_user.user_id', $userId)
            ->where('anime_user.status', 'completed')
            ->select('genres.name', 'animes.title', 'animes.title_english')
            ->get();

        $uniqueFranchises = [];

        foreach ($rawGenres as $row) {
            // On prend le titre anglais s'il existe, sinon le titre original
            $title = $row->title_english ?? $row->title;

            // NETTOYAGE 1 : Les Saisons classiques
            // Enlève " Season 2", " Part 3", " The Final Season", etc.
            $cleanTitle = preg_replace('/(:? Season \d+|:? \d+(st|nd|rd|th) Season|:? Part \d+|:? The Final Season)/i', '', $title);

            // NETTOYAGE 2 : Le "Grand Couperet" (Spécial Kaguya / Films) 🪓
            // Coupe tout ce qui est après un tiret " - ".
            // Ex: "Love is War - Ultra Romantic" -> "Love is War"
            // Ex: "Heaven's Feel - I. Presage Flower" -> "Heaven's Feel"
            $cleanTitle = preg_replace('/(\s-.*)/', '', $cleanTitle);

            // NETTOYAGE 3 : Les chiffres romains de fin (Spécial SAO II, III)
            // Enlève " II", " III" à la toute fin
            $cleanTitle = preg_replace('/(\s[IVX]+)$/', '', $cleanTitle);

            $cleanTitle = trim($cleanTitle);

            // On marque cette franchise comme vue pour ce genre
            $uniqueFranchises[$row->name][$cleanTitle] = true;
        }

        // On compte
        $genreStats = [];
        foreach ($uniqueFranchises as $genreName => $franchises) {
            $genreStats[] = [
                'name' => $genreName,
                'total' => count($franchises)
            ];
        }

        // Tri décroissant
        usort($genreStats, fn($a, $b) => $b['total'] <=> $a['total']);

        return Inertia::render('Stats', [
            'statusData' => $statusStats,
            'scoreData' => $scoreStats,
            'genreData' => $genreStats
        ]);
    }

    public function ranking(Request $request)
    {
        $animes = $request->user()->animes()
            ->select('animes.id', 'title', 'image_url')
            ->withPivot('rank')
            ->get();

        return Inertia::render('Ranking', [
            'animes' => $animes
        ]);
    }
}
