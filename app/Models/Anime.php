<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anime extends Model
{
    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany(User::class, 'anime_user')
            ->withPivot('status', 'score', 'progress')
            ->withTimestamps();
    }
}
