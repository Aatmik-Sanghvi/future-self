<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyMission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'goal_id',
        'mission_date',
        'title',
        'description',
        'future_self_note',
        'category',
        'mood_type',
        'estimated_minutes',
        'difficulty',
        'status',
        'completed_at',
        'reflection',
        'morning_mail_sent_at',
        'reminder_mail_sent_at',
    ];

    protected $casts = [
        'mission_date' => 'date:Y-m-d',
        'completed_at' => 'datetime',
        'morning_mail_sent_at' => 'datetime',
        'reminder_mail_sent_at' => 'datetime',
        'estimated_minutes' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function goal()
    {
        return $this->belongsTo(Goals::class, 'goal_id');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('mission_date', today());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
