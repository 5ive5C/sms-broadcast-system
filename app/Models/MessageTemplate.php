<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageTemplate extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'body',
        'placeholders',
        'parts',
        'last_used_at',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'last_used_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'template_id');
    }

    /**
     * Pull out {placeholder} tokens from a template body, in first-seen
     * order, deduplicated. "phone"/"mobile" are recipient columns, not
     * message placeholders, so they're excluded.
     *
     * @return array<int, string>
     */
    public static function extractPlaceholders(string $body): array
    {
        preg_match_all('/\{(\w+)\}/', $body, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }
}
