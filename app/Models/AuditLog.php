<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'actor_name',
        'client_id',
        'action',
        'detail',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Record an audit entry. $actor is null for system-originated events
     * (e.g. automated refunds), in which case the entry is stamped
     * "system" and not tied to any particular internal/client user.
     */
    public static function record(?User $actor, string $action, ?string $detail = null, ?Client $client = null): self
    {
        return static::create([
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name ?? 'system',
            'client_id' => $client?->id ?? $actor?->client_id,
            'action' => $action,
            'detail' => $detail,
        ]);
    }
}
