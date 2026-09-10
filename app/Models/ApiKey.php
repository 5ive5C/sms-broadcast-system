<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'key',
        'secret',
        'ip_whitelist',
    ];

    protected $hidden = [
        'secret',
    ];

    protected $casts = [
        'ip_whitelist' => 'array',
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at');
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function revoke(): void
    {
        $this->forceFill(['revoked_at' => now()])->save();
    }

    /**
     * Generate a new key/secret pair. Returns the plain secret alongside the
     * model — the plain value is only ever available at creation time.
     */
    public static function generate(Client $client, string $name): array
    {
        $plainSecret = Str::random(40);

        $apiKey = static::create([
            'client_id' => $client->id,
            'name' => $name,
            'key' => 'sk_'.Str::random(32),
            'secret' => Hash::make($plainSecret),
        ]);

        return [$apiKey, $plainSecret];
    }

    public function verifySecret(string $plainSecret): bool
    {
        return Hash::check($plainSecret, $this->secret);
    }
}
