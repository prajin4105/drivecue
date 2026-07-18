<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Models\CronSetting;

try {
    $settings = CronSetting::where('is_active', true)->get();
    foreach ($settings as $setting) {
        Schedule::command($setting->command)->cron($setting->schedule);
    }
} catch (\Exception $e) {
    // Fallback if DB not migrated
    Schedule::command('app:send-auto-reminders')->dailyAt('09:00');
}
