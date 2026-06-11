<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Envoie chaque jour les rappels de don dont la date (reminder_at) est atteinte.
Schedule::command('reminders:send')->dailyAt('09:00');
