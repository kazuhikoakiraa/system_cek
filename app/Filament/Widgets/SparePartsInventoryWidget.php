<?php

namespace App\Filament\Widgets;

use App\Models\SparePart;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SparePartsInventoryWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        $totalSpareParts = SparePart::count();
        
        $lowStock = SparePart::whereColumn('stok', '<=', 'stok_minimum')->count();
        
        $outOfStock = SparePart::where('stok', 0)->count();
        
        $totalValue = SparePart::selectRaw('SUM(stok * harga_satuan) as total')
            ->first()
            ->total ?? 0;

        $healthyStock = $totalSpareParts - $lowStock - $outOfStock;

        return [
            Stat::make('Total Item Suku Cadang', $totalSpareParts)
                ->description("{$healthyStock} stok sehat")
                ->descriptionIcon('heroicon-o-cube')
                ->color('primary')
                ->url('/admin/spare-parts'),

            Stat::make('Stok Menipis', $lowStock)
                ->description('Perlu restock segera')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($lowStock > 0 ? 'warning' : 'success'),

            Stat::make('Stok Habis', $outOfStock)
                ->description('Tidak tersedia')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color($outOfStock > 0 ? 'danger' : 'success'),

            Stat::make('Nilai Inventaris', 'Rp ' . number_format($totalValue, 0, ',', '.'))
                ->description('Total nilai suku cadang')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('info'),
        ];
    }
}
