<?php

namespace App\Filament\Resources\SpareParts\Pages;

use App\Exports\SparePartTransactionExport;
use App\Filament\Resources\SparePartTransactionResource;
use App\Models\SparePartTransaction;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ListSparePartTransactions extends ListRecords
{
    protected static string $resource = SparePartTransactionResource::class;

    protected static ?string $title = 'Laporan Transaksi Suku Cadang';
    
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
            'retur' => Tab::make('Retur')
                ->badge(SparePartTransaction::where('tipe_transaksi', 'RETURN')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('tipe_transaksi', 'RETURN')),
        ];
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $filters = $this->tableFilters;
                    return Excel::download(new SparePartTransactionExport($filters), 'laporan-transaksi-' . now()->format('Y-m-d') . '.xlsx');
                }),
            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->url(fn () => route('spare-part-transactions.pdf', $this->tableFilters))
                ->openUrlInNewTab(),
        ];
    }
}
