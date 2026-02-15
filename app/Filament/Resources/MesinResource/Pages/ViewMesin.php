<?php

namespace App\Filament\Resources\MesinResource\Pages;

use App\Filament\Resources\MesinResource;
use App\Exports\MesinLengkapExport;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Maatwebsite\Excel\Facades\Excel;

class ViewMesin extends ViewRecord
{
    protected static string $resource = MesinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_laporan')
                ->label('Export Laporan Lengkap')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $fileName = 'Laporan_Mesin_' . $this->record->kode_mesin . '_' . now()->format('Y-m-d_His') . '.xlsx';
                    return Excel::download(new MesinLengkapExport($this->record->id), $fileName);
                })
                ->tooltip('Export laporan lengkap mesin dengan semua riwayat'),
            Actions\EditAction::make()
                ->label('Ubah'),
            Actions\DeleteAction::make()
                ->label('Hapus'),
        ];
    }
}
