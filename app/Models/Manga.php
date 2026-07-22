<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Manga extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mal_id',
        'title',
        'image_url',
        'chapters_read',
        'volumes_owned',
        'status',
        'score',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}