<?php

namespace App\Notifications;

use App\Filament\Resources\MaintenanceReports\MaintenanceReportResource;
use App\Models\MaintenanceReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MaintenanceSelesaiNotification extends Notification
{
    use Queueable;

    public function __construct(
        public MaintenanceReport $maintenanceReport
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $mesin = $this->maintenanceReport->mesin;
        $komponen = $this->maintenanceReport->komponenMesin;
        $teknisi = $this->maintenanceReport->teknisi;
        
        return [
            'format' => 'filament',
            'title' => '✅ Maintenance Selesai',
            'body' => "Perbaikan {$mesin->nama_mesin} - {$komponen->nama_komponen} telah diselesaikan oleh {$teknisi?->name}",
            'duration' => 'persistent',
            'actions' => [
                [
                    'name' => 'view',
                    'label' => 'Lihat Hasil',
                    'url' => MaintenanceReportResource::getUrl('index'),
                    'color' => 'success',
                ],
            ],
            'data' => [
                'teknisi' => $teknisi?->name,
                'mesin' => $mesin->nama_mesin,
                'komponen' => $komponen->nama_komponen,
                'catatan' => $this->maintenanceReport->catatan_teknisi,
                'maintenance_report_id' => $this->maintenanceReport->id,
            ],
        ];
    }
}
