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
                'name' => 'Expert Action',
                'icon' => 'sword',
                'color' => 'red',
                'description' => 'A regardé 50 animés de type Action',
                'condition_type' => 'genre_count',
                'condition_value' => 50,
                'metadata' => json_encode(['genre_name' => 'Action'])
            ],
            [
                'name' => 'Explorateur (Adventure)',
                'icon' => 'map',
                'color' => 'green',
                'description' => 'A regardé 30 animés de type Adventure',
                'condition_type' => 'genre_count',
                'condition_value' => 30,
                'metadata' => json_encode(['genre_name' => 'Adventure'])
            ],
            [
                'name' => 'Avant-Garde (Avant Garde)',
                'icon' => 'eye',
                'color' => 'indigo',
                'description' => 'A regardé 10 animés expérimentaux',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Avant Garde'])
            ],
            [
                'name' => 'Clown de Classe (Comedy)',
                'icon' => 'laugh',
                'color' => 'yellow',
                'description' => 'A regardé 50 animés de type Comedy',
                'condition_type' => 'genre_count',
                'condition_value' => 50,
                'metadata' => json_encode(['genre_name' => 'Comedy'])
            ],
            [
                'name' => 'Cœur Brisé (Drama)',
                'icon' => 'heart-crack',
                'color' => 'slate',
                'description' => 'A regardé 40 animés de type Drama',
                'condition_type' => 'genre_count',
                'condition_value' => 40,
                'metadata' => json_encode(['genre_name' => 'Drama'])
            ],
            [
                'name' => 'Monde Magique (Fantasy)',
                'icon' => 'wand-2',
                'color' => 'purple',
                'description' => 'A regardé 50 animés de type Fantasy',
                'condition_type' => 'genre_count',
                'condition_value' => 50,
                'metadata' => json_encode(['genre_name' => 'Fantasy'])
            ],
            [
                'name' => 'Frissons (Horror)',
                'icon' => 'ghost',
                'color' => 'zinc',
                'description' => 'A regardé 20 animés de type Horror',
                'condition_type' => 'genre_count',
                'condition_value' => 20,
                'metadata' => json_encode(['genre_name' => 'Horror'])
            ],
            [
                'name' => 'Enquêteur (Mystery)',
                'icon' => 'search',
                'color' => 'blue',
                'description' => 'A regardé 30 animés de type Mystery',
                'condition_type' => 'genre_count',
                'condition_value' => 30,
                'metadata' => json_encode(['genre_name' => 'Mystery'])
            ],
            [
                'name' => 'Romantique (Romance)',
                'icon' => 'heart',
                'color' => 'pink',
                'description' => 'A regardé 40 animés de type Romance',
                'condition_type' => 'genre_count',
                'condition_value' => 40,
                'metadata' => json_encode(['genre_name' => 'Romance'])
            ],
            [
                'name' => 'Futuriste (Sci-Fi)',
                'icon' => 'rocket',
                'color' => 'cyan',
                'description' => 'A regardé 40 animés de type Sci-Fi',
                'condition_type' => 'genre_count',
                'condition_value' => 40,
                'metadata' => json_encode(['genre_name' => 'Sci-Fi'])
            ],
            [
                'name' => 'Tranche de Vie (Slice of Life)',
                'icon' => 'coffee',
                'color' => 'orange',
                'description' => 'A regardé 40 animés de type Slice of Life',
                'condition_type' => 'genre_count',
                'condition_value' => 40,
                'metadata' => json_encode(['genre_name' => 'Slice of Life'])
            ],
            [
                'name' => 'Athlète (Sports)',
                'icon' => 'trophy',
                'color' => 'emerald',
                'description' => 'A regardé 30 animés de type Sports',
                'condition_type' => 'genre_count',
                'condition_value' => 30,
                'metadata' => json_encode(['genre_name' => 'Sports'])
            ],
            [
                'name' => 'Surnaturel (Supernatural)',
                'icon' => 'sparkles',
                'color' => 'violet',
                'description' => 'A regardé 40 animés de type Supernatural',
                'condition_type' => 'genre_count',
                'condition_value' => 40,
                'metadata' => json_encode(['genre_name' => 'Supernatural'])
            ],
            [
                'name' => 'Esprit Shonen',
                'icon' => 'flame',
                'color' => 'red',
                'description' => 'A regardé 50 animés de type Shounen',
                'condition_type' => 'genre_count',
                'condition_value' => 50,
                'metadata' => json_encode(['genre_name' => 'Shounen'])
            ],
            [
                'name' => 'Seinen Enjoyer',
                'icon' => 'briefcase',
                'color' => 'stone',
                'description' => 'A regardé 30 animés de type Seinen',
                'condition_type' => 'genre_count',
                'condition_value' => 30,
                'metadata' => json_encode(['genre_name' => 'Seinen'])
            ],
            [
                'name' => 'Shojo Fan',
                'icon' => 'star',
                'color' => 'rose',
                'description' => 'A regardé 30 animés de type Shoujo',
                'condition_type' => 'genre_count',
                'condition_value' => 30,
                'metadata' => json_encode(['genre_name' => 'Shoujo'])
            ],
            [
                'name' => 'Homme de Culture (Ecchi)',
                'icon' => 'camera',
                'color' => 'fuchsia',
                'description' => 'A regardé 20 animés de type Ecchi',
                'condition_type' => 'genre_count',
                'condition_value' => 20,
                'metadata' => json_encode(['genre_name' => 'Ecchi'])
            ],
            [
                'name' => 'Sneaky Bastard',
                'icon' => 'venetian-mask',
                'color' => 'stone',
                'description' => 'A regardé 10 animés de type Erotica',
                'condition_type' => 'genre_count',
                'condition_value' => 10,
                'metadata' => json_encode(['genre_name' => 'Erotica'])
            ],
            [
                'name' => 'No-Life',
                'slug' => 'no-life',
                'description' => 'A visionné plus de 1000 heures d\'animés.',
                'icon' => 'clock',
                'color' => 'red',
                'condition_type' => 'special',
                'condition_value' => 60000,
                'metadata' => json_encode(['type' => 'watch_time'])
            ],
            [
                'name' => 'No Game No Life',
                'slug' => 'no-game-no-life',
                'description' => 'A visionné 1000 heures d\'animés ET connaît les règles de Disboard. L\'élite.',
                'icon' => 'gamepad-2',
                'color' => 'purple',
                'condition_type' => 'special',
                'condition_value' => 60000,
                'metadata' => json_encode(['type' => 'watch_time_easter_egg'])
            ],
        ];

        foreach ($genres as $badge) {
            Badge::updateOrCreate(
                ['name' => $badge['name']],
                $badge
            );
        }
    }
}