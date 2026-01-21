<?php

use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnimeController;
use App\Http\Controllers\StatsController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/search', function () {
        return Inertia::render('AnimeSearch');
    })->name('anime.search');

    Route::post('/animes', [AnimeController::class, 'store'])->name('animes.store');
    Route::put('/animes/{anime}', [AnimeController::class, 'update'])->name('animes.update');
    Route::delete('/animes/{anime}', [AnimeController::class, 'destroy'])->name('anime.destroy');

    Route::get('/library', function () {
        return Inertia::render('Library');
    })->name('library');

    Route::get('/my-animes', [AnimeController::class, 'index']);

    Route::get('/api/manual-ranking', [AnimeController::class, 'manualRanking']);
    Route::post('/api/reorder', [AnimeController::class, 'reorder']);

    Route::get('/tier-list', function () {
        return Inertia::render('TierList');
    })->name('anime.tier-list');

    Route::get('/stats', [StatsController::class, 'index'])->name('stats');
});

require __DIR__ . '/settings.php';
