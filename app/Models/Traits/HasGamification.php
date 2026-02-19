<?php

namespace App\Models\Traits;

use App\Models\Badge;

trait HasGamification
{
    public function getLevelTitleAttribute()
    {
        $level = $this->level;

        return match (true) {
            $level >= 100 => 'S.T.U. (Souverain Titanesque Universel)',
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
        $completedAnimes = $this->animes()
            ->wherePivot('status', 'completed')
            ->with('genres')
            ->get();

        $uniqueFranchises = [];

        foreach ($completedAnimes as $anime) {
            $title = $anime->title_english ?? $anime->title;

            $cleanTitle = preg_replace('/(:? Season \d+|:? \d+(st|nd|rd|th) Season|:? Part \d+|:? The Final Season|:? Next Shine)/i', '', $title);
            $cleanTitle = preg_replace('/(\s-.*)/', '', $cleanTitle);
            $cleanTitle = preg_replace('/(\s[IVX]+)$/', '', $cleanTitle);
            $cleanTitle = trim($cleanTitle);

            foreach ($anime->genres as $genre) {
                $uniqueFranchises[$genre->name][$cleanTitle] = true;
            }
        }

        $userGenreCounts = [];
        foreach ($uniqueFranchises as $genreName => $franchises) {
            $userGenreCounts[$genreName] = count($franchises);
        }

        $allWatchedAnimes = $this->animes()->get()->filter(function ($anime) {
            return $anime->pivot->progress > 0 || $anime->pivot->status === 'completed';
        });

        $totalMinutes = $allWatchedAnimes->sum(function ($anime) {
            $progress = $anime->pivot->progress;
            if ($anime->pivot->status === 'completed' && $progress == 0) {
                $progress = $anime->episodes ?: 1;
            }
            $duration = ($anime->duration > 0) ? $anime->duration : 24;
            return $progress * $duration;
        });

        $hasWatchedNGNL = $allWatchedAnimes->contains(function ($anime) {
            $titleEn = $anime->title_english ?? '';
            $titleJp = $anime->title ?? '';

            $checkTitle = function ($t) {
                return stripos($t, 'No Game') !== false && stripos($t, 'No Life') !== false;
            };

            return $checkTitle($titleEn) || $checkTitle($titleJp);
        });

        $allBadges = Badge::all();
        $ownedBadgeIds = $this->badges()->pluck('badges.id')->toArray();

        foreach ($allBadges as $badge) {
            $isEligible = false;

            if ($badge->condition_type === 'genre_count') {
                $meta = json_decode($badge->metadata, true);
                $targetGenre = $meta['genre_name'] ?? null;

                if ($targetGenre && isset($userGenreCounts[$targetGenre])) {
                    if ($userGenreCounts[$targetGenre] >= $badge->condition_value) {
                        $isEligible = true;
                    }
                }
            } else {
                $lowerName = strtolower($badge->name);
                $isNoLifeBadge = ($lowerName === 'no-life' || $lowerName === 'no life');
                $isNGNLBadge = ($lowerName === 'no game no life');

                if ($isNoLifeBadge || $isNGNLBadge) {

                    $hasEnoughTime = $totalMinutes >= 60000;

                    if ($hasEnoughTime) {
                        if ($hasWatchedNGNL && $isNGNLBadge) {
                            $isEligible = true;
                        } elseif (!$hasWatchedNGNL && $isNoLifeBadge) {
                            $isEligible = true;
                        }
                    }
                }
            }

            $hasBadge = in_array($badge->id, $ownedBadgeIds);

            if ($isEligible && !$hasBadge) {
                $this->badges()->attach($badge->id, ['unlocked_at' => now()]);
                if (!app()->runningInConsole()) {
                    session()->push('success', "Nouveau Badge débloqué : {$badge->name} !");
                }
            } elseif (!$isEligible && $hasBadge) {
                $this->badges()->detach($badge->id);
                if (!app()->runningInConsole()) {
                    session()->push('info', "Badge perdu : {$badge->name}");
                }
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