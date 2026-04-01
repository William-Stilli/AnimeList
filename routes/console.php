<?php

use App\Models\User;
use App\Models\Anime;
use App\Jobs\FetchAnimeData;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('anime:fill-the-void', function () {
    $this->info("Inspection minutieuse de la bibliothèque en cours...");

    $animes = Anime::whereNull('duration')
        ->orWhere('duration', 0)
        ->orDoesntHave('genres')
        ->get();

    $count = $animes->count();

    if ($count === 0) {
        $this->info("Incroyable ! Ta bibliothèque est absolument parfaite et complète.");
        return;
    }

    $this->warn("Attention : {$count} animés vont être mis à jour en mode SYNCHRONE.");
    $this->info("Pour ne pas froisser l'API Jikan, on va y aller doucement (1 requête / seconde).");
    $this->info("ça va prendre environ " . round($count / 60, 1) . " minutes.");
    $this->newLine();

    $bar = $this->output->createProgressBar($count);
    $bar->start();

    foreach ($animes as $anime) {
        FetchAnimeData::dispatchSync($anime);
        
        sleep(1); 
        
        $bar->advance();
    }

    $bar->finish();
    $this->newLine(2);
    $this->info("Opération terminée ! Aucun animé n'est passé à la trappe.");
    
})->purpose('Remplit les trous en mode synchrone et sécurisé (sans surcharger Jikan)');