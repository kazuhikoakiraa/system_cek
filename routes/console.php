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
| CATATAN: Command pengecekan:generate-daily sudah tidak diperlukan lagi
| karena status "tidak dicek" sekarang sama dengan "tidak ada data pengecekan"
| Schedule dibawah ini bisa dihapus atau dinonaktifkan
|
*/
// Schedule::command('pengecekan:generate-daily')
//     ->dailyAt('23:55')
//     ->timezone('Asia/Jakarta')
//     ->withoutOverlapping()
//     ->onOneServer()
//     ->appendOutputTo(storage_path('logs/pengecekan-scheduler.log'));
