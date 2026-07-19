<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'helpful_rating',
        'is_personal',
        'understood_level',
        'would_use_again',
        'use_frequency',
        'most_valuable',
        'confused_or_disappointed',
        'feature_to_come_back',
        'monthly_price_willingness',
        'subscription_convincer',
        'nps_score',
        'one_thing_to_change',
        'is_skipped',
    ];

    protected $casts = [
        'is_skipped' => 'boolean',
        'helpful_rating' => 'integer',
        'nps_score' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
