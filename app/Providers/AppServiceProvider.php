<?php

namespace App\Providers;

use App\Models\DaftarPengecekan;
use App\Models\DetailPengecekanMesin;
use App\Models\MaintenanceReport;
use App\Models\Mesin;
use App\Models\SparePartTransaction;
use App\Models\SparePartStockOpname;
use App\Observers\DaftarPengecekanObserver;
use App\Observers\DetailPengecekanMesinObserver;
use App\Observers\MaintenanceReportObserver;
use App\Observers\MesinObserver;
use App\Observers\SparePartTransactionObserver;
use App\Observers\SparePartStockOpnameObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DaftarPengecekan::observe(DaftarPengecekanObserver::class);
        Mesin::observe(MesinObserver::class);
        MaintenanceReport::observe(MaintenanceReportObserver::class);
        DetailPengecekanMesin::observe(DetailPengecekanMesinObserver::class);
        SparePartTransaction::observe(SparePartTransactionObserver::class);
        SparePartStockOpname::observe(SparePartStockOpnameObserver::class);
    }
}
