<?php

use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnimeController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('anime-dashboard', [DashboardController::class, 'index'])->name('anime.dashboard');

    Route::get('/search', [AnimeController::class, 'search'])->name('anime.search');

    //Route::get('/animes/{anime}', [AnimeController::class, 'show'])->name('animes.show');
    Route::get('/animes/{id}', [AnimeController::class, 'show'])->name('animes.show');
    Route::post('/animes', [AnimeController::class, 'store'])->name('animes.store');
    Route::put('/animes/{anime}', [AnimeController::class, 'update'])->name('animes.update');
    Route::delete('/animes/{anime}', [AnimeController::class, 'destroy'])->name('anime.destroy');

    Route::get('/library', function () {
        //dd("STOP ! Je suis vivant !");
        return Inertia::render('Library');
    })->name('library');

    Route::get('/my-animes', [AnimeController::class, 'index']);

    Route::get('/api/manual-ranking', [AnimeController::class, 'manualRanking']);
    Route::post('/api/reorder', [AnimeController::class, 'reorder']);

    Route::get('/ranking', [StatsController::class, 'ranking'])->name('anime.ranking');

    Route::get('/stats', [StatsController::class, 'index'])->name('stats');

    Route::get('/community', [AnimeController::class, 'community'])->name('community.index');

    Route::get('/u/{user}', [AnimeController::class, 'publicList'])->name('user.list');
});

require __DIR__ . '/settings.php';
