<?php

namespace App\Filament\Resources\MLogs;

use App\Filament\Resources\MLogs\Pages;
use App\Filament\Resources\MLogs\Schemas\MLogForm;
use App\Filament\Resources\MLogs\Schemas\MLogInfolist;
use App\Filament\Resources\MLogs\Tables\MLogsTable;
use App\Models\MLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MLogResource extends Resource
{
    protected static ?string $model = MLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Log Perawatan';

    protected static ?string $modelLabel = 'Log Perawatan';

    protected static ?string $pluralModelLabel = 'Log Perawatan';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Mesin';

    protected static ?int $navigationSort = 3;

    protected static ?int $navigationGroupSort = 2;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return MLogForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MLogsTable::configure($table);
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
            'index' => Pages\ListMLogs::route('/'),
            'create' => Pages\CreateMLog::route('/create'),
            'view' => Pages\ViewMLog::route('/{record}'),
            'edit' => Pages\EditMLog::route('/{record}/edit'),
        ];
    }
}
