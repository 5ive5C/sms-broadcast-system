<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'client_id',
        'top_up_request_id',
        'invoice_no',
        'invoice_date',
        'credits',
        'price_per_credit',
        'amount',
        'sst',
        'total',
        'status',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'price_per_credit' => 'decimal:4',
        'amount' => 'decimal:4',
        'sst' => 'decimal:4',
        'total' => 'decimal:4',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function topUpRequest(): BelongsTo
    {
        return $this->belongsTo(TopUpRequest::class);
    }

    public static function nextInvoiceNo(): string
    {
        $year = now()->year;
        $sequence = static::query()->whereYear('created_at', $year)->count() + 1;

        return sprintf('INV-%d-%04d', $year, $sequence);
    }
}
