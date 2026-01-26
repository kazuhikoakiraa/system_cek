<?php

namespace App\Filament\Widgets;

use App\Models\PengecekanMesin;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class TrenPengecekanChart extends ChartWidget
{
    protected ?string $heading = 'Tren Pengecekan 7 Hari Terakhir';
    
    protected static ?int $sort = 2;

    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasAnyRole(['super_admin', 'admin']);
    }

    protected function getData(): array
    {
        $data = [];
        $labels = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->translatedFormat('d M');
            
            $count = PengecekanMesin::whereDate('tanggal_pengecekan', '=', $date->format('Y-m-d'))
                ->where('status', 'selesai')
                ->count();
            
            $data[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pengecekan Selesai',
                    'data' => $data,
                    'backgroundColor' => 'rgba(14, 165, 233, 0.2)',
                    'borderColor' => 'rgba(14, 165, 233, 1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }
}
