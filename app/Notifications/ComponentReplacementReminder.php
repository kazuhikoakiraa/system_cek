<?php

namespace App\Notifications;

use App\Models\MComponent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;

class ComponentReplacementReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $component;
    protected $isOverdue;

    /**
     * Create a new notification instance.
     */
    public function __construct(MComponent $component, bool $isOverdue = false)
    {
        $this->component = $component;
        $this->isOverdue = $isOverdue;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mesin = $this->component->mesin;
        $mesinName = $mesin?->nama_mesin ?? 'N/A';
        $mesinKode = $mesin?->kode_mesin ?? 'N/A';
        
        $subject = $this->isOverdue 
            ? '⚠️ URGENT: Komponen Melewati Jadwal Penggantian' 
            : '⏰ Reminder: Komponen Akan Perlu Diganti';

        $greeting = $this->isOverdue 
            ? 'Perhatian! Komponen Sudah Melewati Jadwal Penggantian' 
            : 'Pengingat Jadwal Penggantian Komponen';

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line("Komponen **{$this->component->nama_komponen}** pada mesin **{$mesinName}** ({$mesinKode})");

        if ($this->isOverdue) {
            $daysPast = \Carbon\Carbon::parse($this->component->estimasi_tanggal_ganti_berikutnya)->diffInDays(now());
            $mail->line("⚠️ **Sudah melewati {$daysPast} hari** dari tanggal penggantian yang direncanakan!")
                ->line("Tanggal yang direncanakan: " . \Carbon\Carbon::parse($this->component->estimasi_tanggal_ganti_berikutnya)->format('d M Y'));
        } else {
            $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($this->component->estimasi_tanggal_ganti_berikutnya));
            $mail->line("Akan perlu diganti dalam **{$daysLeft} hari**")
                ->line("Tanggal penggantian: " . \Carbon\Carbon::parse($this->component->estimasi_tanggal_ganti_berikutnya)->format('d M Y'));
        }

        $mail->line("**Detail Komponen:**")
            ->line("- Part Number: {$this->component->part_number}")
            ->line("- Lokasi: {$this->component->lokasi_pemasangan}")
            ->line("- Supplier: {$this->component->nama_supplier}")
            ->line("- Status: {$this->component->status_komponen}")
            ->action('Lihat Detail Mesin', url($mesin ? "/admin/mesins/{$mesin->id}" : '#'))
            ->line('Mohon segera lakukan tindakan yang diperlukan.');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $mesin = $this->component->mesin;
        return [
            'component_id' => $this->component->id,
            'component_name' => $this->component->nama_komponen,
            'mesin_id' => $this->component->mesin_id,
            'mesin_name' => $mesin?->nama_mesin ?? 'N/A',
            'mesin_code' => $mesin?->kode_mesin ?? 'N/A',
            'estimasi_tanggal_ganti' => $this->component->estimasi_tanggal_ganti_berikutnya,
            'is_overdue' => $this->isOverdue,
            'type' => 'component_replacement_reminder',
        ];
    }

    /**
     * Send Filament notification
     */
    public function toFilament(object $notifiable): FilamentNotification
    {
        $mesin = $this->component->mesin;
        $mesinName = $mesin?->nama_mesin ?? 'N/A';
        
        if ($this->isOverdue) {
            $daysPast = \Carbon\Carbon::parse($this->component->estimasi_tanggal_ganti_berikutnya)->diffInDays(now());
            $title = '⚠️ Komponen Terlambat Diganti';
            $body = "{$this->component->nama_komponen} pada {$mesinName} sudah melewati {$daysPast} hari dari jadwal.";
            $color = 'danger';
        } else {
            $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($this->component->estimasi_tanggal_ganti_berikutnya));
            $title = '⏰ Reminder Penggantian Komponen';
            $body = "{$this->component->nama_komponen} pada {$mesinName} perlu diganti dalam {$daysLeft} hari.";
            $color = 'warning';
        }

        return FilamentNotification::make()
            ->title($title)
            ->body($body)
            ->icon($this->isOverdue ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-bell')
            ->iconColor($color)
            ->actions([
                Action::make('view')
                    ->label('Lihat Mesin')
                    ->url($mesin ? "/admin/mesins/{$mesin->id}" : '#'),
            ])
            ->sendToDatabase($notifiable);
    }
}
