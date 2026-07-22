<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class MangaController extends Controller
{
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
        $validated = $request->validate([
            'mal_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'image_url' => 'nullable|url',
        ]);

        $exists = \App\Models\Manga::where('user_id', auth()->id())
            ->where('mal_id', $validated['mal_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['message' => 'Ce manga est déjà dans ta bibliothèque.']);
        }

        \App\Models\Manga::create([
            'user_id' => auth()->id(),
            'mal_id' => $validated['mal_id'],
            'title' => $validated['title'],
            'image_url' => $validated['image_url'],
            'status' => 'Plan to Read',
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
            $inLibrary = \App\Models\Manga::where('user_id', auth()->id())
                ->where('mal_id', $id)
                ->exists();
        }

        return Inertia::render('MangaDetails', [
            'manga' => $manga,
            'inLibrary' => $inLibrary
        ]);
    }
}