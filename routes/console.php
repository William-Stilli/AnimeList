<?php

use App\Models\User;
use App\Models\Anime;
use App\Jobs\FetchAnimeData;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('anime:fill-the-void', function () {
    $this->info("Recherche des dossiers incomplets (Saison, Année ou Durée manquante)...");

    $animes = Anime::whereNull('season')
        ->orWhereNull('year')
        ->orWhereNull('duration')
        ->orWhere('duration', 0)
        ->get();

    $count = $animes->count();

    if ($count === 0) {
        $this->info("Incroyable ! Ta bibliothèque est déjà complète, Sneaky Bastard.");
        return;
    }

    $this->info("{$count} animés vont être mis à jour. Lancement des jobs...");

    $bar = $this->output->createProgressBar($count);
    $bar->start();

    foreach ($animes as $anime) {
        FetchAnimeData::dispatch($anime);
        $bar->advance();
    }

    $bar->finish();
    $this->newLine(2);
    $this->info("Tous les ordres de mission ont été transmis !");
    $this->comment("Lance 'php artisan queue:work' pour traiter les données en arrière-plan.");
})->purpose('Remplit les colonnes null pour toute la bibliothèque (Saisons, Années, Durées)');

Artisan::command('badges:reset-all', function () {
    $this->info("Suppression de tous les badges attribués...");

    DB::table('badge_user')->truncate();

    $users = User::all();
    $this->info("Recalcul pour {$users->count()} utilisateurs...");

    foreach ($users as $user) {
        $user->checkAchievements();
        $this->info("Badges synchronisés pour : {$user->name}");
    }

    $this->info("Reset terminé. Les badges sont maintenant 100% personnels !");
});