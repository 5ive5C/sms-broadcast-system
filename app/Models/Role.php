<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    /**
     * The full permission catalogue offered on the role editor (Screen 06).
     * '*' (super-admin) is deliberately excluded — it's granted directly,
     * never picked from this checklist.
     */
    const PERMISSIONS = [
        'campaigns.manage' => 'Launch campaigns',
        'recipients.upload' => 'Upload recipient lists',
        'quick-send.manage' => 'Quick send',
        'reports.view' => 'View reports',
        'api-keys.manage' => 'Manage API keys',
        'templates.manage' => 'Manage templates',
        'top-ups.request' => 'Request top-ups',
        'wallet.manage' => 'Manage wallet',
        'users.manage' => 'Manage users',
    ];

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

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->whereNull('client_id');
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        return in_array('*', $permissions, true) || in_array($permission, $permissions, true);
    }
}
