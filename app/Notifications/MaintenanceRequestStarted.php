<?php

namespace App\Notifications;

use App\Models\MRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MaintenanceRequestStarted extends Notification
{
    use Queueable;

    public function __construct(public MRequest $request)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'format' => 'filament',
            'title' => 'Maintenance Dimulai',
            'body' => "Request {$this->request->request_number} sudah mulai dikerjakan.",
            'duration' => 'persistent',
            'request_id' => $this->request->id,
            'request_number' => $this->request->request_number,
            'mesin_name' => $this->request->mesin?->nama_mesin ?? 'N/A',
            'teknisi_name' => $this->request->teknisi?->name ?? 'N/A',
            'status' => $this->request->status,
        ];
    }
}
