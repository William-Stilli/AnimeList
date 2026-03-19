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
        'color',
        'xp_bonus',
        'condition_type',
        'condition_value',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'badge_user')
            ->withPivot('unlocked_at');
    }
}