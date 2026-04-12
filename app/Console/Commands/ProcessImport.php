<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ProcessImport extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:process-import';

    /**
     * The console command description.
     */
    protected $description = 'Exécute toutes les actions post-importation JSON en une seule fois.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Démarrage du protocole de traitement massif...");

        $this->warn("\nÉtape 1/4 : Remplissage des données Jikan (fill-the-void)");
        Artisan::call('anime:fill-the-void', [], $this->output);

        $this->warn("\nÉtape 2/4 : Injection de force du BadgeSeeder");
        Artisan::call('db:seed', ['--class' => 'BadgeSeeder'], $this->output);

        $this->warn("\nÉtape 3/4 : Recalcul absolu de l'XP");
        Artisan::call('app:recalculate-xp', [], $this->output);

        $this->warn("\nÉtape 4/4 : Attribution des Badges");
        Artisan::call('badges:reset-all', [], $this->output);

        $this->newLine();
        $this->info("✅ Terminé ! Ton profil est 100% à jour.");
    }
}