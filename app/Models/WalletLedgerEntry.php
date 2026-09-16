<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WalletLedgerEntry extends Model
{
    protected $fillable = [
        'client_id',
        'wallet_id',
        'type',
        'description',
        'change',
        'balance_after',
        'related_type',
        'related_id',
    ];

    protected $casts = [
        'change' => 'decimal:4',
        'balance_after' => 'decimal:4',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Post an entry and update the wallet balance atomically. Callers are
     * expected to already hold a lock on the wallet row when balances need
     * to be consistent under concurrent writes (see TopUpRequest::approve).
     */
    public static function post(Wallet $wallet, string $type, string $description, float $change, ?Model $related = null): self
    {
        $wallet->increment('balance', $change);
        $wallet->refresh();

        return static::create([
            'client_id' => $wallet->client_id,
            'wallet_id' => $wallet->id,
            'type' => $type,
            'description' => $description,
            'change' => $change,
            'balance_after' => $wallet->balance,
            'related_type' => $related ? $related::class : null,
            'related_id' => $related?->id,
        ]);
    }
}
