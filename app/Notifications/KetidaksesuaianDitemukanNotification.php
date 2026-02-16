<?php

namespace App\Notifications;

use App\Filament\Resources\MaintenanceReports\MaintenanceReportResource;
use App\Models\DetailPengecekanMesin;
use App\Models\MaintenanceReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class KetidaksesuaianDitemukanNotification extends Notification
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
        $operator = $this->maintenanceReport->detailPengecekanMesin?->pengecekanMesin?->operator;
        
        return [
            'format' => 'filament',
            'title' => '⚠️ Ketidaksesuaian Ditemukan',
            'body' => "Ketidaksesuaian pada " . ($mesin?->nama_mesin ?? 'N/A') . " - " . ($komponen?->nama_komponen ?? 'N/A'),
            'duration' => 'persistent',
            'actions' => [
                [
                    'name' => 'view',
                    'label' => 'Lihat Detail',
                    'url' => MaintenanceReportResource::getUrl('index'),
                    'color' => 'primary',
                ],
            ],
            'data' => [
                'operator' => $operator?->name,
                'mesin' => $mesin?->nama_mesin ?? 'N/A',
                'komponen' => $komponen?->nama_komponen ?? 'N/A',
                'issue' => $this->maintenanceReport->issue_description,
                'maintenance_report_id' => $this->maintenanceReport->id,
            ],
        ];
    }
}
