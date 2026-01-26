<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LaporanPengecekanSummary extends BaseWidget
{
    /**
     * @var array{total_mesin?: int, total_pengecekan?: int, total_sesuai?: int, total_tidak_sesuai?: int}
     */
    public array $summary = [];

    public function mount(array $summary = []): void
    {
        $this->summary = $summary;
    }

    public static function canView(): bool
    {
        // Widget ini hanya ditampilkan di halaman laporan, tidak di dashboard
        return false;
    }

    protected function getStats(): array
    {
        $totalMesin = (int) ($this->summary['total_mesin'] ?? 0);
        $totalPengecekan = (int) ($this->summary['total_pengecekan'] ?? 0);
        $totalSesuai = (int) ($this->summary['total_sesuai'] ?? 0);
        $totalTidakSesuai = (int) ($this->summary['total_tidak_sesuai'] ?? 0);

        return [
            Stat::make('Total Mesin', $totalMesin)
                ->color('primary'),

            Stat::make('Total Pengecekan', $totalPengecekan)
                ->color('gray'),

            Stat::make('Sesuai (OK)', $totalSesuai)
                ->color('success'),

            Stat::make('Tidak Sesuai (NG)', $totalTidakSesuai)
                ->color('danger'),
        ];
    }

    protected function getColumns(): int | array | null
    {
        return [
            '@xl' => 4,
            '@lg' => 4,
            '!@lg' => 2,
        ];
    }
}
