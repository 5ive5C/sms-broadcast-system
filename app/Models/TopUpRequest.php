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
        'slip_path',
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
     * Approve the top-up: credits the wallet, posts a ledger entry and an
     * invoice, and records the approval, all atomically.
     */
    public function approve(User $admin): void
    {
        DB::transaction(function () use ($admin) {
            $wallet = Wallet::where('client_id', $this->client_id)->lockForUpdate()->firstOrFail();

            WalletLedgerEntry::post(
                $wallet,
                'topup',
                'Top-up approved — '.$this->reference(),
                (float) $this->credits,
                $this,
            );

            $this->forceFill([
                'status' => 'approved',
                'approved_by' => $admin->id,
                'approved_at' => now(),
            ])->save();

            Invoice::create([
                'client_id' => $this->client_id,
                'top_up_request_id' => $this->id,
                'invoice_no' => Invoice::nextInvoiceNo(),
                'invoice_date' => now()->toDateString(),
                'credits' => $this->credits,
                'price_per_credit' => $this->price_per_credit,
                'amount' => $this->total_amount,
                'sst' => 0,
                'total' => $this->total_amount,
                'status' => 'unpaid',
            ]);
        });

        AuditLog::record($admin, 'Top-up approved', $this->reference().' · +'.number_format($this->credits).' credits', $this->client);
    }

    /**
     * Deterministic reference string, used before persistence-driven IDs
     * are meaningful to a human (matches the TOP-YYYY-NNNN shown on the
     * slides).
     */
    public function reference(): string
    {
        return 'TOP-'.$this->created_at?->format('Y').'-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function reject(User $admin, ?string $notes = null): void
    {
        $this->forceFill([
            'status' => 'rejected',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'notes' => $notes ?? $this->notes,
        ])->save();

        AuditLog::record($admin, 'Top-up rejected', $this->reference(), $this->client);
    }
}
