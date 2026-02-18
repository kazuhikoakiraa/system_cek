<?php

namespace App\Notifications;

use App\Models\MRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceRequestCreated extends Notification
{
    use Queueable;

    public function __construct(public MRequest $request)
    {
        //
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
        return (new MailMessage)
            ->subject('Request Maintenance Baru - ' . $this->request->request_number)
            ->line('Request maintenance baru telah dibuat dan memerlukan persetujuan Anda.')
            ->line('**Nomor Request:** ' . $this->request->request_number)
            ->line('**Mesin:** ' . ($this->request->mesin?->nama_mesin ?? 'N/A'))
            ->line('**Dibuat oleh:** ' . ($this->request->creator?->name ?? 'N/A'))
            ->line('**Tingkat Urgensi:** ' . ucfirst($this->request->urgency_level))
            ->line('**Deskripsi Problema:** ' . $this->request->problema_deskripsi)
            ->action('Lihat Detail & Approve', url('/admin/m-requests/' . $this->request->id))
            ->line('Mohon segera ditinjau untuk persetujuan.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'format' => 'filament',
            'title' => 'Request Maintenance Baru',
            'body' => "Request {$this->request->request_number} untuk mesin " . ($this->request->mesin?->nama_mesin ?? 'N/A'),
            'duration' => 'persistent',
            'actions' => [
                [
                    'name' => 'view',
                    'label' => 'Lihat Detail',
                    'url' => url('/admin/m-requests/' . $this->request->id),
                    'color' => 'primary',
                ],
            ],
            'request_id' => $this->request->id,
            'request_number' => $this->request->request_number,
            'mesin_name' => $this->request->mesin?->nama_mesin ?? 'N/A',
            'creator_name' => $this->request->creator?->name ?? 'N/A',
            'urgency_level' => $this->request->urgency_level,
            'problema_deskripsi' => $this->request->problema_deskripsi,
        ];
    }
}
