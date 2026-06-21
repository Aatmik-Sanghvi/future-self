<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Struggles extends Model
{
    protected $fillable = [
        'goal_id',
        'struggle',
        'category',
        'severity'
    ];

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function store($data){
        return self::create($data);
    }
}
