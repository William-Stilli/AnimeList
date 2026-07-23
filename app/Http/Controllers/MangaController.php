<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MangaController extends Controller
{
    public function index(Request $request)
    {
        $mangas = $request->user()->mangas()
            ->orderBy('manga_user.updated_at', 'desc')
            ->get();

        return Inertia::render('MangaLibrary', [
            'mangas' => $mangas
        ]);
    }

    public function search(Request $request)
    {
        $mangas = [];
        $filters = $request->only(['search']);
        $apiError = null;

        if ($request->filled('search')) {
            $searchTerm = trim(strtolower($request->input('search')));
            $cacheKey = 'tenrai_manga_search_' . md5($searchTerm);

            $mangas = Cache::remember($cacheKey, 3600, function () use ($searchTerm, &$apiError) {
                $response = Http::withHeaders([
                        'User-Agent' => 'UltimateAnimeTracker/1.0 (Projet étudiant CFC)'
                    ])
                    ->withOptions([
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    ])
                    ->withoutVerifying()
                    ->timeout(15)
                    ->get("https://api.tenrai.org/v1/manga", [
                        'q' => $searchTerm,
                        'sfw' => 'true',
                        'limit' => 24
                    ]);

                if ($response->successful()) {
                    $data = $response->json('data') ?? [];
                    return array_map(function ($manga) {
                        $manga['title'] = $manga['title_english'] ?? $manga['title'];
                        return $manga;
                    }, $data);
                }

                $apiError = "L'API Tenrai a renvoyé une erreur (Code: " . $response->status() . ").";
                return [];
            });

            if (empty($mangas) || $apiError) {
                Cache::forget($cacheKey);
            }
        }

        return Inertia::render('MangaSearch', [
            'mangas' => $mangas,
            'filters' => $filters,
            'apiError' => $apiError
        ]);
    }

   public function store(Request $request)
{
    $user = $request->user();

    $validated = $request->validate([
        'mal_id' => 'required|integer',
        'title' => 'required|string|max:255',
        'image_url' => 'nullable|url',
        'chapters' => 'nullable|integer',
        'volumes' => 'nullable|integer',
    ]);

    $manga = \App\Models\Manga::firstOrCreate(
        ['mal_id' => $validated['mal_id']], 
        [
            'title' => $validated['title'],
            'image_url' => $validated['image_url'] ?? null,
            'chapters' => $validated['chapters'] ?? null,
            'volumes' => $validated['volumes'] ?? null,
        ]
    );

    $exists = $user->mangas()->where('mangas.id', $manga->id)->exists();

    if ($exists) {
        return back()->withErrors(['message' => 'Ce manga est déjà dans ta bibliothèque.']);
    }

    $user->mangas()->syncWithoutDetaching([
        $manga->id => [
            'status' => 'Plan to Read',
            'chapters_read' => 0,
            'volumes_owned' => 0,
            'score' => 0,
        ]
    ]);

    return back()->with('success', 'Manga ajouté à ta collection !');
}

    public function show($id)
    {
        $cacheKey = 'tenrai_manga_details_' . $id;

        $manga = Cache::remember($cacheKey, 3600, function () use ($id) {
            $response = Http::withHeaders([
                    'User-Agent' => 'UltimateAnimeTracker/1.0 (Projet étudiant CFC)'
                ])
                ->withoutVerifying()
                ->timeout(15)
                ->get("https://api.tenrai.org/v1/manga/{$id}");

            if ($response->successful()) {
                $data = $response->json('data');
                $data['title'] = $data['title_english'] ?? $data['title'];
                return $data;
            }

            return null;
        });

        if (!$manga) {
            abort(404, 'Manga introuvable sur Tenrai.');
        }

       $inLibrary = false;
        if (auth()->check()) {
            $inLibrary = auth()->user()->mangas()
                ->where('mangas.mal_id', $id)
                ->exists();
        }

        return Inertia::render('MangaDetails', [
            'manga' => $manga,
            'inLibrary' => $inLibrary
        ]);
    }

    public function myMangas(Request $request)
{
    $mangas = $request->user()->mangas()->get();

    return response()->json($mangas);
}

    public function update(Request $request, $id)
    {
        $user = $request->user();

        $manga = $user->mangas()->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|string',
            'chapters_read' => 'required|integer|min:0',
            'volumes_owned' => 'required|integer|min:0',
            'score' => 'required|integer|min:0|max:10',
            'pantheon_rank' => 'nullable|integer|in:1,2,3',
        ]);

        $maxChapters = $manga->chapters;
        $newChaptersRead = $validated['chapters_read'];
        $newStatus = $validated['status'];

        if ($maxChapters && $newChaptersRead > $maxChapters) {
            return response()->json([
                'message' => "Tu ne peux pas lire le chapitre {$newChaptersRead}, ce manga n'en possède que {$maxChapters} !"
            ], 422);
        }

        if ($maxChapters && $newChaptersRead == $maxChapters) {
            $newStatus = 'Completed';
        }

        if (!is_null($validated['pantheon_rank'])) {    
            \Illuminate\Support\Facades\DB::table('manga_user')
                ->where('user_id', $user->id)
                ->where('pantheon_rank', $validated['pantheon_rank'])
                ->where('manga_id', '!=', $id)
                ->update(['pantheon_rank' => null]);
        }

        $user->mangas()->updateExistingPivot($id, [
            'status' => $newStatus,
            'chapters_read' => $newChaptersRead,
            'volumes_owned' => $validated['volumes_owned'],
            'score' => $validated['score'],
            'pantheon_rank' => $validated['pantheon_rank'],
        ]);

        return response()->json(['message' => 'Mise à jour réussie']);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $user->mangas()->detach($id);

        return response()->json(['message' => 'Manga supprimé avec succès']);
    }

    public function toggleStu(Request $request, $mal_id)
{
    $user = $request->user();

    $manga = $user->mangas()->where('mangas.mal_id', $mal_id)->firstOrFail();

    $currentStatus = $manga->pivot->is_stu;

    if (!$currentStatus) {
        \Illuminate\Support\Facades\DB::table('manga_user')
            ->where('user_id', $user->id)
            ->update(['is_stu' => false]);
        
        $user->mangas()->updateExistingPivot($manga->id, ['is_stu' => true]);
    } else {
        $user->mangas()->updateExistingPivot($manga->id, ['is_stu' => false]);
    }

    return back();
}
}