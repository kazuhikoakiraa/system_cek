<?php

namespace App\Filament\Resources\PengecekanMesins;

use App\Filament\Resources\PengecekanMesins\Pages\CreatePengecekanMesin;
use App\Filament\Resources\PengecekanMesins\Pages\EditPengecekanMesin;
use App\Filament\Resources\PengecekanMesins\Pages\ListPengecekanMesins;
use App\Filament\Resources\PengecekanMesins\Pages\MulaiPengecekan;
use App\Filament\Resources\PengecekanMesins\Pages\ViewPengecekanMesin;
use App\Filament\Resources\PengecekanMesins\Schemas\PengecekanMesinForm;
use App\Filament\Resources\PengecekanMesins\Tables\PengecekanMesinsTable;
use App\Filament\Widgets\StatusPengecekanOverview;
use App\Models\PengecekanMesin;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Facades\Filament;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PengecekanMesinResource extends Resource
{
    protected static ?string $model = PengecekanMesin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Pengecekan';

    protected static ?string $modelLabel = 'Pengecekan';

    protected static ?string $pluralModelLabel = 'Pengecekan';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Pengecekan';

    protected static ?int $navigationSort = 21;

    protected static ?int $navigationGroupSort = 3;

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
            'view' => ViewPengecekanMesin::route('/{record}'),
            'edit' => EditPengecekanMesin::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        // Allow editing only for super_admin and admin roles
        /** @var User|null $user */
        $user = Filament::auth()->user();
        return $user && $user->hasAnyRole(['Super Admin', 'admin']);
    }

    public static function canView($record): bool
    {
        return true;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
