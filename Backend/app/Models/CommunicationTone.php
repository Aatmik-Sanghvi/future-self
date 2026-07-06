<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationTone extends Model
{
    protected $fillable = [
        'goal_id',
        'tone',
    ];

    public function scopeDetails($query)
    {
        return $query->select(['id', 'goal_id', 'tone']);
    }

    public function goal()
    {
        return $this->belongsTo(Goals::class);
    }

    public function store($data)
    {
        // Upsert: one tone per goal
        return self::updateOrCreate(
            ['goal_id' => $data['goal_id']],
            ['tone' => $data['tone']]
        );
    }

    public function getTone($goal_id)
    {
        return $this->where('goal_id', $goal_id)->Details()->first();
    }

    public function removeDetail($id)
    {
        return $this->find($id)->delete();
    }
}
