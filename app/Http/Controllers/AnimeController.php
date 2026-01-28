<?php
namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Jobs\FetchAnimeData;

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
            'image_url' => 'required|string',
            'episodes' => 'nullable|integer',
        ]);

        $anime = Anime::firstOrCreate(
            ['mal_id' => $validated['mal_id']],
            [
                'title' => $validated['title'],
                'image_url' => $validated['image_url'],
                'episodes' => $validated['episodes'] ?? null,
            ]
        );

        if (!$request->user()->animes()->where('animes.id', $anime->id)->exists()) {
            $request->user()->animes()->attach($anime->id, [
                'status' => 'plan_to_watch',
                'progress' => 0,
                'score' => 0,
                'rank' => null
            ]);
        } else {
            return redirect()->back()->with('warning', 'Cet animé est déjà dans ta liste !');
        }

        FetchAnimeData::dispatch($anime);

        return redirect()->back()->with('success', 'Animé ajouté ! Les détails arrivent...');
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

        if (isset($validated['progress']) && $anime->episodes && $validated['progress'] >= $anime->episodes) {
            $pivotData['status'] = 'completed';
            $pivotData['progress'] = $anime->episodes;
        }

        if (isset($validated['status']) && $validated['status'] === 'completed') {
            if ($anime->episodes) {
                $pivotData['progress'] = $anime->episodes;
            }
        }

        // if ($request->has('progress')) {
        //     $current = $request->user()->animes()->where('anime_id', $anime->id)->first()->pivot->progress;

        //     dd([
        //         'Reçu du Front' => $request->input('progress'),
        //         'Actuel en Base' => $current,
        //         'ID Anime' => $anime->id,
        //         'ID User' => $request->user()->id
        //     ]);
        // }

        if (!empty($pivotData)) {
            $request->user()->animes()->updateExistingPivot($anime->id, $pivotData);
        }

        if (isset($validated['image_url'])) {
            $anime->update(['image_url' => $validated['image_url']]);
        }

        return redirect()->back()->with('success', 'Progression mise à jour !');
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