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

        $statusStats = DB::table('anime_user')->where('user_id', $userId)->select('status', DB::raw('count(*) as total'))->groupBy('status')->get();

        $scoreStats = DB::table('anime_user')
            ->where('user_id', $userId)
            ->whereNotNull('score')
            ->where('score', '>', 0)
            ->select('score', DB::raw('count(*) as total'))
            ->groupBy('score')
            ->orderBy('score')
            ->get();

        $genreStats = DB::table('genres')
            ->join('anime_genre', 'genres.id', '=', 'anime_genre.genre_id')
            ->join('animes', 'anime_genre.anime_id', '=', 'animes.id')
            ->join('anime_user', 'animes.id', '=', 'anime_user.anime_id')
            ->where('anime_user.user_id', $userId)
            ->select('genres.name', DB::raw('count(*) as total'))
            ->groupBy('genres.name')
            ->orderBy('total', 'desc')
            ->get();

        return Inertia::render('Stats', [
            'statusData' => $statusStats,
            'scoreData' => $scoreStats,
            'genreData' => $genreStats
        ]);
    }
}
