<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class RecalculateXp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recalculate-xp {user_id? : (Optional) The ID of the user to recalculate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate XP for users based on their watched animes and award badges.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');

        $query = User::query();
        if ($userId) {
            $query->where('id', $userId);
        }

        $users = $query->with(['animes.genres'])->get();

        $bar = $this->output->createProgressBar(count($users));
        $bar->start();

        foreach ($users as $user) {
            $totalXp = 0;
            $episodeCount = 0;

            foreach ($user->animes as $anime) {
                $progress = $anime->pivot->progress ?? 0;
                $totalXp += ($progress * 10);
                $episodeCount += $progress;

                if ($anime->pivot->status === 'completed') {
                    $totalXp += 100;
                }
            }

            $user->updateQuietly(['xp' => $totalXp]);

            if (method_exists($user, 'checkAchievements')) {
                $this->info("\nVérification des badges pour {$user->name}...");
                try {
                    $user->checkAchievements();
                } catch (\Exception $e) {
                    $this->error("Erreur lors du check des badges : " . $e->getMessage());
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($userId && $users->isNotEmpty()) {
            $u = $users->first();
            $u->load('badges');
            $this->info("User [{$u->name}] : {$u->xp} XP.");
            $this->info("Badges débloqués : " . $u->badges->pluck('name')->join(', '));
        }
    }
}