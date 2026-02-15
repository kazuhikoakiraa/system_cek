<?php

namespace App\Filament\Widgets;

use App\Models\Mesin;
use App\Models\MComponent;
use App\Models\MRequest;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MachineStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Total mesin
        $totalMesins = Mesin::count();
        $activeMesins = Mesin::where('status', 'aktif')->count();
        $maintenanceMesins = Mesin::where('status', 'maintenance')->count();
        $brokenMesins = Mesin::where('status', 'rusak')->count();

        // Komponen perlu ganti
        $componentNeedReplacement = MComponent::where('status_komponen', 'perlu_ganti')
            ->orWhere('status_komponen', 'rusak')
            ->count();

        $componentOverdue = MComponent::whereNotNull('estimasi_tanggal_ganti_berikutnya')
            ->where('estimasi_tanggal_ganti_berikutnya', '<', Carbon::now())
            ->count();

        // Request maintenance pending
        $pendingRequests = MRequest::where('status', 'pending_approval')->count();
        $inProgressRequests = MRequest::where('status', 'in_progress')->count();

        // Mesin mendekati penggantian (30 hari)
        $machinesNearReplacement = Mesin::whereNotNull('estimasi_penggantian')
            ->whereBetween('estimasi_penggantian', [Carbon::now(), Carbon::now()->addDays(30)])
            ->count();

        return [
            Stat::make('Total Mesin', $totalMesins)
                ->description("{$activeMesins} aktif, {$maintenanceMesins} maintenance, {$brokenMesins} rusak")
                ->descriptionIcon('heroicon-o-cog')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3])
                ->url('/admin/mesins'),

            Stat::make('Komponen Perlu Ganti', $componentNeedReplacement)
                ->description("{$componentOverdue} sudah terlambat")
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($componentNeedReplacement > 0 ? 'warning' : 'success')
                ->chart([3, 5, 8, 10, 7, 9, 12, 10]),

            Stat::make('Request Maintenance', $pendingRequests + $inProgressRequests)
                ->description("{$pendingRequests} pending, {$inProgressRequests} in progress")
                ->descriptionIcon('heroicon-o-wrench')
                ->color($pendingRequests > 5 ? 'danger' : 'info')
                ->chart([2, 4, 3, 5, 6, 4, 3, 5])
                ->url('/admin/m-requests'),

            Stat::make('Mesin Perlu Evaluasi', $machinesNearReplacement)
                ->description('Mendekati akhir umur ekonomis (30 hari)')
                ->descriptionIcon('heroicon-o-calendar')
                ->color($machinesNearReplacement > 0 ? 'warning' : 'success'),
        ];
    }
}
