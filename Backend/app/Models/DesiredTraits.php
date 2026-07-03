<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesiredTraits extends Model
{
    protected $fillable = [
        'goal_id',
        'trait',
    ];

    public function scopeDetails($query){
        return $query->select(['id', 'goal_id', 'trait']);
    }

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

    public function getDesiredTraits($goal_id){
        return $this->where('goal_id', $goal_id)->Details()->get();
    }

    public function removeDetail($id){
        return $this->find($id)->delete();
    }
}
