<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\AnimeController;

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
});

require __DIR__ . '/settings.php';
