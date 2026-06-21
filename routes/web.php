<?php

use App\Http\Controllers\AnimeController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Settings\DataController;
use App\Http\Controllers\RewatchController;
use App\Http\Controllers\StatsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Artisan;

Route::get('/secret-badge-init', function () {
    try {
        Artisan::call('db:seed', ['--class' => 'BadgeSeeder', '--force' => true]);
        Artisan::call('badges:reset-all');
        Artisan::call('app:recalculate-xp');
        
        return "Opération réussie ! Les badges sont dans la base Aiven. Tu peux fermer cette page.";
    } catch (\Exception $e) {
        return "Alerte rouge : " . $e->getMessage();
    }
});

Route::get('/super-secret-migrate-otaku-777', function () {
    Artisan::call('migrate', ['--force' => true]);
    return '<pre>' . Artisan::output() . '</pre>';
});

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('anime-dashboard', [DashboardController::class, 'index'])->name('anime.dashboard');

    Route::get('/search', [AnimeController::class, 'search'])->name('anime.search');

    Route::get('/animes/{id}', [AnimeController::class, 'show'])->name('animes.show');
    Route::get('/animes/{id}/recommendations', [AnimeController::class, 'recommendation'])->name('anime.recommendations');
    Route::post('/animes', [AnimeController::class, 'store'])->name('animes.store');
    Route::post('/animes/{anime:mal_id}/stu', [AnimeController::class, 'toggleStuCategorie'])->name('animes.toggleStu');
    Route::put('/animes/{anime}', [AnimeController::class, 'update'])->name('animes.update');
    Route::delete('/animes/{anime}', [AnimeController::class, 'destroy'])->name('anime.destroy');

    Route::get('/library', function (Request $request) {
        $collections = $request->user()->collections()->with('animes:id')->get();

        return Inertia::render('Library', [
            'collections' => $collections
        ]);
    })->name('library');

    Route::get('/my-animes', [AnimeController::class, 'index'])->name('animes.index');

    Route::get('/api/manual-ranking', [AnimeController::class, 'manualRanking']);
    Route::post('/api/reorder', [AnimeController::class, 'reorder']);

    Route::get('/ranking', [StatsController::class, 'ranking'])->name('anime.ranking');

    Route::get('/stats', [StatsController::class, 'index'])->name('stats');

    Route::get('/community', [AnimeController::class, 'community'])->name('community.index');

    Route::get('/u/{user}', [AnimeController::class, 'publicList'])->name('user.list');

    Route::post('/animes/{anime}/pantheon', [AnimeController::class, 'updatePantheon'])->name('animes.pantheon');

    Route::get('settings/data', [DataController::class, 'show'])->name('settings.data');
    Route::get('settings/data/export', [DataController::class, 'export'])->name('settings.data.export');
    Route::post('settings/data/import', [DataController::class, 'import'])->name('settings.data.import');
    Route::get('settings/data/exportOtherFormat', [DataController::class, 'exportOtherFormat'])->name('settings.data.exportOtherFormat');

    Route::post('/collections', [CollectionController::class, 'store'])->name('collections.store');
    Route::delete('/collections/{collection}', [CollectionController::class, 'destroy'])->name('collections.destroy');
    Route::post('/collections/{collection}/toggle', [CollectionController::class, 'toggleAnime'])->name('collections.toggle');

    Route::post('/animes/{anime}/rewatches', [RewatchController::class, 'store'])->name('rewatches.store');
    Route::delete('/rewatches/{rewatch}', [RewatchController::class, 'destroy'])->name('rewatches.destroy');
});

require __DIR__ . '/settings.php';
