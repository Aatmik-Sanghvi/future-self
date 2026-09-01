<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 8:00 AM Morning Daily Mission Email Dispatch
Schedule::command('missions:send-morning')->dailyAt('08:00')->timezone(config('app.timezone'));

// Hourly Mission Reminder Check for pending missions at user-selected reminder times
Schedule::command('missions:send-reminders')->everyMinute()->timezone(config('app.timezone'));
