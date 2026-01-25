<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| Generate record pengecekan untuk mesin yang tidak dicek setiap hari
| Berjalan setiap pukul 23:55 untuk menutup hari tersebut
|
*/
Schedule::command('pengecekan:generate-daily')
    ->dailyAt('23:55')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/pengecekan-scheduler.log'));
