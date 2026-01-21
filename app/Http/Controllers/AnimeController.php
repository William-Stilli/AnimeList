<?php
namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnimeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mal_id' => 'required|integer',
            'title' => 'required|string',
            'title_english' => 'nullable|string',
            'image_url' => 'nullable|string',
            'episodes' => 'nullable|integer',
        ]);

        $anime = Anime::firstOrCreate(
            ['mal_id' => $validated['mal_id']],
            [
                'title' => $validated['title'],
                'title_english' => $validated['title_english'] ?? null,
                'image_url' => $validated['image_url'],
                'episodes' => $validated['episodes'] ?? null,
            ]
        );

        if ($request->has('genres')) {
            foreach ($request->input('genres') as $genreData) {
                $genre = Genre::firstOrCreate(
                    ['mal_id' => $genreData['mal_id']],
                    ['name' => $genreData['name']],
                );

                $anime->genres()->syncWithoutDetaching($genre->id);
            }
        }

        $request->user()->animes()->syncWithoutDetaching([
            $anime->id => ['status' => 'plan_to_watch']
        ]);

        return response()->json(['message' => 'Animé et genres ajouté avec succès !']);
    }

    public function index(Request $request)
    {
        return $request->user()
            ->animes()
            ->orderByPivot('updated_at', 'desc')
            ->get();
    }

    public function update(Request $request, Anime $anime)
    {
        $validated = $request->validate([
            'status' => 'required|in:watching,completed,plan_to_watch,dropped',
            'progress' => 'integer|min:0',
            'score' => 'nullable|integer|min:0|max:10',
        ]);

        $request->user()->animes()->updateExistingPivot($anime->id, [
            'status' => $validated['status'],
            'progress' => $validated['progress'],
            'score' => $validated['score'],
        ]);

        return response()->json(['message' => 'Mise à jour réussie !']);
    }

    public function destroy(Request $request, Anime $anime)
    {
        $request->user()->animes()->detach($anime->id);

        return response()->json(['message' => 'Animé supprimé de la liste']);
    }

    public function manualRanking(Request $request)
    {
        return $request->user()->animes()->wherePivot('status', 'completed')->orderByPivot('rank', 'asc')->orderByPivot('updated_at', 'desc')->get();
    }

    public function reorder(Request $request)
    {
        $orderedIds = $request->input('animes');

        foreach ($orderedIds as $index => $animeId) {
            $request->user()->animes()->updateExistingPivot($animeId, [
                'rank' => $index + 1
            ]);
        }

        return response()->json(['message' => 'Ordre sauvegardé']);
    }
}