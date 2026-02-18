<?php

namespace App\Filament\Pages;

use App\Models\DaftarPengecekan;
use App\Models\PengecekanMesin;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class LaporanPengecekan extends Page implements HasForms
{
    use InteractsWithForms;
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static ?string $navigationLabel = 'Laporan Pengecekan';

    protected static ?string $title = 'Laporan Pengecekan Mesin';

    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 50;

    protected static ?int $navigationGroupSort = 99;

    public ?array $data = [];

    public ?string $filterPeriode = 'bulanan';
    public ?string $tanggalMulai = null;
    public ?string $tanggalSelesai = null;
    public ?int $mesinId = null;
    public Collection $laporanData;
    public bool $showPreview = false;

    public function getView(): string
    {
        return 'filament.pages.laporan-pengecekan';
    }

    public function mount(): void
    {
        $this->laporanData = collect();
        $this->tanggalMulai = now()->startOfMonth()->format('Y-m-d');
        $this->tanggalSelesai = now()->endOfMonth()->format('Y-m-d');

        $this->form->fill([
            'filter_periode' => 'bulanan',
            'tanggal_mulai' => $this->tanggalMulai,
            'tanggal_selesai' => $this->tanggalSelesai,
            'mesin_id' => null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter Laporan')
                    ->description('Atur filter untuk menampilkan data laporan pengecekan mesin')
                    ->icon(Heroicon::OutlinedFunnel)
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Select::make('filter_periode')
                                    ->label('Periode')
                                    ->options([
                                        'harian' => 'Harian',
                                        'mingguan' => 'Mingguan',
                                        'bulanan' => 'Bulanan',
                                        'tahunan' => 'Tahunan',
                                        'custom' => 'Custom Range',
                                    ])
                                    ->default('bulanan')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $this->updateDateRange($state, $set);
                                    }),

                                DatePicker::make('tanggal_mulai')
                                    ->label('Tanggal Mulai')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->live(),

                                DatePicker::make('tanggal_selesai')
                                    ->label('Tanggal Selesai')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->live(),

                                Select::make('mesin_id')
                                    ->label('Daftar Pengecekan')
                                    ->placeholder('Semua Daftar Pengecekan')
                                    ->options(DaftarPengecekan::pluck('nama_mesin', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->live(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function updateDateRange(string $periode, callable $set): void
    {
        $now = now();

        match ($periode) {
            'harian' => (function () use ($now, $set) {
                $set('tanggal_mulai', $now->format('Y-m-d'));
                $set('tanggal_selesai', $now->format('Y-m-d'));
            })(),
            'mingguan' => (function () use ($now, $set) {
                $set('tanggal_mulai', $now->startOfWeek()->format('Y-m-d'));
                $set('tanggal_selesai', $now->endOfWeek()->format('Y-m-d'));
            })(),
            'bulanan' => (function () use ($now, $set) {
                $set('tanggal_mulai', $now->startOfMonth()->format('Y-m-d'));
                $set('tanggal_selesai', $now->endOfMonth()->format('Y-m-d'));
            })(),
            'tahunan' => (function () use ($now, $set) {
                $set('tanggal_mulai', $now->startOfYear()->format('Y-m-d'));
                $set('tanggal_selesai', $now->endOfYear()->format('Y-m-d'));
            })(),
            default => null,
        };
    }

    public function tampilkanLaporan(): void
    {
        $data = $this->form->getState();

        $this->tanggalMulai = $data['tanggal_mulai'];
        $this->tanggalSelesai = $data['tanggal_selesai'];
        $this->mesinId = $data['mesin_id'];
        $this->filterPeriode = $data['filter_periode'];

        $this->laporanData = $this->getLaporanData();
        $this->showPreview = true;

        Notification::make()
            ->title('Laporan berhasil dimuat')
            ->success()
            ->send();
    }

    protected function getLaporanData(): Collection
    {
        $tanggalMulai = $this->parseDateInput($this->tanggalMulai)->startOfDay();
        $tanggalSelesai = $this->parseDateInput($this->tanggalSelesai)->endOfDay();

        $query = DaftarPengecekan::with([
            'operator',
            'komponenMesins',
            'pengecekan' => function ($query) use ($tanggalMulai, $tanggalSelesai) {
                $query->whereBetween('tanggal_pengecekan', [
                    $tanggalMulai,
                    $tanggalSelesai,
                ])->with(['detailPengecekan', 'operator']);
            },
        ]);

        if ($this->mesinId) {
            $query->where('id', $this->mesinId);
        }

        return $query->get();
    }

    public function exportPdf(): void
    {
        $data = $this->form->getState();

        $this->tanggalMulai = $data['tanggal_mulai'];
        $this->tanggalSelesai = $data['tanggal_selesai'];
        $this->mesinId = $data['mesin_id'];
        $this->filterPeriode = $data['filter_periode'];

        $laporanData = $this->getLaporanData();

        if ($laporanData->isEmpty()) {
            Notification::make()
                ->title('Tidak ada data')
                ->body('Tidak ada data pengecekan pada periode yang dipilih.')
                ->warning()
                ->send();
            return;
        }

        $url = route('laporan.export-pdf', [
            'tanggal_mulai' => $this->tanggalMulai,
            'tanggal_selesai' => $this->tanggalSelesai,
            'mesin_id' => $this->mesinId,
            'filter_periode' => $this->filterPeriode,
        ]);

        $this->js("window.open('{$url}', '_blank')");
    }

    public function exportExcel(): void
    {
        $data = $this->form->getState();

        $this->tanggalMulai = $data['tanggal_mulai'];
        $this->tanggalSelesai = $data['tanggal_selesai'];
        $this->mesinId = $data['mesin_id'];
        $this->filterPeriode = $data['filter_periode'];

        $laporanData = $this->getLaporanData();

        if ($laporanData->isEmpty()) {
            Notification::make()
                ->title('Tidak ada data')
                ->body('Tidak ada data pengecekan pada periode yang dipilih.')
                ->warning()
                ->send();
            return;
        }

        $url = route('laporan.export-excel', [
            'tanggal_mulai' => $this->tanggalMulai,
            'tanggal_selesai' => $this->tanggalSelesai,
            'mesin_id' => $this->mesinId,
            'filter_periode' => $this->filterPeriode,
        ]);

        $this->js("window.open('{$url}', '_blank')");
    }

    protected function generateNomorDokumen(): string
    {
        $tahun = now()->format('Y');
        $bulan = now()->format('m');

        // Format: DOC/CKMS/YYYY/MM/XXXX
        $sequence = PengecekanMesin::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->count() + 1;

        return sprintf('DOC/CKMS/%s/%s/%04d', $tahun, $bulan, $sequence);
    }

    protected function parseDateInput(?string $value): Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return now();
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            return Carbon::createFromFormat('d/m/Y', $value);
        }

        return Carbon::parse($value);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('tampilkan')
                ->label('Tampilkan Laporan')
                ->icon(Heroicon::OutlinedEye)
                ->color('primary')
                ->action('tampilkanLaporan'),

            Action::make('export_pdf')
                ->label('Export PDF')
                ->icon(Heroicon::OutlinedDocumentArrowDown)
                ->color('danger')
                ->action('exportPdf'),

            Action::make('export_excel')
                ->label('Export Excel')
                ->icon(Heroicon::OutlinedTableCells)
                ->color('success')
                ->action('exportExcel'),
        ];
    }

    public function getSubheading(): ?string
    {
        if ($this->showPreview && $this->laporanData->isNotEmpty()) {
            $tanggalMulai = Carbon::parse($this->tanggalMulai)->translatedFormat('d F Y');
            $tanggalSelesai = Carbon::parse($this->tanggalSelesai)->translatedFormat('d F Y');
            return "Periode: {$tanggalMulai} - {$tanggalSelesai}";
        }

        return 'Pilih filter dan klik "Tampilkan Laporan" untuk melihat data';
    }

    public function getSummaryData(): array
    {
        if (!$this->showPreview || $this->laporanData->isEmpty()) {
            return [
                'total_daftar_pengecekan' => 0,
                'total_pengecekan' => 0,
                'total_sesuai' => 0,
                'total_tidak_sesuai' => 0,
            ];
        }

        $totalPengecekan = $this->laporanData->sum(fn($m) => $m->pengecekan->count());
        $totalSesuai = 0;
        $totalTidakSesuai = 0;
        
        foreach ($this->laporanData as $mesin) {
            foreach ($mesin->pengecekan as $p) {
                $totalSesuai += $p->detailPengecekan->where('status_sesuai', 'sesuai')->count();
                $totalTidakSesuai += $p->detailPengecekan->where('status_sesuai', 'tidak_sesuai')->count();
            }
        }

        return [
            'total_daftar_pengecekan' => $this->laporanData->count(),
            'total_pengecekan' => $totalPengecekan,
            'total_sesuai' => $totalSesuai,
            'total_tidak_sesuai' => $totalTidakSesuai,
        ];
    }
}
