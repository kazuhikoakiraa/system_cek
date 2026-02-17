<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AdminGreetingWidget;
use App\Filament\Widgets\DashboardAccountWidget;
use App\Filament\Widgets\MachineMaintenanceAlert;
use App\Filament\Widgets\MachineStatsOverview;
use App\Filament\Widgets\MachineStatusChart;
use App\Filament\Widgets\MaintenancePerformanceWidget;
use App\Filament\Widgets\OperatorReminderWidget;
use App\Filament\Widgets\RecentMaintenanceActivity;
use App\Filament\Widgets\SparePartsInventoryWidget;
use App\Filament\Widgets\StatusPengecekanOverview;
use App\Filament\Widgets\TeknisiReminderWidget;
use App\Filament\Widgets\TrenPengecekanChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            AdminGreetingWidget::class,
            DashboardAccountWidget::class,
            OperatorReminderWidget::class,
            TeknisiReminderWidget::class,
            StatusPengecekanOverview::class,
            MachineStatsOverview::class,
            MaintenancePerformanceWidget::class,
            SparePartsInventoryWidget::class,
            TrenPengecekanChart::class,
            MachineStatusChart::class,
            MachineMaintenanceAlert::class,
            RecentMaintenanceActivity::class,
        ];
    }

    public function getColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 4,
        ];
    }
}
