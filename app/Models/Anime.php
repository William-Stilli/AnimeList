<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anime extends Model
{

    use HasFactory;
    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany(User::class, 'anime_user')
            ->withPivot('status', 'score', 'progress', 'review', 'is_stu', 'rank')
            ->withTimestamps();
    }

    public function getDisplayTitleAttribute()
    {
        return $this->title_english ?: $this->title;
    }
    protected $appends = ['display_title'];

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'anime_genre');
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class)->withTimestamps();
    }
}
