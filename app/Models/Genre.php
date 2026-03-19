<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Genre extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'mal_id'];

    // public function genres()
    // {
    //     return $this->belongsToMany(Genre::class, 'anime_genre');
    // }

    public function animes()
    {
        return $this->belongsToMany(Anime::class, 'anime_genre');
    }
}
