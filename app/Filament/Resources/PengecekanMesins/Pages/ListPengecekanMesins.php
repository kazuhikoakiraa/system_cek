<?php

namespace App\Filament\Resources\PengecekanMesins\Pages;

use App\Filament\Resources\PengecekanMesins\PengecekanMesinResource;
use App\Filament\Widgets\StatusPengecekanOverview;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListPengecekanMesins extends ListRecords
{
    protected static string $resource = PengecekanMesinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mulai_pengecekan')
                ->label('Mulai Pengecekan')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->url(fn () => PengecekanMesinResource::getUrl('mulai')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatusPengecekanOverview::class,
        ];
    }

    public function getTitle(): string
    {
        return 'Pengecekan Mesin - ' . now()->translatedFormat('d F Y');
    }

    public function getSubheading(): ?string
    {
        return 'Daftar dan status pengecekan mesin untuk hari ini: ' . now()->translatedFormat('l, d F Y');
    }
}
