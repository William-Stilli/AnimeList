<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            [
                'name' => 'Drama Queen',
                'slug' => 'drama-queen',
                'description' => '5 animés Dramatiques terminés.',
                'icon' => '😭',
                'xp_bonus' => 300,
            ],
            [
                'name' => 'Shonen Jumper',
                'slug' => 'shonen-jumper',
                'description' => '20 Shonens vus.',
                'icon' => '🔥',
                'xp_bonus' => 1000,
            ],
            [
                'name' => 'No Life',
                'slug' => 'no-life',
                'description' => 'Tu as passé plus de 1000 heures devant des animés.',
                'icon' => '💀',
                'xp_bonus' => 2000,
            ],
            [
                'name' => 'Romcom Enjoyer',
                'slug' => 'romcom-enjoyer',
                'description' => '10 Romances.',
                'icon' => '💘',
                'xp_bonus' => 400,
            ],
        ];

        foreach ($badges as $badge) {
            Badge::firstOrCreate(['slug' => $badge['slug']], $badge);
        }
    }
}