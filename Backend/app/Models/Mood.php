<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mood extends Model
{
    protected $fillable = [
        'user_id',
        'mood_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store($data){
        $mood = $this->create([
            'user_id' => auth()->id(),
            'mood_type' => $data['mood_type']
        ]);

        return $mood;
    }
}
