<?php

namespace App\Notifications;

use App\Models\Mesin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;

class MachineReplacementReminder extends Notification implements ShouldQueue
{
    use Queueable;

    protected $mesin;
    protected $isOverdue;

    /**
     * Create a new notification instance.
     */
    public function __construct(Mesin $mesin, bool $isOverdue = false)
    {
        $this->mesin = $mesin;
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
        $subject = $this->isOverdue 
            ? '⚠️ URGENT: Mesin Melewati Umur Ekonomis' 
            : '⏰ Reminder: Mesin Mendekati Akhir Umur Ekonomis';

        $greeting = $this->isOverdue 
            ? 'Perhatian! Mesin Sudah Melewati Umur Ekonomis' 
            : 'Pengingat Umur Ekonomis Mesin';

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line("Mesin **{$this->mesin->nama_mesin}** ({$this->mesin->kode_mesin})");

        if ($this->isOverdue) {
            $daysPast = \Carbon\Carbon::parse($this->mesin->estimasi_penggantian)->diffInDays(now());
            $mail->line("⚠️ **Sudah melewati {$daysPast} hari** dari estimasi penggantian!")
                ->line("Estimasi penggantian: " . \Carbon\Carbon::parse($this->mesin->estimasi_penggantian)->format('d M Y'));
        } else {
            $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($this->mesin->estimasi_penggantian));
            $mail->line("Akan mencapai akhir umur ekonomis dalam **{$daysLeft} hari**")
                ->line("Estimasi penggantian: " . \Carbon\Carbon::parse($this->mesin->estimasi_penggantian)->format('d M Y'));
        }

        $mail->line("**Detail Mesin:**")
            ->line("- Manufaktur: {$this->mesin->manufacturer}")
            ->line("- Model: {$this->mesin->model_number}")
            ->line("- Tanggal Pengadaan: " . ($this->mesin->tanggal_pengadaan ? $this->mesin->tanggal_pengadaan->format('d M Y') : '-'))
            ->line("- Umur Ekonomis: {$this->mesin->umur_ekonomis_tahun} tahun")
            ->line("- Status: {$this->mesin->status}")
            ->action('Lihat Detail Mesin', url("/admin/mesins/{$this->mesin->id}"))
            ->line('Mohon segera evaluasi untuk penggantian atau perpanjangan masa pakai.');

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'mesin_id' => $this->mesin->id,
            'mesin_name' => $this->mesin->nama_mesin,
            'mesin_code' => $this->mesin->kode_mesin,
            'estimasi_penggantian' => $this->mesin->estimasi_penggantian,
            'is_overdue' => $this->isOverdue,
            'type' => 'machine_replacement_reminder',
        ];
    }

    /**
     * Send Filament notification
     */
    public function toFilament(object $notifiable): FilamentNotification
    {
        if ($this->isOverdue) {
            $daysPast = \Carbon\Carbon::parse($this->mesin->estimasi_penggantian)->diffInDays(now());
            $title = '⚠️ Mesin Melewati Umur Ekonomis';
            $body = "{$this->mesin->nama_mesin} ({$this->mesin->kode_mesin}) sudah melewati {$daysPast} hari dari estimasi penggantian.";
            $color = 'danger';
        } else {
            $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($this->mesin->estimasi_penggantian));
            $title = '⏰ Reminder Umur Ekonomis Mesin';
            $body = "{$this->mesin->nama_mesin} ({$this->mesin->kode_mesin}) akan mencapai akhir umur ekonomis dalam {$daysLeft} hari.";
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
                    ->url("/admin/mesins/{$this->mesin->id}"),
            ])
            ->sendToDatabase($notifiable);
    }
}
