<?php
namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Anime;
use App\Jobs\FetchAnimeData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AnimeController extends Controller
{
    public function show(Request $request, $id)
    {
        $anime = Anime::where('mal_id', $id)->first();

        if ($anime) {
            $anime->load([
                'users' => function ($query) use ($request) {
                    $query->where('users.id', $request->user()->id)
                        ->withPivot('status', 'score', 'progress', 'rank', 'review', 'is_stu', 'custom_image_path');
                },
                'genres'
            ]);
        } else {
            $response = Http::withoutVerifying()->get("https://api.jikan.moe/v4/anime/{$id}");

            if ($response->failed()) {
                abort(404);
            }

            $apiData = $response->json('data');

            $durationInMinutes = $this->parseDuration($apiData['duration'] ?? '');

            $anime = new Anime([
                'mal_id' => $apiData['mal_id'],
                'title' => $apiData['title_english'] ?? $apiData['title'],
                'image_url' => $apiData['images']['jpg']['large_image_url'],
                'episodes' => $apiData['episodes'],
                'score' => $apiData['score'],
                'type' => $apiData['type'],
                'year' => $apiData['year'] ?? null,
                'synopsis' => $apiData['synopsis'] ?? '',
                'duration' => $durationInMinutes,
            ]);

            $anime->is_saved = false;
        }

        return Inertia::render('AnimeDetails', [
            'anime' => $anime
        ]);
    }

    private function parseDuration(?string $durationString): int
    {
        if (!$durationString || $durationString === 'Unknown') {
            return 24;
        }

        $minutes = 0;

        if (preg_match('/(\d+)\s*hr/', $durationString, $hours)) {
            $minutes += (int) $hours[1] * 60;
        }

        if (preg_match('/(\d+)\s*min/', $durationString, $mins)) {
            $minutes += (int) $mins[1];
        }

        return $minutes > 0 ? $minutes : 24;
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
            ->withPivot([
                'status',
                'progress',
                'score',
                'is_stu',
                'updated_at',
                'custom_image_path',
                'pantheon_rank'
            ])
            ->with(['genres', 'collections:id'])
            ->orderByPivot('is_stu', 'desc')
            ->orderByRaw('anime_user.pantheon_rank IS NULL ASC')
            ->orderByPivot('pantheon_rank', 'asc')
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
            'review' => 'nullable|string|max:5000',
            'selected_image_url' => 'nullable|url',
            'pantheon_rank' => 'nullable|integer|in:1,2,3'
        ]);

        $user = $request->user();
        $currentEntry = $user->animes()->where('animes.id', $anime->id)->first();

        if (!$currentEntry) {
            return redirect()->back()->with('error', 'Cet animé n\'est pas dans ta liste.');
        }

        $currentPivot = $currentEntry->pivot;
        $pivotData = [];
        $xpChange = 0;
        $message = 'Dossier mis à jour.';

        if (array_key_exists('pantheon_rank', $validated)) {
            $rank = $validated['pantheon_rank'];

            if ($rank !== null) {
                $existing = $user->animes()->wherePivot('pantheon_rank', $rank)->first();

                if ($existing && $existing->id !== $anime->id) {
                    $user->animes()->updateExistingPivot($existing->id, ['pantheon_rank' => null]);
                }
            }

            $pivotData['pantheon_rank'] = $rank;
        }

        if (isset($validated['status']))
            $pivotData['status'] = $validated['status'];
        if (isset($validated['progress']))
            $pivotData['progress'] = $validated['progress'];
        if (isset($validated['score']))
            $pivotData['score'] = $validated['score'];
        if (array_key_exists('rank', $validated))
            $pivotData['rank'] = $validated['rank'];
        if (array_key_exists('review', $validated))
            $pivotData['review'] = $validated['review'];

        if (isset($validated['progress']) && $anime->episodes && $validated['progress'] >= $anime->episodes) {
            $pivotData['status'] = 'completed';
            $pivotData['progress'] = $anime->episodes;
        }

        if (isset($validated['status']) && $validated['status'] === 'completed') {
            if ($anime->episodes) {
                $pivotData['progress'] = $anime->episodes;
            }
        }

        if (!empty($validated['selected_image_url'])) {
            $pivotData['custom_image_path'] = $validated['selected_image_url'];
            $message .= " Image de couverture modifiée !";
        }

        if ($request->boolean('reset_image')) {
            $pivotData['custom_image_path'] = null;
            $message .= " Image rétablie par défaut.";
        }

        $newProgress = $pivotData['progress'] ?? $currentPivot->progress;
        $oldProgress = $currentPivot->progress;

        if ($newProgress != $oldProgress) {
            $diff = $newProgress - $oldProgress;
            $xpChange += ($diff * 10);
        }

        $newStatus = $pivotData['status'] ?? $currentPivot->status;
        $oldStatus = $currentPivot->status;

        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            $xpChange += 100;
            $message = "Animé terminé !";
        }

        if ($oldStatus === 'completed' && $newStatus !== 'completed') {
            $xpChange -= 100;
            $message = "Statut corrigé.";
        }

        if ($request->hasFile('custom_image')) {
            $path = $request->file('custom_image')->store('covers', 'public');
            $pivotData['custom_image_path'] = $path;
            $message .= " Image perso ajoutée !";
        }

        if (!empty($pivotData)) {
            $user->animes()->updateExistingPivot($anime->id, $pivotData);
        }


        if ($xpChange !== 0) {
            $currentXp = $user->xp;
            $newXp = max(0, $currentXp + $xpChange);

            $user->update(['xp' => $newXp]);

            if ($xpChange > 0) {
                if (method_exists($user, 'checkAchievements')) {
                    $user->checkAchievements($anime);
                }
                $message .= " +{$xpChange} XP.";
            } else {
                $loss = abs($xpChange);
                $message .= " Correction effectuée : -{$loss} XP.";
            }
        }

        if ($request->has('collections')) {
            $anime->collections()->sync($request->collections);
        } else {
            $anime->collections()->sync([]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function destroy(Request $request, Anime $anime)
    {
        $user = $request->user();

        $animeInList = $user->animes()->where('animes.id', $anime->id)->first();

        if ($animeInList) {
            $pivot = $animeInList->pivot;
            $xpLost = 0;

            if ($pivot->progress > 0) {
                $xpLost += ($pivot->progress * 10);
            }

            if ($pivot->status === 'completed') {
                $xpLost += 100;
            }

            if ($xpLost > 0) {
                $newXp = max(0, $user->xp - $xpLost);
                $user->update(['xp' => $newXp]);
            }

            $user->animes()->detach($anime->id);

            return response()->json([
                'message' => "Animé supprimé. Tu as perdu {$xpLost} XP."
            ]);
        }

        return response()->json(['message' => 'Animé introuvable dans ta liste'], 404);
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

    public function publicList(User $user)
    {
        $user->load('badges');

        $animes = $user->animes()
            ->with('genres')
            ->withPivot(['is_stu', 'pantheon_rank', 'updated_at', 'review', 'score', 'status'])
            ->orderByPivot('is_stu', 'desc')
            ->orderByRaw('anime_user.pantheon_rank IS NULL ASC')
            ->orderByPivot('pantheon_rank', 'asc')
            ->orderByPivot('updated_at', 'desc')
            ->get();

        $uniqueFranchises = [];

        foreach ($animes as $anime) {
            if ($anime->pivot->status !== 'completed') {
                continue;
            }

            $title = $anime->title_english ?? $anime->title;

            $cleanTitle = preg_replace('/(:? Season \d+|:? \d+(st|nd|rd|th) Season|:? Part \d+|:? The Final Season|:? Next Shine)/i', '', $title);
            $cleanTitle = preg_replace('/(\s-.*)/', '', $cleanTitle);
            $cleanTitle = preg_replace('/(\s[IVX]+)$/', '', $cleanTitle);
            $cleanTitle = trim($cleanTitle);

            foreach ($anime->genres as $genre) {
                $uniqueFranchises[$genre->name][$cleanTitle] = true;
            }
        }

        $topGenres = collect($uniqueFranchises)->map(function ($titlesArray) {
            return count($titlesArray);
        })
            ->sortDesc()
            ->take(6);

        return Inertia::render('PublicLibrary', [
            'animes' => $animes,
            'targetUser' => [
                'name' => $user->name,
                'id' => $user->id,
                'badges' => $user->badges,
                'level' => $user->level_title
            ],
            'radarData' => [
                'labels' => $topGenres->keys()->toArray(),
                'values' => $topGenres->values()->toArray(),
            ]
        ]);
    }

    public function updatePantheon(Request $request, Anime $anime)
    {
        $request->validate([
            'rank' => 'nullable|integer|in:1,2,3'
        ]);

        $rank = $request->rank;
        $user = auth()->user();

        if ($rank !== null) {
            $existing = $user->animes()->wherePivot('pantheon_rank', $rank)->first();

            if ($existing && $existing->id !== $anime->id) {
                $user->animes()->updateExistingPivot($existing->id, ['pantheon_rank' => null]);
            }
        }

        $user->animes()->syncWithoutDetaching([
            $anime->id => ['pantheon_rank' => $rank]
        ]);

        return back()->with('success', 'Le Panthéon a été mis à jour avec succès !');
    }

    public function community(Request $request)
    {
        $users = User::where('id', '!=', $request->user()->id)
            ->withCount('animes')
            ->orderBy('animes_count', 'desc')
            ->get();

        return Inertia::render('Community', [
            'users' => $users
        ]);
    }

    public function search(Request $request)
    {
        $animes = [];
        $filters = $request->only(['search']);

        if ($request->filled('search')) {
            $searchTerm = rawurlencode($request->input('search'));

            $response = Http::withoutVerifying()
                ->timeout(10)
                ->get("https://api.jikan.moe/v4/anime?q={$searchTerm}&sfw=true&limit=24");

            if ($response->successful()) {
                $animes = $response->json('data');

                $animes = array_map(function ($anime) {
                    $anime['title'] = $anime['title_english'] ?? $anime['title'];
                    return $anime;
                }, $animes);
            }
        }

        return Inertia::render('AnimeSearch', [
            'animes' => $animes,
            'filters' => $filters
        ]);
    }

    public function toggleStuCategorie(Request $request, Anime $anime)
    {
        $user = $request->user();

        $currentEntry = $user->animes()->where('animes.id', $anime->id)->first();
        $isCurrentlyStu = $currentEntry->pivot->is_stu;

        if ($isCurrentlyStu) {
            $user->animes()->updateExistingPivot($anime->id, ['is_stu' => false]);
            return back()->with('success', "{$anime->title} a abdiqué. Le place est libre.");
        }

        \Illuminate\Support\Facades\DB::table('anime_user')
            ->where('user_id', $user->id)
            ->update(['is_stu' => false]);

        $user->animes()->updateExistingPivot($anime->id, ['is_stu' => true]);

        return back()->with('success', "Parfait, {$anime->title} est maintenant ton S.T.U. !");
    }
}