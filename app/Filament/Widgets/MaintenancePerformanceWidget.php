<?php

namespace App\Filament\Widgets;

use App\Models\MaintenanceReport;
use App\Models\MLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MaintenancePerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        // Maintenance Reports this month
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $completedThisMonth = MaintenanceReport::where('status', 'completed')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $inProgressThisMonth = MaintenanceReport::where('status', 'in_progress')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        $pendingThisMonth = MaintenanceReport::where('status', 'pending')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        // Average completion time (completed in last 30 days)
        $completedRecent = MaintenanceReport::where('status', 'completed')
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->get();

        $avgCompletionHours = 0;
        if ($completedRecent->count() > 0) {
            $totalHours = $completedRecent->sum(function ($report) {
                return $report->created_at->diffInHours($report->updated_at);
            });
            $avgCompletionHours = round($totalHours / $completedRecent->count(), 1);
        }

        // Log perawatan stats
        $logsThisMonth = MLog::whereBetween('tanggal_mulai', [$startOfMonth, $endOfMonth])->count();
        $logsCompleted = MLog::where('status', 'completed')
            ->whereBetween('tanggal_mulai', [$startOfMonth, $endOfMonth])
            ->count();

        // Spare parts usage this month
        $sparePartsUsed = DB::table('m_log_spare_parts')
            ->join('m_logs', 'm_log_spare_parts.m_log_id', '=', 'm_logs.id')
            ->whereBetween('m_logs.tanggal_mulai', [$startOfMonth, $endOfMonth])
            ->sum('m_log_spare_parts.jumlah_digunakan');

        return [
            Stat::make('Laporan Maintenance Bulan Ini', $completedThisMonth)
                ->description("{$pendingThisMonth} pending, {$inProgressThisMonth} in progress")
                ->descriptionIcon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->chart(array_fill(0, 7, rand(1, 10))),

            Stat::make('Rata-rata Waktu Penyelesaian', $avgCompletionHours . ' jam')
                ->description('30 hari terakhir')
                ->descriptionIcon('heroicon-o-clock')
                ->color($avgCompletionHours < 24 ? 'success' : ($avgCompletionHours < 48 ? 'warning' : 'danger')),

            Stat::make('Log Perawatan Bulan Ini', $logsThisMonth)
                ->description("{$logsCompleted} selesai")
                ->descriptionIcon('heroicon-o-document-text')
                ->color('info')
                ->url('/admin/m-logs'),

            Stat::make('Suku Cadang Digunakan', $sparePartsUsed)
                ->description('Bulan ini')
                ->descriptionIcon('heroicon-o-cube')
                ->color('warning'),
        ];
    }
}
