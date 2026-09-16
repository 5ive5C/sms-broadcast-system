<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageAttempt extends Model
{
    protected $fillable = [
        'message_id',
        'sequence',
        'attempted_at',
        'result',
    ];

    protected $casts = [
        'attempted_at' => 'datetime',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
