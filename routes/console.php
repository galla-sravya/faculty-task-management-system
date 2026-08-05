<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reminders:send-deadline')->everyThirtyMinutes();
Schedule::command('reminders:send-deadline-day', ['morning'])->dailyAt('09:00')->timezone('Asia/Kolkata');
Schedule::command('reminders:send-deadline-day', ['evening'])->dailyAt('18:00')->timezone('Asia/Kolkata');
