<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetAllBadges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'badges:reset-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Supprime et recalcule tous les badges de la plateforme pour chaque utilisateur.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Suppression de tous les badges attribués...");

        Schema::disableForeignKeyConstraints();
        DB::table('badge_user')->truncate();
        Schema::enableForeignKeyConstraints();

        $users = User::all();

        if ($users->isEmpty()) {
            $this->warn("Aucun utilisateur trouvé en base de données.");
            return;
        }

        $this->info("Recalcul pour {$users->count()} utilisateurs...");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            $user->checkAchievements();
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Reset terminé. Les badges sont maintenant 100% personnels !");
        
        $this->comment("⚠️ N'oublie pas : si 'checkAchievements' envoie des tâches en arrière-plan, tes badges n'apparaîtront que lorsque tu auras lancé le Worker !");
    }
}