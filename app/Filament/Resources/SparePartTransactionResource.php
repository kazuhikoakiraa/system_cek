<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SpareParts\Pages;
use App\Models\SparePart;
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
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SparePartTransactionResource extends Resource
{
    protected static ?string $model = SparePartTransaction::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $recordTitleAttribute = 'nomor_transaksi';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Suku Cadang';

    protected static ?string $navigationLabel = 'Laporan Suku Cadang';

    protected static ?string $pluralModelLabel = 'Laporan Suku Cadang';

    protected static ?string $modelLabel = 'Transaksi';

    protected static ?int $navigationSort = 73;

    protected static ?int $navigationGroupSort = 5;
    
    // Read-only resource untuk laporan
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('spare_part_id')
                    ->label('Suku Cadang')
                    ->options(SparePart::where('status', 'active')->orderBy('nama_suku_cadang')->pluck('nama_suku_cadang', 'id'))
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $sparePart = SparePart::find($state);
                            if ($sparePart) {
                                $set('stok_sebelum', $sparePart->stok);
                            }
                        }
                    })
                    ->helperText(fn ($state) => $state ? 'Stok saat ini: ' . SparePart::find($state)?->stok . ' ' . SparePart::find($state)?->satuan : ''),

                Select::make('tipe_transaksi')
                    ->label('Tipe Transaksi')
                    ->options([
                        'IN' => 'Masuk',
                        'OUT' => 'Keluar',
                        'RETURN' => 'Retur',
                    ])
                    ->required()
                    ->reactive()
                    ->columnSpanFull(),

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
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $sparePartId = $get('spare_part_id');
                        $tipeTransaksi = $get('tipe_transaksi');
                        
                        if ($sparePartId && $state && $tipeTransaksi) {
                            $sparePart = SparePart::find($sparePartId);
                            if ($sparePart) {
                                $stokSebelum = $sparePart->stok;
                                $set('stok_sebelum', $stokSebelum);
                                
                                if ($tipeTransaksi === 'IN' || $tipeTransaksi === 'RETURN') {
                                    $set('stok_sesudah', $stokSebelum + $state);
                                } else {
                                    $set('stok_sesudah', $stokSebelum - $state);
                                }
                            }
                        }
                    })
                    ->helperText('Jumlah suku cadang yang ditransaksikan'),

                TextInput::make('stok_sebelum')
                    ->label('Stok Sebelum')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),

                TextInput::make('stok_sesudah')
                    ->label('Stok Sesudah')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(),

                Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->rows(3)
                    ->placeholder('Contoh: Untuk perbaikan mesin A, Retur dari supplier, Pembelian baru, dll')
                    ->columnSpanFull(),

                FileUpload::make('dokumen')
                    ->label('Dokumen Pendukung')
                    ->directory('spare-part-transactions')
                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                    ->maxSize(5120)
                    ->helperText('Upload bukti transaksi (nota, surat jalan, dll)')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_transaksi')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('sparePart.kode_suku_cadang')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('sparePart.nama_suku_cadang')
                    ->label('Nama Suku Cadang')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(40),

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
                    ->alignEnd()
                    ->weight('bold'),

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
                    ->toggleable()
                    ->searchable(),

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
            ->filters([
                SelectFilter::make('tipe_transaksi')
                    ->label('Tipe Transaksi')
                    ->options([
                        'IN' => 'Masuk',
                        'OUT' => 'Keluar',
                        'RETURN' => 'Retur',
                    ]),

                SelectFilter::make('spare_part_id')
                    ->label('Suku Cadang')
                    ->relationship('sparePart', 'nama_suku_cadang')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('tanggal_transaksi')
                    ->form([
                        DateTimePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        DateTimePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari_tanggal'], fn ($query, $date) => $query->whereDate('tanggal_transaksi', '>=', $date))
                            ->when($data['sampai_tanggal'], fn ($query, $date) => $query->whereDate('tanggal_transaksi', '<=', $date));
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Detail'),
            ])
            ->emptyStateHeading('Belum Ada Transaksi')
            ->emptyStateDescription('Transaksi akan muncul saat Anda menambah/mengurangi stok dari menu Suku Cadang')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSparePartTransactions::route('/'),
            'view' => Pages\ViewSparePartTransaction::route('/{record}'),
        ];
    }
}
