<?php

namespace App\Filament\Resources\PengecekanMesins;

use App\Filament\Resources\PengecekanMesins\Pages\CreatePengecekanMesin;
use App\Filament\Resources\PengecekanMesins\Pages\EditPengecekanMesin;
use App\Filament\Resources\PengecekanMesins\Pages\ListPengecekanMesins;
use App\Filament\Resources\PengecekanMesins\Pages\MulaiPengecekan;
use App\Filament\Resources\PengecekanMesins\Schemas\PengecekanMesinForm;
use App\Filament\Resources\PengecekanMesins\Tables\PengecekanMesinsTable;
use App\Filament\Widgets\StatusPengecekanOverview;
use App\Models\PengecekanMesin;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PengecekanMesinResource extends Resource
{
    protected static ?string $model = PengecekanMesin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Pengecekan Mesin';

    protected static ?string $modelLabel = 'Pengecekan Mesin';

    protected static ?string $pluralModelLabel = 'Pengecekan Mesin';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Mesin';

    protected static ?int $navigationSort = 21;

    public static function form(Schema $schema): Schema
    {
        return PengecekanMesinForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PengecekanMesinsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            StatusPengecekanOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengecekanMesins::route('/'),
            'mulai' => MulaiPengecekan::route('/mulai'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
