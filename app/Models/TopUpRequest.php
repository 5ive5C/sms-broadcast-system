<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class TopUpRequest extends Model
{
    protected $fillable = [
        'client_id',
        'pricing_tier_id',
        'credits',
        'price_per_credit',
        'total_amount',
        'status',
        'approved_by',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'price_per_credit' => 'decimal:4',
        'total_amount' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function pricingTier(): BelongsTo
    {
        return $this->belongsTo(PricingTier::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Approve the top-up and credit the client's wallet atomically.
     */
    public function approve(User $admin): void
    {
        DB::transaction(function () use ($admin) {
            $wallet = Wallet::where('client_id', $this->client_id)->lockForUpdate()->firstOrFail();
            $wallet->increment('balance', $this->credits);

            $this->forceFill([
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ])->save();
        });
    }

    public function reject(User $admin, ?string $notes = null): void
    {
        $this->forceFill([
            'status' => 'rejected',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'notes' => $notes ?? $this->notes,
        ])->save();
    }
}
