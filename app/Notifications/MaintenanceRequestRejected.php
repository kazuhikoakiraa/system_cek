<?php

namespace App\Notifications;

use App\Models\MRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceRequestRejected extends Notification
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
            ->error()
            ->subject('Request Maintenance Ditolak - ' . $this->request->request_number)
            ->line('Request maintenance Anda telah ditolak.')
            ->line('**Nomor Request:** ' . $this->request->request_number)
            ->line('**Mesin:** ' . $this->request->mesin->nama_mesin)
            ->line('**Ditolak oleh:** ' . $this->request->approver->name)
            ->line('**Alasan Penolakan:** ' . $this->request->rejection_reason)
            ->action('Lihat Detail', url('/admin/m-requests/' . $this->request->id))
            ->line('Silakan hubungi admin untuk informasi lebih lanjut.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'request_id' => $this->request->id,
            'request_number' => $this->request->request_number,
            'mesin_name' => $this->request->mesin->nama_mesin,
            'rejector_name' => $this->request->approver->name,
            'rejection_reason' => $this->request->rejection_reason,
        ];
    }
}
