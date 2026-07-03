<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goals extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'timeframe',
        'priority',
        'status',
    ];

    public function scopeDetails($query){
        return $query->select(['id', 'user_id','title','description', 'category', 'timeframe', 'priority', 'status']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store($data){
        $data['user_id'] = auth()->id();
        return self::create($data);
    }

    public function getGoals(){
        return $this->where('user_id', auth()->id())->Details()->first();
    }

    public function removeDetail($id){
        return $this->find($id)->delete();
    }
}
