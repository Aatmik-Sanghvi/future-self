<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fear extends Model
{
    protected $fillable = [
        'goal_id',
        'fear',
        'category',
        'priority'
    ];

    public function scopeDetails($query){
        return $query->select(['id', 'goal_id', 'fear', 'category', 'priority']);
    }

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function store($data){
        return self::create($data);
    }

    public function getFears($goal_id){
        return $this->where('goal_id', $goal_id)->Details()->first();
    }
}
