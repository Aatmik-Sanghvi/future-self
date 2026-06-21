<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesiredTraits extends Model
{
    protected $fillable = [
        'goal_id',
        'trait',
    ];

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function store($data){
        $traits = [];
        foreach($data['traits'] as $trait){
            $traits[] = self::create([
                'goal_id' => $data['goal_id'],
                'trait' => $trait
            ]);
        }
        return $traits;
    }
}
