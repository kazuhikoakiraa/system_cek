<?php

namespace App\Filament\Resources\SpareParts\Tables;

use App\Models\SparePartTransaction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SparePartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder-spare-part.png'))
                    ->toggleable(),

                TextColumn::make('kode_suku_cadang')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('nama_suku_cadang')
                    ->label('Nama Suku Cadang')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('category.nama_kategori')
                    ->label('Kategori')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('stok')
                    ->label('Stok')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(function ($record) {
                        if ($record->stok <= 0) return 'danger';
                        if ($record->stok <= $record->stok_minimum) return 'warning';
                        if ($record->stok >= $record->stok_maksimum) return 'info';
                        return 'success';
                    })
                    ->formatStateUsing(fn ($state, $record) => $state . ' ' . $record->satuan),

                TextColumn::make('stok_minimum')
                    ->label('Min')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state, $record) => $state . ' ' . $record->satuan),

                TextColumn::make('stok_maksimum')
                    ->label('Max')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state, $record) => $state . ' ' . $record->satuan),

                TextColumn::make('lokasi_penyimpanan')
                    ->label('Lokasi')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('harga_satuan')
                    ->label('Harga Satuan')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('nilai_stok')
                    ->label('Nilai Total')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable()
                    ->getStateUsing(fn ($record) => $record->nilai_stok),

                TextColumn::make('supplier')
                    ->label('Pemasok')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tahun_pengadaan')
                    ->label('Tahun')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tanggal_warranty_expired')
                    ->label('Garansi Berakhir')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->color(function ($record) {
                        if (!$record->tanggal_warranty_expired) return null;
                        if ($record->isWarrantyActive()) {
                            if ($record->isWarrantyExpiringSoon()) {
                                return 'warning';
                            }
                            return 'success';
                        }
                        return 'danger';
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                        default => $state,
                    })
                    ->color(fn ($state) => match($state) {
                        'active' => 'success',
                        'inactive' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'nama_kategori')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'inactive' => 'Tidak Aktif',
                    ]),

                SelectFilter::make('status_stok')
                    ->label('Status Stok')
                    ->options([
                        'out_of_stock' => 'Habis',
                        'low_stock' => 'Stok Rendah',
                        'normal' => 'Normal',
                        'over_stock' => 'Over Stock',
                    ])
                    ->query(function ($query, array $data) {
                        if (!$data['value']) return $query;
                        
                        return match($data['value']) {
                            'out_of_stock' => $query->where('stok', '<=', 0),
                            'low_stock' => $query->whereRaw('stok > 0 AND stok <= stok_minimum'),
                            'normal' => $query->whereRaw('stok > stok_minimum AND stok < stok_maksimum'),
                            'over_stock' => $query->whereRaw('stok >= stok_maksimum'),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat'),
                EditAction::make()
                    ->label('Ubah'),
                Action::make('tambah_stok')
                    ->label('Tambah Stok')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->form([
                        TextInput::make('jumlah')
                            ->label('Jumlah Masuk')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->helperText(fn ($record) => 'Stok saat ini: ' . $record->stok . ' ' . $record->satuan),

                        DateTimePicker::make('tanggal_transaksi')
                            ->label('Tanggal & Waktu')
                            ->default(now())
                            ->required()
                            ->native(false),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(2)
                            ->placeholder('Contoh: Pembelian baru, Retur dari supplier, dll'),
                    ])
                    ->action(function ($record, array $data) {
                        $stokSebelum = $record->stok;
                        $stokSesudah = $stokSebelum + $data['jumlah'];

                        // Buat transaksi
                        SparePartTransaction::create([
                            'spare_part_id' => $record->id,
                            'tipe_transaksi' => 'IN',
                            'tanggal_transaksi' => $data['tanggal_transaksi'],
                            'user_id' => Auth::id(),
                            'jumlah' => $data['jumlah'],
                            'stok_sebelum' => $stokSebelum,
                            'stok_sesudah' => $stokSesudah,
                            'keterangan' => $data['keterangan'] ?? 'Penambahan stok',
                        ]);

                        // Update stok
                        $record->update(['stok' => $stokSesudah]);

                        Notification::make()
                            ->success()
                            ->title('Stok berhasil ditambahkan')
                            ->body("Stok {$record->nama_suku_cadang} bertambah {$data['jumlah']} {$record->satuan}")
                            ->send();
                    }),
                Action::make('kurangi_stok')
                    ->label('Kurangi Stok')
                    ->icon('heroicon-o-minus-circle')
                    ->color('danger')
                    ->form([
                        TextInput::make('jumlah')
                            ->label('Jumlah Keluar')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->helperText(fn ($record) => 'Stok saat ini: ' . $record->stok . ' ' . $record->satuan),

                        DateTimePicker::make('tanggal_transaksi')
                            ->label('Tanggal & Waktu')
                            ->default(now())
                            ->required()
                            ->native(false),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(2)
                            ->placeholder('Contoh: Untuk perbaikan mesin A, Maintenance B, dll')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        if ($data['jumlah'] > $record->stok) {
                            Notification::make()
                                ->danger()
                                ->title('Stok tidak cukup')
                                ->body("Stok tersedia hanya {$record->stok} {$record->satuan}")
                                ->send();
                            return;
                        }

                        $stokSebelum = $record->stok;
                        $stokSesudah = $stokSebelum - $data['jumlah'];

                        // Buat transaksi
                        SparePartTransaction::create([
                            'spare_part_id' => $record->id,
                            'tipe_transaksi' => 'OUT',
                            'tanggal_transaksi' => $data['tanggal_transaksi'],
                            'user_id' => Auth::id(),
                            'jumlah' => $data['jumlah'],
                            'stok_sebelum' => $stokSebelum,
                            'stok_sesudah' => $stokSesudah,
                            'keterangan' => $data['keterangan'],
                        ]);

                        // Update stok
                        $record->update(['stok' => $stokSesudah]);

                        Notification::make()
                            ->success()
                            ->title('Stok berhasil dikurangi')
                            ->body("Stok {$record->nama_suku_cadang} berkurang {$data['jumlah']} {$record->satuan}")
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Suku Cadang Terpilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
