<?php

namespace App\Models\Traits;

use App\Models\Badge;

trait HasGamification
{
    public function getLevelTitleAttribute()
    {
        $level = $this->level;

        return match (true) {
            $level >= 75 => 'S.T.U. (Souverain Titanesque Universel)',
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
        $this->load(['animes.genres', 'badges']);

        $ownedBadgeSlugs = $this->badges->pluck('slug')->toArray();

        $watchedAnimes = $this->animes->filter(fn($a) => $a->pivot->progress > 0);

        $totalMinutes = $watchedAnimes->sum(function ($anime) {
            $duration = $anime->duration ?? 24;
            return $anime->pivot->progress * $duration;
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

        $rules = [
            'no-life' => $totalMinutes >= 60000,
            'drama-queen' => $genreCounts['Drama'] >= 5,
            'shonen-jumper' => $genreCounts['Shounen'] >= 20,
            'romcom-enjoyer' => $genreCounts['Romance'] >= 10,
        ];

        foreach ($rules as $slug => $isEligible) {
            $hasBadge = in_array($slug, $ownedBadgeSlugs);

            if ($isEligible && !$hasBadge) {
                $this->unlockBadge($slug);
            } elseif (!$isEligible && $hasBadge) {
                $this->revokeBadge($slug);
            }
        }
    }

    private function unlockBadge($slug)
    {
        $badge = Badge::where('slug', $slug)->first();
        if ($badge && !$this->badges()->where('badge_id', $badge->id)->exists()) {
            $this->badges()->attach($badge->id);
        }
    }

    public function revokeBadge($slug)
    {
        $badge = Badge::where('slug', $slug)->first();

        if ($badge && $this->badges->contains($badge->id)) {
            $this->badges()->detach($badge->id);

            $currentErrors = session()->get('error', []);
            if (!is_array($currentErrors))
                $currentErrors = [$currentErrors];

            session()->flash('error', "Badge PERDU : {$badge->name} (Condition plus remplie).");
        }
    }
}