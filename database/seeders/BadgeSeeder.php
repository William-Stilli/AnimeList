<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $genres = [
            [
                'name' => 'Action',
                'icon' => 'swords',
                'color' => 'red',
                'description' => 'A regardé 10 animés d\'Action.',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Action']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Explorateur',
                'icon' => 'map',
                'color' => 'green',
                'description' => 'A regardé 10 animés d\'Adventure. Le monde est vaste.',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Adventure']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Maître du Rire',
                'icon' => 'smile',
                'color' => 'yellow',
                'description' => 'A regardé 10 animés Comedy. Aussi drôle que les plans de Kaguya-sama.',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Comedy']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Drama',
                'icon' => 'droplet',
                'color' => 'blue',
                'description' => 'A regardé 10 animés Drama.',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Drama']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Fantasy',
                'icon' => 'wand',
                'color' => 'purple',
                'description' => 'A regardé 10 animés Fantasy.',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Fantasy']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Cœur Palpitant',
                'icon' => 'heart',
                'color' => 'pink',
                'description' => 'A regardé 10 animés Romance.',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Romance']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Sci-Fi',
                'icon' => 'flask-conical',
                'color' => 'teal',
                'description' => 'A regardé 10 animés Sci-Fi.',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Sci-Fi']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Tranche de Vie',
                'icon' => 'coffee',
                'color' => 'orange',
                'description' => 'A regardé 10 animés Slice of Life.',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Slice of Life']),
                'xp_bonus' => 100
            ],

            [
                'name' => 'Détective de l\'Ombre',
                'icon' => 'search',
                'color' => 'gray',
                'description' => 'A regardé 10 animés Mystery.',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Mystery']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Tension Maximale',
                'icon' => 'alert-triangle',
                'color' => 'yellow',
                'description' => 'A regardé 10 animés Suspense.',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Suspense']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Survivant de la Nuit',
                'icon' => 'ghost',
                'color' => 'slate',
                'description' => 'A regardé 5 animés Horror.',
                'condition_type' => 'genre_count',
                'condition_value' => 5,
                'metadata' => json_encode(['genre_name' => 'Horror']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Supernaturel',
                'icon' => 'eye',
                'color' => 'indigo',
                'description' => 'A regardé 10 animés Supernatural. Tu vois ce que les autres ignorent.',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Supernatural']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Mind Game',
                'icon' => 'brain',
                'color' => 'purple',
                'description' => 'A regardé 5 animés Psychological.',
                'condition_type' => 'genre_count',
                'condition_value' => 5,
                'metadata' => json_encode(['genre_name' => 'Psychological']),
                'xp_bonus' => 100
            ],

            [
                'name' => 'Spearhead',
                'icon' => 'bot',
                'color' => 'gray',
                'description' => 'A regardé 5 animés Mecha.',
                'condition_type' => 'genre_count',
                'condition_value' => 5,
                'metadata' => json_encode(['genre_name' => 'Mecha']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Isekai',
                'icon' => 'truck',
                'color' => 'blue',
                'description' => 'A regardé 10 animés Isekai. Prêt pour ta nouvelle vie ?',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Isekai']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Beater',
                'icon' => 'gamepad-2',
                'color' => 'slate',
                'description' => 'A regardé 5 animés Video Game.',
                'condition_type' => 'genre_count',
                'condition_value' => 5,
                'metadata' => json_encode(['genre_name' => 'Video Game']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Cavalier du Temps',
                'icon' => 'clock',
                'color' => 'cyan',
                'description' => 'A regardé 5 animés Time Travel.',
                'condition_type' => 'genre_count',
                'condition_value' => 5,
                'metadata' => json_encode(['genre_name' => 'Time Travel']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Commandant Stratégique',
                'icon' => 'shield',
                'color' => 'emerald',
                'description' => 'A regardé 5 animés Military.',
                'condition_type' => 'genre_count',
                'condition_value' => 5,
                'metadata' => json_encode(['genre_name' => 'Military']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Seconde Chance',
                'icon' => 'repeat',
                'color' => 'rose',
                'description' => 'A regardé 5 animés Reincarnation. Un nouveau départ, sous les projecteurs.',
                'condition_type' => 'genre_count',
                'condition_value' => 5,
                'metadata' => json_encode(['genre_name' => 'Reincarnation']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Magie',
                'icon' => 'book-open',
                'color' => 'violet',
                'description' => 'A regardé 10 animés Super Power / Magic.',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Super Power']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Shounen Protagonist',
                'icon' => 'flame',
                'color' => 'orange',
                'description' => 'A regardé 10 animés Shounen.',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Shounen']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Âme Sombre',
                'icon' => 'moon',
                'color' => 'slate',
                'description' => 'A regardé 10 animés Seinen.',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Seinen']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Fleur de Cerisier',
                'icon' => 'flower',
                'color' => 'pink',
                'description' => 'A regardé 5 animés Shoujo.',
                'condition_type' => 'genre_count',
                'condition_value' => 5,
                'metadata' => json_encode(['genre_name' => 'Shoujo']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Héros Mythique',
                'icon' => 'crown',
                'color' => 'yellow',
                'description' => 'A regardé 5 animés Mythology.',
                'condition_type' => 'genre_count',
                'condition_value' => 5,
                'metadata' => json_encode(['genre_name' => 'Mythology']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Aimant à Problèmes',
                'icon' => 'users',
                'color' => 'pink',
                'description' => 'A regardé 5 animés Harem. Comment fais-tu pour toutes les attirer ?',
                'condition_type' => 'genre_count',
                'condition_value' => 5,
                'metadata' => json_encode(['genre_name' => 'Harem']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Bain de Sang',
                'icon' => 'skull',
                'color' => 'red',
                'description' => 'A regardé 5 animés Gore.',
                'condition_type' => 'genre_count',
                'condition_value' => 5,
                'metadata' => json_encode(['genre_name' => 'Gore']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Délégué de Classe',
                'icon' => 'graduation-cap',
                'color' => 'blue',
                'description' => 'A regardé 10 animés School.',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'School']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Culture de l\'Ombre',
                'icon' => 'eye-off',
                'color' => 'rose',
                'description' => 'A regardé 5 animés Ecchi. Un vrai Sneaky Bastard.',
                'condition_type' => 'genre_count',
                'condition_value' => 5,
                'metadata' => json_encode(['genre_name' => 'Ecchi']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Gourmet 3 Étoiles',
                'icon' => 'utensils',
                'color' => 'orange',
                'description' => 'A regardé 5 animés Gourmet.',
                'condition_type' => 'genre_count',
                'condition_value' => 5,
                'metadata' => json_encode(['genre_name' => 'Gourmet']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Athlète Virtuel',
                'icon' => 'trophy',
                'color' => 'emerald',
                'description' => 'A regardé 5 animés Sports.',
                'condition_type' => 'genre_count',
                'condition_value' => 5,
                'metadata' => json_encode(['genre_name' => 'Sports']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Mélomane',
                'icon' => 'music',
                'color' => 'violet',
                'description' => 'A regardé 5 animés Music.',
                'condition_type' => 'genre_count',
                'condition_value' => 5,
                'metadata' => json_encode(['genre_name' => 'Music']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Esprit Abstrait',
                'icon' => 'palette',
                'color' => 'indigo',
                'description' => 'A regardé 3 animés Avant Garde.',
                'condition_type' => 'genre_count',
                'condition_value' => 3,
                'metadata' => json_encode(['genre_name' => 'Avant Garde']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'No-Life',
                'slug' => 'no-life',
                'description' => 'A visionné plus de 1000 heures d\'animés.',
                'icon' => 'clock',
                'color' => 'red',
                'condition_type' => 'special',
                'condition_value' => 60000,
                'metadata' => json_encode(['type' => 'watch_time']),
                'xp_bonus' => 1000
            ],
            [
                'name' => 'No Game No Life',
                'slug' => 'no-game-no-life',
                'description' => 'A visionné 1000 heures d\'animés ET connaît les règles de Disboard.',
                'icon' => 'gamepad-2',
                'color' => 'purple',
                'condition_type' => 'special',
                'condition_value' => 60000,
                'metadata' => json_encode(['type' => 'watch_time_easter_egg']),
                'xp_bonus' => 100
            ],
            [
                'name' => 'Yuri Enjoyer',
                'description' => 'A regardé 10 animés Yuri (Girls Love)',
                'icon' => 'venus',
                'color' => 'rose',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Girls Love']),
                'xp_bonus' => 100
            ]
        ];

        foreach ($genres as $badge) {
            Badge::updateOrCreate(
                ['name' => $badge['name']],
                $badge
            );
        }
    }
}