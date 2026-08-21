<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'provider',
        'country_code',
        'mobile',
        'profile_image',
        'is_onboarded',
        'is_admin',
        'google2fa_secret',
        'google2fa_enabled',
        'two_factor_recovery_codes',
        'current_state_summary',
        'future_self_summary',
        'daily_limit',
        'bonus_chats',
        'feedback_reward_claimed',
        'daily_streak',
        'last_login'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
        'two_factor_recovery_codes',
    ];

    protected $appends = [
        'is_daily_mood_check_in',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'google2fa_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'last_login' => 'datetime',
            'daily_streak' => 'integer',
        ];
    }

    public function scopeDetails($query){
        return $query->select('id', 'name', 'email', 'country_code', 'mobile', 'profile_image', 'is_onboarded', 'daily_streak', 'created_at');
    }

    public function setPasswordAttribute($password)
    {
        if (!empty($password)) {
            $this->attributes['password'] = Hash::make($password);
        }
    }

    public function getProfileImageAttribute($value){
        return checkFileExist($value);
    }

    public function mood(){
        return $this->hasMany(Mood::class);
    }

    public function goals()
    {
        return $this->hasOne(Goals::class);
    }

    /**
     * Get all activity logs for this user.
     */
    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class);
    }

    /**
     * Get all daily active user records for this user.
     */
    public function dailyActiveRecords()
    {
        return $this->hasMany(DailyActiveUser::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    protected function isDailyMoodCheckIn(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->mood()
                ->whereDate('created_at', today())
                ->exists()
        );
    }
}