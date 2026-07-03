<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleModel extends Model
{
    protected $fillable = [
        'goal_id',
        'names',
    ];

    public function scopeDetails($query){
        return $query->select(['id', 'goal_id', 'names']);
    }

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    public function store($data){
        $roleModels = [];
        foreach($data['names'] as $name){
            $roleModels[] = self::create([
                'goal_id' => $data['goal_id'],
                'names' => $name
            ]);
        }
        return $roleModels;
    }

    public function getRoleModels($goal_id){
        return $this->where('goal_id', $goal_id)->Details()->get();
    }

    public function removeDetail($id){
        return $this->find($id)->delete();
    }
}
