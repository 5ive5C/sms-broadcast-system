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
        'status',
        'pricing_tier_id',
        'company_reg_no',
        'address',
        'pic_name',
        'pic_phone',
        'pic_email',
        'industry',
        'message_types',
        'two_factor_required',
    ];

    protected $casts = [
        'message_types' => 'array',
        'two_factor_required' => 'boolean',
    ];

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function messageTemplates(): HasMany
    {
        return $this->hasMany(MessageTemplate::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function walletLedgerEntries(): HasMany
    {
        return $this->hasMany(WalletLedgerEntry::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function pricingTier(): BelongsTo
    {
        return $this->belongsTo(PricingTier::class);
    }

    public function topUpRequests(): HasMany
    {
        return $this->hasMany(TopUpRequest::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
