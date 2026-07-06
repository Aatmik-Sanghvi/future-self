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
        $existingNames = self::where('goal_id', $data['goal_id'])
            ->pluck('names')
            ->toArray();

        $newNames = array_diff($data['names'], $existingNames);

        $records = [];

        foreach ($newNames as $name) {
            $records[] = [
                'goal_id' => $data['goal_id'],
                'names' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($records)) {
            self::insert($records);
        }

        return $records;
    }

    public function getRoleModels($goal_id){
        return $this->where('goal_id', $goal_id)->Details()->get();
    }

    public function removeDetail($id){
        return $this->find($id)->delete();
    }
}
