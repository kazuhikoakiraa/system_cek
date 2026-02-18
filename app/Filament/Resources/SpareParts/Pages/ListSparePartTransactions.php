<?php

namespace App\Filament\Resources\SpareParts\Pages;

use App\Exports\SparePartTransactionExport;
use App\Filament\Resources\SparePartTransactionResource;
use App\Models\SparePartTransaction;
use App\Http\Controllers\SparePartTransactionPdfController;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ListSparePartTransactions extends ListRecords
{
    protected static string $resource = SparePartTransactionResource::class;

    protected static ?string $title = 'Laporan Suku Cadang';
    
    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua')
                ->badge(SparePartTransaction::count()),
            'masuk' => Tab::make('Masuk')
                ->badge(SparePartTransaction::where('tipe_transaksi', 'IN')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_transaksi', 'IN')),
            'keluar' => Tab::make('Keluar')
                ->badge(SparePartTransaction::where('tipe_transaksi', 'OUT')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_transaksi', 'OUT')),
        ];
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    DatePicker::make('tanggal_mulai')
                        ->label('Tanggal Mulai')
                        ->native(false)
                        ->prefixIcon('heroicon-o-calendar')
                        ->displayFormat('d/m/Y')
                        ->placeholder('Pilih tanggal mulai'),
                    DatePicker::make('tanggal_selesai')
                        ->label('Tanggal Selesai')
                        ->native(false)
                        ->prefixIcon('heroicon-o-calendar')
                        ->displayFormat('d/m/Y')
                        ->placeholder('Pilih tanggal selesai'),
                    Select::make('tipe_transaksi')
                        ->label('Tipe Transaksi')
                        ->options([
                            'IN' => 'Masuk',
                            'OUT' => 'Keluar',
                            'RETURN' => 'Retur',
                        ])
                        ->placeholder('Semua Tipe')
                        ->prefixIcon('heroicon-o-arrow-path'),
                    Select::make('spare_part_id')
                        ->label('Suku Cadang')
                        ->relationship('sparePart', 'nama_suku_cadang')
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Suku Cadang')
                        ->prefixIcon('heroicon-o-wrench-screwdriver'),
                ])
                ->action(function (array $data) {
                    $query = SparePartTransaction::with(['sparePart', 'user']);

                    if (!empty($data['tanggal_mulai'])) {
                        $query->whereDate('tanggal_transaksi', '>=', $data['tanggal_mulai']);
                    }

                    if (!empty($data['tanggal_selesai'])) {
                        $query->whereDate('tanggal_transaksi', '<=', $data['tanggal_selesai']);
                    }

                    if (!empty($data['tipe_transaksi'])) {
                        $query->where('tipe_transaksi', $data['tipe_transaksi']);
                    }

                    if (!empty($data['spare_part_id'])) {
                        $query->where('spare_part_id', $data['spare_part_id']);
                    }

                    $transactions = $query->orderBy('tanggal_transaksi', 'desc')->get();

                    if ($transactions->isEmpty()) {
                        Notification::make()
                            ->warning()
                            ->title('Tidak ada data')
                            ->body('Tidak ada transaksi yang sesuai dengan filter.')
                            ->send();
                        return;
                    }

                    $tanggalMulai = !empty($data['tanggal_mulai']) ? Carbon::parse($data['tanggal_mulai']) : null;
                    $tanggalSelesai = !empty($data['tanggal_selesai']) ? Carbon::parse($data['tanggal_selesai']) : null;
                    $tipeTransaksi = $data['tipe_transaksi'] ?? null;
                    $sparePartId = $data['spare_part_id'] ?? null;

                    $fileName = 'Laporan_Transaksi_Suku_Cadang_' . now()->format('Y-m-d_His') . '.xlsx';

                    Notification::make()
                        ->success()
                        ->title('Export berhasil')
                        ->body('File Excel sedang diunduh...')
                        ->send();

                    return Excel::download(
                        new SparePartTransactionExport($transactions, $tanggalMulai, $tanggalSelesai, $tipeTransaksi, $sparePartId),
                        $fileName
                    );
                }),

            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->form([
                    DatePicker::make('tanggal_mulai')
                        ->label('Tanggal Mulai')
                        ->native(false)
                        ->prefixIcon('heroicon-o-calendar')
                        ->displayFormat('d/m/Y')
                        ->placeholder('Pilih tanggal mulai'),
                    DatePicker::make('tanggal_selesai')
                        ->label('Tanggal Selesai')
                        ->native(false)
                        ->prefixIcon('heroicon-o-calendar')
                        ->displayFormat('d/m/Y')
                        ->placeholder('Pilih tanggal selesai'),
                    Select::make('tipe_transaksi')
                        ->label('Tipe Transaksi')
                        ->options([
                            'IN' => 'Masuk',
                            'OUT' => 'Keluar',
                            'RETURN' => 'Retur',
                        ])
                        ->placeholder('Semua Tipe')
                        ->prefixIcon('heroicon-o-arrow-path'),
                    Select::make('spare_part_id')
                        ->label('Suku Cadang')
                        ->relationship('sparePart', 'nama_suku_cadang')
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Suku Cadang')
                        ->prefixIcon('heroicon-o-wrench-screwdriver'),
                ])
                ->action(function (array $data) {
                    $params = array_filter($data);

                    Notification::make()
                        ->success()
                        ->title('Export berhasil')
                        ->body('File PDF sedang diunduh...')
                        ->send();

                    $request = request()->merge($params);
                    return app(SparePartTransactionPdfController::class)->download($request);
                }),
        ];
    }
}
