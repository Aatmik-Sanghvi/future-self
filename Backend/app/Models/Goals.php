<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goals extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'timeframe',
        'priority',
        'status',
        'target_value',
        'current_value',
        'unit',
    ];

    protected $casts = [
        'priority' => 'integer',
        'target_value' => 'float',
        'current_value' => 'float',
    ];

    public function scopeDetails($query)
    {
        return $query->select([
            'id',
            'user_id',
            'title',
            'description',
            'category',
            'timeframe',
            'priority',
            'status',
            'target_value',
            'current_value',
            'unit',
            'created_at',
            'updated_at',
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dailyMissions()
    {
        return $this->hasMany(DailyMission::class, 'goal_id');
    }

    public function isMeasurable(): bool
    {
        return !is_null($this->target_value) && $this->target_value > 0;
    }

    public function getTargetProgress(): ?array
    {
        if (!$this->isMeasurable()) {
            return null;
        }

        $target = (float) $this->target_value;
        $current = (float) ($this->current_value ?? 0);
        $percentage = $target > 0 ? min(100.0, round(($current / $target) * 100, 1)) : 0.0;

        return [
            'is_measurable' => true,
            'target_value' => $target,
            'current_value' => $current,
            'unit' => $this->unit ?? '',
            'percentage' => $percentage,
            'is_target_reached' => $current >= $target,
        ];
    }

    public function store($data)
    {
        $data['user_id'] = auth()->id();
        return self::create($data);
    }

    public function getGoals()
    {
        return $this->where('user_id', auth()->id())->Details()->first();
    }

    public function removeDetail($id)
    {
        return $this->find($id)?->delete();
    }
}

