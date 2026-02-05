<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'xp_bonus',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'badge_user')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }
}