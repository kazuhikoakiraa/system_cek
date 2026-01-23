<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MesinResource\Pages;
use App\Models\Mesin;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Forms\Components\Textarea as FormTextarea;
use Filament\Forms\Components\TextInput as FormTextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MesinResource extends Resource
{
    protected static ?string $model = Mesin::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog;

    protected static ?string $navigationLabel = 'Mesin';

    protected static ?string $pluralModelLabel = 'Mesin';

    protected static ?string $modelLabel = 'Mesin';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Mesin')
                    ->schema([
                        Grid::make(2)
                            ->components([
                                FormTextInput::make('nama_mesin')
                                    ->label('Nama Mesin')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Mesin CNC 001')
                                    ->columnSpan(2),

                                FormSelect::make('user_id')
                                    ->label('Operator yang Bertanggung Jawab')
                                    ->options(function () {
                                        return User::role('operator')->get()->pluck('name', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->helperText('Hanya user dengan role Operator yang dapat dipilih')
                                    ->columnSpan(2),

                                FormTextarea::make('deskripsi')
                                    ->label('Deskripsi Mesin')
                                    ->rows(3)
                                    ->placeholder('Deskripsi umum tentang mesin ini')
                                    ->columnSpan(2),
                            ]),
                    ]),

                Section::make('Komponen & Pengecekan')
                    ->schema([
                        Repeater::make('komponenMesins')
                            ->relationship()
                            ->label('Daftar Komponen')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        FormTextInput::make('nama_komponen')
                                            ->label('Nama Komponen')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Contoh: Motor Penggerak, Bearing, dll'),

                                        FormSelect::make('frekuensi')
                                            ->label('Frekuensi Pengecekan')
                                            ->options([
                                                'harian' => 'Harian',
                                                'mingguan' => 'Mingguan',
                                                'bulanan' => 'Bulanan',
                                                'tahunan' => 'Tahunan',
                                            ])
                                            ->required()
                                            ->native(false),

                                        FormTextInput::make('standar')
                                            ->label('Standar Pengecekan')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Contoh: Tekanan 5-7 bar, Suhu maksimal 80°C')
                                            ->columnSpan(2),

                                        FormTextarea::make('catatan')
                                            ->label('Catatan')
                                            ->rows(2)
                                            ->placeholder('Catatan tambahan untuk komponen ini')
                                            ->columnSpan(2),
                                    ]),
                            ])
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['nama_komponen'] ?? null)
                            ->addActionLabel('Tambah Komponen')
                            ->reorderable()
                            ->collapsible()
                            ->defaultItems(1)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_mesin')
                    ->label('Nama Mesin')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('operator.name')
                    ->label('Operator')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('komponenMesins.nama_komponen')
                    ->label('Komponen')
                    ->badge()
                    ->separator(',')
                    ->limit(50),

                TextColumn::make('komponenMesins_count')
                    ->counts('komponenMesins')
                    ->label('Jumlah Komponen')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Operator')
                    ->relationship('operator', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListMesins::route('/'),
            'create' => Pages\CreateMesin::route('/create'),
            'view' => Pages\ViewMesin::route('/{record}'),
            'edit' => Pages\EditMesin::route('/{record}/edit'),
        ];
    }
}
