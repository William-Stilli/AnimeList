<?php

namespace App\Models\Traits;

use App\Models\Badge;

trait HasGamification
{
    public function getLevelTitleAttribute()
    {
        $level = $this->level;

        return match (true) {
            $level >= 50 => 'Divinité',
            $level >= 40 => 'Sage',
            $level >= 25 => 'Otaku',
            $level >= 10 => 'Weeb',
            default => 'Novice',
        };
    }

    public function getLevelAttribute()
    {
        if ($this->xp <= 0)
            return 1;
        return floor(sqrt($this->xp / 5));
    }

    public function getNextLevelXpAttribute()
    {
        $nextLevel = $this->level + 1;
        return 5 * ($nextLevel ** 2);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'badge_user')->withPivot('unlocked_at');
    }

    public function gainXp(int $amount)
    {
        $oldLevelTitle = $this->level_title;
        $this->increment('xp', $amount);

        return $this->level_title !== $oldLevelTitle;
    }

    public function checkAchievements($currentAnime = null)
    {
        $this->loadMissing('animes.genres');

        $animes = $this->animes;

        $totalMinutes = $animes->sum(function ($anime) {
            $duration = $anime->duration ?? 24;
            return $anime->pivot->progress * $duration;
        });

        if ($totalMinutes >= 60000) {
            $this->unlockBadge('no-life');
        }

        $watchedAnimes = $animes->filter(function ($anime) {
            return $anime->pivot->progress > 0;
        });

        $genreCounts = [
            'Drama' => 0,
            'Shounen' => 0,
            'Romance' => 0,
        ];

        foreach ($watchedAnimes as $anime) {
            foreach ($anime->genres as $genre) {
                foreach ($genreCounts as $key => $count) {
                    if (stripos($genre->name, $key) !== false) {
                        $genreCounts[$key]++;
                    }
                }
            }
        }

        if ($genreCounts['Drama'] >= 5) {
            $this->unlockBadge('drama-queen');
        }

        if ($genreCounts['Shounen'] >= 20) {
            $this->unlockBadge('shonen-jumper');
        }

        if ($genreCounts['Romance'] >= 10) {
            $this->unlockBadge('romcom-enjoyer');
        }
    }

    private function unlockBadge($slug)
    {
        $badge = Badge::where('slug', $slug)->first();
        if ($badge && !$this->badges()->where('badge_id', $badge->id)->exists()) {
            $this->badges()->attach($badge->id);
        }
    }
}