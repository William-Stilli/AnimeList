<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $user->load('badges');

        $watching = $user->animes()
            ->wherePivot('status', 'watching')
            ->orderByPivot('updated_at', 'desc')
            ->take(4)
            ->get();

        $totalEpisodes = $user->animes()->sum('anime_user.progress');

        $totalMinutes = DB::table('anime_user')
            ->join('animes', 'anime_user.anime_id', '=', 'animes.id')
            ->where('anime_user.user_id', $user->id)
            ->sum(DB::raw('anime_user.progress * animes.duration'));

        $days = floor($totalMinutes / 1440);
        $hours = floor(($totalMinutes % 1440) / 60);

        $stuAnime = $user->animes()
            ->wherePivot('is_stu', true)
            ->first();

        return Inertia::render('AnimeDashboard', [
            'watching' => $watching,
            'stats' => [
                'episodes' => $totalEpisodes,
                'time_spent' => "{$days}j {$hours}h",
                'completed_count' => $user->animes()->wherePivot('status', 'completed')->count(),
            ],
            'badges' => $user->badges,
            'stuAnime' => $stuAnime
        ]);
    }
}