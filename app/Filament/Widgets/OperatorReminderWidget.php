<?php

namespace App\Filament\Widgets;

use App\Models\PengecekanMesin;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class OperatorReminderWidget extends Widget
{
    protected string $view = 'filament.widgets.operator-reminder-simple';
    
    protected static ?int $sort = -4;
    
    protected int | string | array $columnSpan = 'full';

    protected ?string $pollingInterval = '60s';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole('Operator');
    }

    public function getMessage(): array
    {
        $user = Auth::user();
        
        $sudahMengecek = PengecekanMesin::where('user_id', $user->id)
            ->whereDate('tanggal_pengecekan', today())
            ->where('status', 'selesai')
            ->exists();

        $jumlahDicek = PengecekanMesin::where('user_id', $user->id)
            ->whereDate('tanggal_pengecekan', today())
            ->where('status', 'selesai')
            ->count();

        if ($sudahMengecek) {
            return [
                'type' => 'success',
                'icon' => 'heroicon-o-check-circle',
                'title' => 'Terima Kasih, ' . $user->name . '!',
                'message' => "Anda telah menyelesaikan $jumlahDicek pengecekan hari ini. Kerja keras Anda sangat membantu!",
            ];
        } else {
            return [
                'type' => 'warning',
                'icon' => 'heroicon-o-clock',
                'title' => 'Pengingat Pengecekan, ' . $user->name,
                'message' => 'Anda belum melakukan pengecekan hari ini. Jangan lupa untuk mengecek daftar pengecekan yang menjadi tanggung jawab Anda.',
            ];
        }
    }
}
