<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserEmailLog extends Model
{
    protected $table = 'user_email_logs';

    protected $fillable = [
        'email',
        'otp',
        'otp_expires_at',
    ];

    protected $casts = [
        'otp_expires_at' => 'datetime',
    ];
}
