<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'client_id',
        'template_id',
        'created_by',
        'launched_by',
        'name',
        'content',
        'lane',
        'status',
        'recipient_source',
        'recipient_file_name',
        'pending_recipients',
        'rejected_rows',
        'merge_mapping',
        'recipient_count',
        'rejected_count',
        'delivered_count',
        'submitted_count',
        'failed_count',
        'credits_required',
        'credits_spent',
        'scheduled_at',
        'launched_at',
    ];

    protected $casts = [
        'pending_recipients' => 'array',
        'rejected_rows' => 'array',
        'merge_mapping' => 'array',
        'scheduled_at' => 'datetime',
        'launched_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function launcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'launched_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function progressPercent(): int
    {
        if ($this->recipient_count === 0) {
            return 0;
        }

        $dispatched = $this->delivered_count + $this->submitted_count + $this->failed_count;

        return (int) round(($dispatched / $this->recipient_count) * 100);
    }
}
