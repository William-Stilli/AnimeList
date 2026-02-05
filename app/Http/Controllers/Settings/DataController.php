<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DataController extends Controller
{
    public function show()
    {
        return Inertia::render('settings/Data');
    }

    public function export(Request $request)
    {
        $filename = 'anime-list-' . date('Y-m-d') . '.json';

        $animes = $request->user()->animes()
            ->select('animes.mal_id', 'animes.title', 'animes.image_url', 'animes.episodes')
            ->get()
            ->map(function ($anime) {
                return [
                    'mal_id' => $anime->mal_id,
                    'title' => $anime->title,
                    'image_url' => $anime->image_url,
                    'total_episodes' => $anime->episodes,
                    'status' => $anime->pivot->status,
                    'score' => $anime->pivot->score,
                    'progress' => $anime->pivot->progress,
                    'review' => $anime->pivot->review,
                    'updated_at' => $anime->pivot->updated_at,
                ];
            });

        return response()->streamDownload(function () use ($animes) {
            echo json_encode($animes, JSON_PRETTY_PRINT);
        }, $filename);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimetypes:application/json,text/plain',
        ]);

        $json = json_decode(file_get_contents($request->file('file')->getRealPath()), true);

        if (!$json || !is_array($json)) {
            return back()->withErrors(['file' => "Le fichier JSON est corrompu ou invalide."]);
        }

        DB::transaction(function () use ($json) {
            $user = Auth::user();

            foreach ($json as $entry) {
                $anime = Anime::firstOrCreate(
                    ['mal_id' => $entry['mal_id']],
                    [
                        'title' => $entry['title'] ?? 'Unknown Title',
                        'image_url' => $entry['image_url'] ?? null,
                        'episodes' => $entry['total_episodes'] ?? null,
                    ]
                );

                $user->animes()->syncWithoutDetaching([
                    $anime->id => [
                        'status' => $entry['status'] ?? 'plan_to_watch',
                        'score' => $entry['score'] ?? 0,
                        'progress' => $entry['progress'] ?? 0,
                        'review' => $entry['review'] ?? null,
                    ]
                ]);
            }
        });

        return back()->with('success', count($json) . ' animés ont été importés avec succès.');
    }
}