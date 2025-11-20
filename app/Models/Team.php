<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
