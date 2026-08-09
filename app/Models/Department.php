<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    protected $fillable = ['name', 'code', 'hod_id', 'status'];

    public function hod(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hod_id');
    }

    public function nbaCoordinator(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class)->where('role', 'nba_coordinator');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function faculty(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'faculty');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }
}
