<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyActiveUser extends Model
{
    protected $fillable = [
        'user_id',
        'active_date',
        'first_seen_at',
        'last_seen_at',
        'request_count',
    ];

    protected function casts(): array
    {
        return [
            'active_date' => 'date',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns this daily active record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->where('active_date', '>=', $from);
        }
        if ($to) {
            $query->where('active_date', '<=', $to);
        }
        return $query;
    }
}
