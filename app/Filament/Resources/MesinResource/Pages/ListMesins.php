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
            Actions\Action::make('export_all')
                ->label('Export Semua Mesin')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $fileName = 'Daftar_Semua_Mesin_' . now()->format('Y-m-d_His') . '.xlsx';
                    return Excel::download(new MesinLengkapExport(), $fileName);
                })
                ->tooltip('Export daftar semua mesin ke Excel'),
            Actions\CreateAction::make()
                ->label('Tambah Mesin'),
        ];
    }
}
