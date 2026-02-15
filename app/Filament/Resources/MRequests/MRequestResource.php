<?php

namespace App\Filament\Resources\MRequests;

use App\Filament\Resources\MRequests\Pages;
use App\Filament\Resources\MRequests\Schemas\MRequestForm;
use App\Filament\Resources\MRequests\Schemas\MRequestInfolist;
use App\Filament\Resources\MRequests\Tables\MRequestsTable;
use App\Models\MRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MRequestResource extends Resource
{
    protected static ?string $model = MRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Permintaan Maintenance';

    protected static ?string $modelLabel = 'Permintaan Maintenance';

    protected static ?string $pluralModelLabel = 'Permintaan Maintenance';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Mesin';

    protected static ?int $navigationSort = 2;

    protected static ?int $navigationGroupSort = 2;

    protected static ?string $recordTitleAttribute = 'request_number';

    public static function form(Schema $schema): Schema
    {
        return MRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MRequestsTable::configure($table);
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
            'index' => Pages\ListMRequests::route('/'),
            'create' => Pages\CreateMRequest::route('/create'),
            'view' => Pages\ViewMRequest::route('/{record}'),
            'edit' => Pages\EditMRequest::route('/{record}/edit'),
        ];
    }
}
