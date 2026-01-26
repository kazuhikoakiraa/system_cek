<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AdminGreetingWidget extends Widget
{
    protected string $view = 'filament.widgets.admin-greeting';
    
    protected static ?int $sort = -3;
    
    protected int | string | array $columnSpan = 1;

    protected ?string $pollingInterval = null;

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasAnyRole(['super_admin', 'admin', 'panel_user']);
    }

    public function getMessage(): array
    {
        $user = Auth::user();
        $now = now()->timezone('Asia/Jakarta');
        $hour = $now->hour;
        
        // Tentukan salam berdasarkan waktu
        if ($hour >= 5 && $hour < 11) {
            $greeting = 'Selamat Pagi';
            $icon = 'heroicon-o-sun';
        } elseif ($hour >= 11 && $hour < 15) {
            $greeting = 'Selamat Siang';
            $icon = 'heroicon-o-sun';
        } elseif ($hour >= 15 && $hour < 18) {
            $greeting = 'Selamat Sore';
            $icon = 'heroicon-o-sun';
        } else {
            $greeting = 'Selamat Malam';
            $icon = 'heroicon-o-moon';
        }

        // Array quotes inspiratif
        $quotes = [
            'Kesuksesan adalah hasil dari persiapan, kerja keras, dan belajar dari kegagalan.',
            'Kepemimpinan adalah tindakan, bukan posisi.',
            'Kualitas bukan suatu tindakan, tetapi kebiasaan.',
            'Tim yang hebat membuat perbedaan besar.',
            'Inovasi membedakan pemimpin dari pengikut.',
            'Keunggulan adalah melakukan hal biasa dengan luar biasa baik.',
            'Kesempatan tidak datang, Anda menciptakannya.',
            'Fokus pada solusi, bukan masalah.',
        ];

        // Quotes berubah setiap hari berdasarkan tanggal
        $dayOfYear = $now->dayOfYear;
        $quoteIndex = $dayOfYear % count($quotes);
        $dailyQuote = $quotes[$quoteIndex];

        return [
            'greeting' => $greeting . ', ' . $user->name . '!',
            'quote' => $dailyQuote,
            'icon' => $icon,
        ];
    }
}
