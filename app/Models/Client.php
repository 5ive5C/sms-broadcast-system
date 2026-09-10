<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sender_id',
        'status',
        'pricing_tier_id',
    ];

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function pricingTier(): BelongsTo
    {
        return $this->belongsTo(PricingTier::class);
    }

    public function topUpRequests(): HasMany
    {
        return $this->hasMany(TopUpRequest::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
