<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingTier extends Model
{
    protected $fillable = [
        'name',
        'min_credits',
        'price_per_credit',
        'is_active',
    ];

    protected $casts = [
        'price_per_credit' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function topUpRequests(): HasMany
    {
        return $this->hasMany(TopUpRequest::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
