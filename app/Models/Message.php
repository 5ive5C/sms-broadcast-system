<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Message extends Model
{
    protected $fillable = [
        'code',
        'client_id',
        'campaign_id',
        'api_key_id',
        'lane',
        'recipient',
        'content',
        'encoding',
        'parts',
        'status',
        'credit_charged',
        'credit_refunded',
        'failure_reason',
        'isms_status',
        'submitted_at',
        'scheduled_for',
        'queued_at',
        'final_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'queued_at' => 'datetime',
        'final_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Message $message) {
            $message->code ??= 'msg_'.Str::lower(Str::random(6));
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(MessageAttempt::class);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    /**
     * Rows inserted (by the portal or the API) but not yet handed to the
     * gateway job — what the dispatch-pending poller claims.
     */
    public function scopeAwaitingDispatch(Builder $query): Builder
    {
        return $query->where('status', 'submitted')
            ->whereNull('queued_at')
            ->where(fn (Builder $q) => $q->whereNull('scheduled_for')->orWhere('scheduled_for', '<=', now()));
    }

    public function isFinal(): bool
    {
        return $this->status !== 'submitted';
    }

    /**
     * Whether iSMS's own report disagrees with our recorded status — the
     * discrepancy reconciliation (Screen 28) is built to surface.
     */
    public function hasReconciliationMismatch(): bool
    {
        return $this->isms_status !== null && $this->isms_status !== $this->status;
    }

    /**
     * Mask a phone number for display, e.g. +60123456781 -> +60123••••81
     * (matches the message log screens).
     */
    public function maskedRecipient(): string
    {
        $number = $this->recipient;

        if (strlen($number) <= 6) {
            return $number;
        }

        return substr($number, 0, -6).'••••'.substr($number, -2);
    }

    /**
     * Encoding + SMS part count for a message body — same GSM-7 detection
     * and segment math the composer's live counter uses client-side,
     * computed again here since credits are priced off it.
     *
     * @return array{encoding: string, parts: int}
     */
    public static function smsParts(string $content): array
    {
        $gsm7Charset = '@£$¥èéùìòÇ'.\chr(10).'Øø'.\chr(13).'ÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !"#¤%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà';
        $isGsm7 = true;

        foreach (mb_str_split($content) as $char) {
            if (! str_contains($gsm7Charset, $char)) {
                $isGsm7 = false;
                break;
            }
        }

        $length = mb_strlen($content);
        $single = $isGsm7 ? 160 : 70;
        $multi = $isGsm7 ? 153 : 67;
        $parts = $length === 0 ? 0 : ($length <= $single ? 1 : (int) ceil($length / $multi));

        return ['encoding' => $isGsm7 ? 'gsm7' : 'unicode', 'parts' => max($parts, 1)];
    }
}
