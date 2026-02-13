<?php

namespace App\Filament\Resources\MaintenanceReports\Pages;

use App\Exports\MaintenanceReportExcel;
use App\Filament\Resources\MaintenanceReports\MaintenanceReportResource;
use App\Models\MaintenanceReport;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Blade;
use Maatwebsite\Excel\Facades\Excel;

class ListMaintenanceReports extends ListRecords
{
    protected static string $resource = MaintenanceReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_excel')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    DatePicker::make('tanggal_mulai')
                        ->label('Tanggal Mulai')
                        ->native(false)
                        ->prefixIcon('heroicon-o-calendar')
                        ->displayFormat('d/m/Y')
                        ->placeholder('Pilih tanggal mulai'),
                    DatePicker::make('tanggal_selesai')
                        ->label('Tanggal Selesai')
                        ->native(false)
                        ->prefixIcon('heroicon-o-calendar')
                        ->displayFormat('d/m/Y')
                        ->placeholder('Pilih tanggal selesai'),
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Menunggu',
                            'in_progress' => 'Sedang Diproses',
                            'completed' => 'Selesai',
                        ])
                        ->placeholder('Semua Status')
                        ->prefixIcon('heroicon-o-flag'),
                    Select::make('mesin_id')
                        ->label('Mesin')
                        ->relationship('mesin', 'nama_mesin')
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Mesin')
                        ->prefixIcon('heroicon-o-cog-6-tooth'),
                ])
                ->action(function (array $data) {
                    $query = MaintenanceReport::with(['mesin', 'komponenMesin', 'teknisi', 'spareParts']);

                    if (!empty($data['tanggal_mulai'])) {
                        $query->whereDate('created_at', '>=', $data['tanggal_mulai']);
                    }

                    if (!empty($data['tanggal_selesai'])) {
                        $query->whereDate('created_at', '<=', $data['tanggal_selesai']);
                    }

                    if (!empty($data['status'])) {
                        $query->where('status', $data['status']);
                    }

                    if (!empty($data['mesin_id'])) {
                        $query->where('mesin_id', $data['mesin_id']);
                    }

                    $reports = $query->orderBy('created_at', 'desc')->get();

                    if ($reports->isEmpty()) {
                        Notification::make()
                            ->warning()
                            ->title('Tidak ada data')
                            ->body('Tidak ada laporan maintenance yang sesuai dengan filter.')
                            ->send();
                        return;
                    }

                    $tanggalMulai = !empty($data['tanggal_mulai']) ? Carbon::parse($data['tanggal_mulai']) : null;
                    $tanggalSelesai = !empty($data['tanggal_selesai']) ? Carbon::parse($data['tanggal_selesai']) : null;
                    $status = $data['status'] ?? null;
                    $mesinId = $data['mesin_id'] ?? null;

                    $fileName = 'Laporan_Maintenance_' . now()->format('Y-m-d_His') . '.xlsx';

                    Notification::make()
                        ->success()
                        ->title('Export berhasil')
                        ->body('File Excel sedang diunduh...')
                        ->send();

                    return Excel::download(
                        new MaintenanceReportExcel($reports, $tanggalMulai, $tanggalSelesai, $status, $mesinId),
                        $fileName
                    );
                }),

            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-text')
                ->color('danger')
                ->form([
                    DatePicker::make('tanggal_mulai')
                        ->label('Tanggal Mulai')
                        ->native(false)
                        ->prefixIcon('heroicon-o-calendar')
                        ->displayFormat('d/m/Y')
                        ->placeholder('Pilih tanggal mulai'),
                    DatePicker::make('tanggal_selesai')
                        ->label('Tanggal Selesai')
                        ->native(false)
                        ->prefixIcon('heroicon-o-calendar')
                        ->displayFormat('d/m/Y')
                        ->placeholder('Pilih tanggal selesai'),
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'pending' => 'Menunggu',
                            'in_progress' => 'Sedang Diproses',
                            'completed' => 'Selesai',
                        ])
                        ->placeholder('Semua Status')
                        ->prefixIcon('heroicon-o-flag'),
                    Select::make('mesin_id')
                        ->label('Mesin')
                        ->relationship('mesin', 'nama_mesin')
                        ->searchable()
                        ->preload()
                        ->placeholder('Semua Mesin')
                        ->prefixIcon('heroicon-o-cog-6-tooth'),
                    Checkbox::make('include_detail')
                        ->label('Sertakan Detail Lengkap')
                        ->helperText('Menampilkan detail lengkap setiap laporan (catatan, spare parts, dll)')
                        ->default(false)
                        ->inline(false),
                ])
                ->action(function (array $data) {
                    $query = MaintenanceReport::with(['mesin', 'komponenMesin', 'teknisi', 'spareParts']);

                    if (!empty($data['tanggal_mulai'])) {
                        $query->whereDate('created_at', '>=', $data['tanggal_mulai']);
                    }

                    if (!empty($data['tanggal_selesai'])) {
                        $query->whereDate('created_at', '<=', $data['tanggal_selesai']);
                    }

                    if (!empty($data['status'])) {
                        $query->where('status', $data['status']);
                    }

                    if (!empty($data['mesin_id'])) {
                        $query->where('mesin_id', $data['mesin_id']);
                    }

                    $reports = $query->orderBy('created_at', 'desc')->get();

                    if ($reports->isEmpty()) {
                        Notification::make()
                            ->warning()
                            ->title('Tidak ada data')
                            ->body('Tidak ada laporan maintenance yang sesuai dengan filter.')
                            ->send();
                        return;
                    }

                    $tanggalMulai = !empty($data['tanggal_mulai']) ? Carbon::parse($data['tanggal_mulai']) : null;
                    $tanggalSelesai = !empty($data['tanggal_selesai']) ? Carbon::parse($data['tanggal_selesai']) : null;
                    $includeDetail = $data['include_detail'] ?? false;

                    $html = Blade::render('exports.maintenance-report-pdf', [
                        'laporanData' => $reports,
                        'tanggalMulai' => $tanggalMulai,
                        'tanggalSelesai' => $tanggalSelesai,
                        'includeDetail' => $includeDetail,
                    ]);

                    $options = new Options();
                    $options->set('isHtml5ParserEnabled', true);
                    $options->set('isRemoteEnabled', true);
                    $options->set('defaultFont', 'DejaVu Sans');

                    $dompdf = new Dompdf($options);
                    $dompdf->loadHtml($html);
                    $dompdf->setPaper('A4', 'portrait');
                    $dompdf->render();

                    $fileName = 'Laporan_Maintenance_' . now()->format('Y-m-d_His') . '.pdf';

                    Notification::make()
                        ->success()
                        ->title('Export berhasil')
                        ->body('File PDF sedang diunduh...')
                        ->send();

                    return response()->streamDownload(
                        fn () => print($dompdf->output()),
                        $fileName
                    );
                }),

            CreateAction::make()
                ->label('Buat Laporan Maintenance')
                ->icon('heroicon-o-plus'),
        ];
    }
}
