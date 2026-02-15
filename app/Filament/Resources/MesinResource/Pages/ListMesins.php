<?php

namespace App\Filament\Resources\MesinResource\Pages;

use App\Filament\Resources\MesinResource;
use App\Exports\MesinLengkapExport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListMesins extends ListRecords
{
    protected static string $resource = MesinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('danger')
                ->url(fn () => route('mesin.export.pdf'))
                ->openUrlInNewTab()
                ->tooltip('Export daftar mesin ke PDF'),
            
            Actions\Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $fileName = 'Daftar_Master_Mesin_' . now()->format('Y-m-d_His') . '.xlsx';
                    return Excel::download(new MesinLengkapExport(), $fileName);
                })
                ->tooltip('Export daftar mesin ke Excel'),
            
            Actions\CreateAction::make()
                ->label('Tambah Mesin')
                ->icon('heroicon-o-plus'),
        ];
    }
}
