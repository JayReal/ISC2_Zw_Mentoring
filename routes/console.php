<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => Cache::put('operations.scheduler_last_run', now()->toIso8601String(), now()->addMinutes(10)))
    ->name('operations-heartbeat')->everyMinute();
Schedule::command('queue:work --queue=default --sleep=1 --tries=3 --timeout=90 --max-time=240 --stop-when-empty')
    ->name('drain-notification-queue')->everyMinute()->withoutOverlapping(5);
Schedule::command('app:send-mentoring-reminders')->dailyAt('08:00')->timezone('Africa/Harare')->withoutOverlapping();
Schedule::command('queue:prune-failed --hours=720')->dailyAt('02:00')->timezone('Africa/Harare')->withoutOverlapping();
