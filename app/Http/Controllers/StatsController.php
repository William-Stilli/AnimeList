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

        $statusStats = DB::table('anime_user')
            ->where('user_id', $userId)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $scoreStats = DB::table('anime_user')
            ->where('user_id', $userId)
            ->whereNotNull('score')
            ->where('score', '>', 0)
            ->select('score', DB::raw('count(*) as total'))
            ->groupBy('score')
            ->orderBy('score')
            ->get();

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
            $title = $row->title_english ?? $row->title;

            $cleanTitle = preg_replace('/(:? Season \d+|:? \d+(st|nd|rd|th) Season|:? Part \d+|:? The Final Season|:? Next Shine)/i', '', $title);

            $cleanTitle = preg_replace('/(\s-.*)/', '', $cleanTitle);

            $cleanTitle = preg_replace('/(\s[IVX]+)$/', '', $cleanTitle);

            $cleanTitle = trim($cleanTitle);

            $uniqueFranchises[$row->name][$cleanTitle] = true;
        }

        $genreStats = [];
        foreach ($uniqueFranchises as $genreName => $franchises) {
            $genreStats[] = [
                'name' => $genreName,
                'total' => count($franchises)
            ];
        }

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
