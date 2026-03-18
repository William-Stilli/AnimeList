<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500'
        ]);

        $request->user()->collections()->create($validated);

        return back()->with('success', 'Collection créée avec succès !');
    }

    public function toggleAnime(Request $request, Collection $collection)
    {
        if ($collection->user_id !== $request->user()->id) {
            abort(403, 'Action non autorisée.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:animes,id'
        ]);

        $collection->animes()->toggle($validated['anime_id']);

        return back()->with('success', 'Playlist mise à jour');
    }

    public function destroy(Request $request, Collection $collection)
    {
        if ($collection->user_id !== $request->user()->id) {
            abort(403, 'Action non autorisée.');
        }

        $collection->delete();

        return back()->with('success', 'Collection supprimée.');
    }
}
