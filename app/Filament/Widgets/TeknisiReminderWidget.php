<?php

namespace App\Filament\Widgets;

use App\Models\MaintenanceReport;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class TeknisiReminderWidget extends Widget
{
    protected string $view = 'filament.widgets.teknisi-reminder-simple';
    
    protected static ?int $sort = -3;
    
    protected int | string | array $columnSpan = 1;

    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('Teknisi');
    }

    public function getMessage(): array
    {
        $user = Auth::user();
        
        // Cek maintenance yang belum selesai (status selain 'completed')
        $maintenanceBelumSelesai = MaintenanceReport::where('teknisi_id', $user->id)
            ->where('status', '!=', 'completed')
            ->whereNull('tanggal_selesai')
            ->count();

        $maintenanceSelesaiHariIni = MaintenanceReport::where('teknisi_id', $user->id)
            ->where('status', 'completed')
            ->whereDate('tanggal_selesai', today())
            ->count();

        if ($maintenanceBelumSelesai > 0) {
            return [
                'type' => 'warning',
                'icon' => 'heroicon-o-wrench-screwdriver',
                'title' => 'Pengingat Maintenance, ' . $user->name,
                'message' => "Anda memiliki $maintenanceBelumSelesai mesin yang belum selesai maintenance. Jangan lupa untuk menyelesaikan perbaikan mesin tersebut.",
            ];
        } else {
            return [
                'type' => 'success',
                'icon' => 'heroicon-o-check-circle',
                'title' => 'Terima Kasih, ' . $user->name . '!',
                'message' => $maintenanceSelesaiHariIni > 0 
                    ? "Anda telah menyelesaikan $maintenanceSelesaiHariIni maintenance hari ini. Kerja keras Anda sangat membantu!"
                    : "Semua maintenance sudah selesai. Terima kasih atas kerja keras Anda!",
            ];
        }
    }
}
