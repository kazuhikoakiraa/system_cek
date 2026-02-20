<?php

namespace App\Notifications;

use App\Models\MRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceRequestApproved extends Notification
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
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Request Maintenance Disetujui - ' . $this->request->request_number)
            ->line('Request maintenance telah disetujui dan dapat dikerjakan.')
            ->line('**Nomor Request:** ' . $this->request->request_number)
            ->line('**Mesin:** ' . ($this->request->mesin?->nama_mesin ?? 'N/A'))
            ->line('**Disetujui oleh:** ' . ($this->request->approver?->name ?? 'N/A'))
            ->line('**Tingkat Urgensi:** ' . ucfirst($this->request->urgency_level))
            ->line('**Deskripsi Problema:** ' . $this->request->problema_deskripsi)
            ->when($this->request->approval_notes, function ($mail) {
                return $mail->line('**Catatan Approval:** ' . $this->request->approval_notes);
            })
            ->action('Mulai Pekerjaan', url('/admin/m-logs/create?request=' . $this->request->id))
            ->line('Silakan segera mengerjakan maintenance ini.');
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
            'title' => 'Request Maintenance Disetujui',
            'body' => "Request {$this->request->request_number} telah disetujui.",
            'duration' => 'persistent',
            'actions' => [
                [
                    'name' => 'view',
                    'label' => 'Mulai Pekerjaan',
                    'url' => url('/admin/m-logs/create?request=' . $this->request->id),
                    'color' => 'success',
                ],
            ],
            'request_id' => $this->request->id,
            'request_number' => $this->request->request_number,
            'mesin_name' => $this->request->mesin?->nama_mesin ?? 'N/A',
            'approver_name' => $this->request->approver?->name ?? 'N/A',
            'urgency_level' => $this->request->urgency_level,
            'approval_notes' => $this->request->approval_notes,
        ];
    }
}
