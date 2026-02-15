<?php

namespace App\Filament\Widgets;

use App\Models\Mesin;
use Filament\Widgets\ChartWidget;

class MachineStatusChart extends ChartWidget
{
    protected ?string $heading = '🏭 Status Mesin';

    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = ['md' => 1, 'xl' => 1];

    protected function getData(): array
    {
        $aktif = Mesin::where('status', 'aktif')->count();
        $nonaktif = Mesin::where('status', 'nonaktif')->count();
        $maintenance = Mesin::where('status', 'maintenance')->count();
        $rusak = Mesin::where('status', 'rusak')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Status Mesin',
                    'data' => [$aktif, $nonaktif, $maintenance, $rusak],
                    'backgroundColor' => [
                        'rgb(34, 197, 94)',  // Green for aktif
                        'rgb(156, 163, 175)', // Gray for nonaktif
                        'rgb(245, 158, 11)',  // Orange for maintenance
                        'rgb(239, 68, 68)',   // Red for rusak
                    ],
                ],
            ],
            'labels' => ['Aktif', 'Non-Aktif', 'Maintenance', 'Rusak'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
