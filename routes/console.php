<?php

use App\Console\Commands\CheckInventoryAlerts;
use App\Console\Commands\ProcessDiscountPlans;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule discount plans processing every minute
Schedule::command(ProcessDiscountPlans::class)->everyMinute();

// Schedule inventory alerts check every hour
Schedule::command(CheckInventoryAlerts::class)->hourly();
