<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReconciliationRun extends Model
{
    protected $fillable = [
        'run_date',
        'our_records',
        'isms_report',
        'matched_pct',
        'discrepancy_count',
    ];

    protected $casts = [
        'run_date' => 'date',
        'matched_pct' => 'decimal:2',
    ];

    public function records(): HasMany
    {
        return $this->hasMany(ReconciliationRecord::class);
    }
}
