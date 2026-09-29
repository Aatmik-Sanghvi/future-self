<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 8:00 AM Morning Daily Mission Email Dispatch
Schedule::command('missions:send-morning')->dailyAt('08:00')->timezone(config('app.timezone'));

// 9:00 AM Motivational Email Dispatch for users inactive 3+ days (runs after morning missions)
Schedule::command('emails:send-motivational')->dailyAt('09:00')->timezone(config('app.timezone'));

// 10:00 AM Feedback Reminder Email Dispatch (every 2 days, enforced by command logic)
Schedule::command('emails:send-feedback-reminders')->dailyAt('10:00')->timezone(config('app.timezone'));

// Hourly Mission Reminder Check for pending missions at user-selected reminder times
Schedule::command('missions:send-reminders')->everyThirtyMinutes()->timezone(config('app.timezone'));

