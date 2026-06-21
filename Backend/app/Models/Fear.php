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

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function store($data){
        return self::create($data);
    }
}
