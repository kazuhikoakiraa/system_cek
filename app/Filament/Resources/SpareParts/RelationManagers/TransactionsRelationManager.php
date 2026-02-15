<?php

namespace App\Filament\Resources\SpareParts\RelationManagers;

use App\Models\SparePartTransaction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'Riwayat Transaksi';

    protected static ?string $label = 'Transaksi';

    protected static ?string $pluralLabel = 'Transaksi';

    protected static ?string $recordTitleAttribute = 'nomor_transaksi';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('tipe_transaksi')
                    ->label('Tipe Transaksi')
                    ->options([
                        'IN' => 'Masuk',
                        'OUT' => 'Keluar',
                        'RETURN' => 'Retur',
                    ])
                    ->required()
                    ->reactive(),

                DateTimePicker::make('tanggal_transaksi')
                    ->label('Tanggal & Waktu Transaksi')
                    ->default(now())
                    ->required()
                    ->native(false),

                TextInput::make('jumlah')
                    ->label('Jumlah')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->helperText('Jumlah suku cadang yang ditransaksikan'),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(3)
                    ->placeholder('Contoh: Untuk perbaikan mesin A, Retur dari supplier, dll')
                    ->columnSpanFull(),

                FileUpload::make('dokumen')
                    ->label('Dokumen Pendukung')
                    ->directory('spare-part-transactions')
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->maxSize(5120)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_transaksi')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('tipe_transaksi')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'IN' => 'Masuk',
                        'OUT' => 'Keluar',
                        'RETURN' => 'Retur',
                        default => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'IN' => 'success',
                        'OUT' => 'danger',
                        'RETURN' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('tanggal_transaksi')
                    ->label('Tanggal & Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->numeric()
                    ->suffix(fn (Model $record) => ' ' . $record->sparePart->satuan)
                    ->alignEnd(),

                TextColumn::make('stok_sebelum')
                    ->label('Stok Sebelum')
                    ->numeric()
                    ->suffix(fn (Model $record) => ' ' . $record->sparePart->satuan)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('stok_sesudah')
                    ->label('Stok Sesudah')
                    ->numeric()
                    ->suffix(fn (Model $record) => ' ' . $record->sparePart->satuan)
                    ->badge()
                    ->color(function (Model $record) {
                        $sparePart = $record->sparePart;
                        if ($record->stok_sesudah <= 0) return 'danger';
                        if ($record->stok_sesudah <= $sparePart->stok_minimum) return 'warning';
                        return 'success';
                    }),

                TextColumn::make('user.name')
                    ->label('Diinput Oleh')
                    ->toggleable(),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->toggleable()
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_transaksi', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat'),
                EditAction::make()
                    ->label('Ubah')
                    ->visible(fn (Model $record) => $record->created_at->diffInHours(now()) < 24),
                DeleteAction::make()
                    ->label('Hapus')
                    ->visible(fn (Model $record) => Auth::user()?->hasRole('super_admin')),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Transaksi')
                    ->mutateFormDataUsing(function (array $data, RelationManager $livewire): array {
                        $sparePart = $livewire->getOwnerRecord();
                        
                        $data['spare_part_id'] = $sparePart->id;
                        $data['user_id'] = Auth::id();
                        $data['stok_sebelum'] = $sparePart->stok;
                        
                        // Hitung stok sesudah
                        if ($data['tipe_transaksi'] === 'IN' || $data['tipe_transaksi'] === 'RETURN') {
                            $data['stok_sesudah'] = $sparePart->stok + $data['jumlah'];
                        } else {
                            $data['stok_sesudah'] = $sparePart->stok - $data['jumlah'];
                        }
                        
                        return $data;
                    })
                    ->after(function (Model $record, RelationManager $livewire) {
                        // Update stok di spare part
                        $sparePart = $livewire->getOwnerRecord();
                        $sparePart->update(['stok' => $record->stok_sesudah]);
                    }),
            ]);
    }
}
