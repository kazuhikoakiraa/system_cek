<?php

namespace App\Filament\Resources\MaintenanceReports;

use App\Filament\Resources\MaintenanceReports\Pages\CreateMaintenanceReport;
use App\Filament\Resources\MaintenanceReports\Pages\EditMaintenanceReport;
use App\Filament\Resources\MaintenanceReports\Pages\ListMaintenanceReports;
use App\Filament\Resources\MaintenanceReports\Schemas\MaintenanceReportForm;
use App\Filament\Resources\MaintenanceReports\Tables\MaintenanceReportsTable;
use App\Models\MaintenanceReport;
use BackedEnum;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MaintenanceReportResource extends Resource
{
    protected static ?string $model = MaintenanceReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static string|\UnitEnum|null $navigationGroup = 'Maintenance';

    protected static ?string $navigationLabel = 'Laporan Maintenance';

    protected static ?string $pluralModelLabel = 'Laporan Maintenance';

    protected static ?string $modelLabel = 'Laporan Maintenance';

    protected static ?int $navigationSort = 70;

    public static function form(Schema $schema): Schema
    {
        return MaintenanceReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MaintenanceReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMaintenanceReports::route('/'),
            'create' => CreateMaintenanceReport::route('/create'),
            'edit' => EditMaintenanceReport::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        // Semua user yang terautentikasi bisa create maintenance report
        return Auth::check();
    }
}
