<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReconciliationRecord extends Model
{
    protected $fillable = [
        'reconciliation_run_id',
        'message_id',
        'client_id',
        'our_status',
        'isms_status',
        'resolution',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(ReconciliationRun::class, 'reconciliation_run_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
