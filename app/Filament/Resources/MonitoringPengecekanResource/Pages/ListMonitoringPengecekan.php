<?php

namespace App\Filament\Resources\MonitoringPengecekanResource\Pages;

use App\Filament\Resources\MonitoringPengecekanResource;
use App\Filament\Widgets\StatusPengecekanOverview;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListMonitoringPengecekan extends ListRecords
{
    protected static string $resource = MonitoringPengecekanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatusPengecekanOverview::class,
        ];
    }

    public function getTitle(): string
    {
        return 'Monitoring Pengecekan Mesin - ' . now()->translatedFormat('d F Y');
    }

    public function getHeading(): string
    {
        return 'Monitoring Pengecekan Mesin';
    }

    public function getSubheading(): ?string
    {
        return 'Daftar status pengecekan mesin untuk hari ini: ' . now()->translatedFormat('l, d F Y');
    }
}
