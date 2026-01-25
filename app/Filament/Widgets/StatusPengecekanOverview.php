<?php

namespace App\Filament\Widgets;

use App\Models\Mesin;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatusPengecekanOverview extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $totalMesin = Mesin::count();
        
        $sudahDicek = Mesin::whereHas('pengecekan', function ($query) {
            $query->whereDate('tanggal_pengecekan', today())
                ->where('status', 'selesai');
        })->count();

        $sedangDicek = Mesin::whereHas('pengecekan', function ($query) {
            $query->whereDate('tanggal_pengecekan', today())
                ->where('status', 'dalam_proses');
        })->count();

        $belumDicek = $totalMesin - $sudahDicek - $sedangDicek;

        $persentaseSelesai = $totalMesin > 0 
            ? round(($sudahDicek / $totalMesin) * 100, 1) 
            : 0;

        return [
            Stat::make('Total Mesin', $totalMesin)
                ->description('Jumlah seluruh mesin')
                ->descriptionIcon('heroicon-o-server')
                ->color('primary'),

            Stat::make('Sudah Dicek', $sudahDicek)
                ->description("$persentaseSelesai% selesai")
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Sedang Dicek', $sedangDicek)
                ->description('Proses berlangsung')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Belum Dicek', $belumDicek)
                ->description('Perlu dicek hari ini')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }

    public function getDisplayName(): string
    {
        return 'Status Pengecekan Hari Ini - ' . now()->translatedFormat('d F Y');
    }
}
