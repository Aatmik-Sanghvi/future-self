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
        'is_pro',
        'pro_expires_at',
        'subscription_status',
        'trial_ends_at',
        'has_used_trial',
        'razorpay_subscription_id',
        'google2fa_secret',
        'google2fa_enabled',
        'two_factor_recovery_codes',
        'current_state_summary',
        'future_self_summary',
        'daily_limit',
        'bonus_chats',
        'feedback_reward_claimed',
        'daily_streak',
        'last_login',
        'mission_email_enabled',
        'mission_reminder_time',
        'mission_reminder_enabled',
        'last_mission_completed_at',
        'last_feedback_reminder_sent_at',
        'last_motivational_mail_sent_at',
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
            'is_pro' => 'boolean',
            'pro_expires_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'has_used_trial' => 'boolean',
            'google2fa_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'last_login' => 'datetime',
            'daily_streak' => 'integer',
            'mission_email_enabled' => 'boolean',
            'mission_reminder_enabled' => 'boolean',
            'last_mission_completed_at' => 'datetime',
            'last_feedback_reminder_sent_at' => 'datetime',
            'last_motivational_mail_sent_at' => 'datetime',
        ];
    }

    public function scopeDetails($query){
        return $query->select(
            'id', 'name', 'email', 'country_code', 'mobile', 'profile_image',
            'is_onboarded', 'daily_streak', 'is_pro', 'pro_expires_at',
            'subscription_status', 'trial_ends_at', 'has_used_trial', 'created_at'
        );
    }

    /**
     * Scope a query to only include users with an active Pro subscription or 7-day trial.
     */
    public function scopeActivePro($query)
    {
        return $query->where('is_pro', true)
            ->where(function ($q) {
                $q->whereNull('subscription_status')
                  ->orWhere('subscription_status', '!=', 'cancelled');
            })
            ->where(function ($q) {
                $q->whereNull('pro_expires_at')
                  ->orWhere('pro_expires_at', '>', now());
            });
    }

    /**
     * Check if the user currently has an active Pro subscription or 7-day free trial.
     */
    public function hasActivePro(): bool
    {
        if (! $this->is_pro) {
            return false;
        }

        if ($this->subscription_status === 'cancelled') {
            return false;
        }

        if ($this->pro_expires_at && $this->pro_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
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
        return $this->hasMany(Goals::class);
    }

    public function activeGoal()
    {
        return $this->hasOne(Goals::class)->where('status', 'active')->latestOfMany();
    }

    public function dailyMissions()
    {
        return $this->hasMany(DailyMission::class);
    }

    /**
     * Get all activity logs for this user.
     */
    public function activityLogs()
    {
        return $this->hasMany(UserActivityLog::class);
    }

    /**
     * Get all feedback submissions for this user.
     */
    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
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

    /**
     * Calculate consecutive days of completed daily missions.
     */
    public function calculateDailyStreak(): int
    {
        $completedDates = $this->dailyMissions()
            ->where('status', 'completed')
            ->orderByDesc('mission_date')
            ->pluck('mission_date')
            ->map(function ($date) {
                return $date instanceof \Carbon\CarbonInterface
                    ? $date->toDateString()
                    : \Carbon\Carbon::parse($date)->toDateString();
            })
            ->unique()
            ->values();

        if ($completedDates->isEmpty()) {
            return 0;
        }

        $todayStr = today()->toDateString();
        $yesterdayStr = today()->subDay()->toDateString();

        $streak = 0;
        $checkDate = today();

        if ($completedDates->contains($todayStr)) {
            $checkDate = today();
        } elseif ($completedDates->contains($yesterdayStr)) {
            $checkDate = today()->subDay();
        } else {
            return 0;
        }

        while ($completedDates->contains($checkDate->toDateString())) {
            $streak++;
            $checkDate = $checkDate->subDay();
        }

        return $streak;
    }

    /**
     * Sync and persist the calculated daily streak for the user.
     */
    public function syncDailyStreak(): int
    {
        $streak = $this->calculateDailyStreak();
        if ($this->daily_streak !== $streak) {
            $this->update(['daily_streak' => $streak]);
        }
        return $streak;
    }
}