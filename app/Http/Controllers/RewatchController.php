<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Rewatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RewatchController extends Controller
{
    /**
     * Enregistrer un nouveau rewatch.
     */
    public function store(Request $request, Anime $anime)
    {
        $maxRule = $anime->episodes ? "|max:{$anime->episodes}" : '';

        $validated = $request->validate([
            'start_episode' => "required|integer|min:1{$maxRule}",
            'end_episode' => "required|integer|min:1|gte:start_episode{$maxRule}", 
        ], [
            'start_episode.max' => "L'animé n'a que {$anime->episodes} épisodes.",
            'end_episode.max' => "L'animé n'a que {$anime->episodes} épisodes.",
            'end_episode.gte' => "L'épisode de fin doit être plus grand ou égal au début."
        ]);

        $rewatch = Rewatch::create([
            'user_id' => Auth::id(),
            'anime_id' => $anime->id,
            'start_episode' => $validated['start_episode'],
            'end_episode' => $validated['end_episode'],
        ]);

        $episodesWatched = $validated['end_episode'] - $validated['start_episode'] + 1;
        
        $xpEarned = $episodesWatched * 10; 
        
        $user = Auth::user();
        $user->increment('xp', $xpEarned);

        return response()->json(['rewatch' => $rewatch]);
    }

    /**
     * Supprimer un rewatch existant.
     */
    public function destroy(Rewatch $rewatch)
    {
        if ($rewatch->user_id !== Auth::id()) {
            abort(403, 'Accès refusé.');
        }

        $episodesWatched = $rewatch->end_episode - $rewatch->start_episode + 1;
        $xpLost = $episodesWatched * 10;
        
        $user = Auth::user();
        
        $newXp = max(0, $user->xp - $xpLost); 
        $user->update(['xp' => $newXp]);

        $rewatch->delete();

        return response()->json(['message' => 'Rewatch supprimé avec succès. XP retiré.']);
    }
}