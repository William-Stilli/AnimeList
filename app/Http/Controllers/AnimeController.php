<?php
namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AnimeController extends Controller
{
    public function show(Request $request, Anime $anime)
    {
        $anime->load([
            'genres',
            'users' => function ($query) use ($request) {
                $query->where('users.id', $request->user()->id);
            }
        ]);

        return Inertia::render('AnimeDetails', [
            'anime' => $anime
        ]);
    }
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
            'status' => 'sometimes|in:watching,completed,plan_to_watch,dropped',
            'progress' => 'sometimes|integer|min:0',
            'score' => 'sometimes|integer|min:0|max:10',
            'rank' => 'sometimes|nullable|integer',
            'image_url' => 'sometimes|string'
        ]);

        $pivotData = [];

        if (isset($validated['status']))
            $pivotData['status'] = $validated['status'];
        if (isset($validated['progress']))
            $pivotData['progress'] = $validated['progress'];
        if (isset($validated['score']))
            $pivotData['score'] = $validated['score'];
        if (array_key_exists('rank', $validated))
            $pivotData['rank'] = $validated['rank'];

        if (!empty($pivotData)) {
            $request->user()->animes()->updateExistingPivot($anime->id, $pivotData);
        }

        if (isset($validated['image_url'])) {
            $anime->update(['image_url' => $validated['image_url']]);
        }

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