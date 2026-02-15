<?php

namespace App\Filament\Widgets;

use App\Models\MRequest;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MaintenanceRequestTrendChart extends ChartWidget
{
    protected ?string $heading = '📈 Trend Permintaan Maintenance (30 Hari Terakhir)';

    protected static ?int $sort = 8;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(29);

        $dates = collect();
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dates->push($currentDate->format('Y-m-d'));
            $currentDate->addDay();
        }

        // Get requests grouped by date and urgency
        $requests = MRequest::whereBetween('requested_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($request) {
                return $request->requested_at->format('Y-m-d');
            });

        // Prepare data arrays
        $criticalData = [];
        $highData = [];
        $mediumData = [];
        $lowData = [];
        $labels = [];

        foreach ($dates as $date) {
            $dateRequests = $requests->get($date, collect());
            
            $labels[] = Carbon::parse($date)->format('d/m');
            $criticalData[] = $dateRequests->where('urgency_level', 'critical')->count();
            $highData[] = $dateRequests->where('urgency_level', 'high')->count();
            $mediumData[] = $dateRequests->where('urgency_level', 'medium')->count();
            $lowData[] = $dateRequests->where('urgency_level', 'low')->count();
        }

        return [
            'datasets' => [
                [
                    'label' => '🔴 Critical',
                    'data' => $criticalData,
                    'borderColor' => 'rgb(220, 38, 38)',
                    'backgroundColor' => 'rgba(220, 38, 38, 0.1)',
                    'tension' => 0.3,
                ],
                [
                    'label' => '🟠 High',
                    'data' => $highData,
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'tension' => 0.3,
                ],
                [
                    'label' => '🟡 Medium',
                    'data' => $mediumData,
                    'borderColor' => 'rgb(234, 179, 8)',
                    'backgroundColor' => 'rgba(234, 179, 8, 0.1)',
                    'tension' => 0.3,
                ],
                [
                    'label' => '🟢 Low',
                    'data' => $lowData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'tension' => 0.3,
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
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
        ];
    }
}
