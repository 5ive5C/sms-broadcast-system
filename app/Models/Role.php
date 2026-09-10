<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'slug',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function portalUsers(): HasMany
    {
        return $this->hasMany(PortalUser::class);
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('client_id');
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true);
    }
}
