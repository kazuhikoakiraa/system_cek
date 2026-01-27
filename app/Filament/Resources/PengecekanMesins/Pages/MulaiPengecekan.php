<?php

namespace App\Filament\Resources\PengecekanMesins\Pages;

use App\Filament\Resources\PengecekanMesins\PengecekanMesinResource;
use App\Models\DetailPengecekanMesin;
use App\Models\KomponenMesin;
use App\Models\Mesin;
use App\Models\PengecekanMesin;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MulaiPengecekan extends Page implements HasForms
{
    use InteractsWithForms;
    
    protected static string $resource = PengecekanMesinResource::class;

    protected static ?string $title = 'Mulai Pengecekan Mesin';

    public ?array $data = [];

    public function getView(): string
    {
        return 'filament.resources.pengecekan-mesins.pages.mulai-pengecekan';
    }

    public function mount(): void
    {
        // Check if user has any machines assigned
        $userMachinesCount = Mesin::where('user_id', Auth::id())->count();
        
        if ($userMachinesCount === 0) {
            Notification::make()
                ->warning()
                ->title('Tidak Ada Mesin')
                ->body('Anda belum memiliki mesin yang ditugaskan. Silakan hubungi administrator.')
                ->persistent()
                ->send();
        }

        // Check if user has any unchecked machines today
        $today = Carbon::today();
        $uncheckedMachinesCount = Mesin::where('user_id', Auth::id())
            ->whereDoesntHave('pengecekan', function ($query) use ($today) {
                $query->whereDate('tanggal_pengecekan', $today);
            })
            ->count();

        if ($userMachinesCount > 0 && $uncheckedMachinesCount === 0) {
            Notification::make()
                ->success()
                ->title('Semua Mesin Sudah Dicek')
                ->body('Semua mesin Anda sudah dilakukan pengecekan hari ini.')
                ->persistent()
                ->send();
        }

        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Waktu Pengecekan')
                    ->description('Waktu pengecekan akan tersimpan secara otomatis')
                    ->schema([
                        Placeholder::make('waktu_real_time')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString('
                                <div x-data="{ 
                                    currentTime: \'\',
                                    updateTime() {
                                        const days = [\'Minggu\', \'Senin\', \'Selasa\', \'Rabu\', \'Kamis\', \'Jumat\', \'Sabtu\'];
                                        const months = [\'Januari\', \'Februari\', \'Maret\', \'April\', \'Mei\', \'Juni\', \'Juli\', \'Agustus\', \'September\', \'Oktober\', \'November\', \'Desember\'];
                                        const now = new Date();
                                        const dayName = days[now.getDay()];
                                        const day = now.getDate();
                                        const monthName = months[now.getMonth()];
                                        const year = now.getFullYear();
                                        const hours = String(now.getHours()).padStart(2, \'0\');
                                        const minutes = String(now.getMinutes()).padStart(2, \'0\');
                                        const seconds = String(now.getSeconds()).padStart(2, \'0\');
                                        this.currentTime = `${dayName}, ${day} ${monthName} ${year} - ${hours}:${minutes}:${seconds}`;
                                    }
                                }" 
                                x-init="updateTime(); setInterval(() => updateTime(), 1000)"
                                class="flex items-center gap-2">
                                    <span class="text-sm text-gray-700 dark:text-gray-300" x-text="currentTime"></span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                        Real-time
                                    </span>
                                </div>
                            '))
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Section::make('Pilih Mesin untuk Pengecekan')
                    ->schema([
                        Select::make('mesin_id')
                            ->label('Mesin')
                            ->options(function () {
                                /** @var User $user */
                                $user = Auth::user();
                                $today = Carbon::today();

                                // Ambil mesin yang operatornya adalah user yang login
                                $mesins = Mesin::where('user_id', $user->id)
                                    // Cek mesin yang belum dicek hari ini
                                    ->whereDoesntHave('pengecekan', function ($query) use ($today) {
                                        $query->whereDate('tanggal_pengecekan', $today);
                                    })
                                    // Filter mesin yang memiliki komponen yang bisa dicek
                                    ->whereHas('komponenMesins', function ($query) {
                                        $query->where(function ($q) {
                                            // Komponen harian selalu bisa dicek
                                            $q->where('frekuensi', 'harian')
                                            // Atau komponen yang belum pernah dicek
                                            ->orWhereDoesntHave('detailPengecekan');
                                        });
                                    })
                                    ->pluck('nama_mesin', 'id');

                                return $mesins;
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $livewire) {
                                if ($state) {
                                    $komponenList = KomponenMesin::where('mesin_id', $state)
                                        ->get()
                                        ->map(function ($komponen) {
                                            $isCheckable = $komponen->isCheckable();
                                            $lastCheck = $komponen->getLastCheck();
                                            $nextCheckDate = $komponen->getNextCheckDate();
                                            
                                            return [
                                                'komponen_mesin_id' => $komponen->id,
                                                'nama_komponen' => $komponen->nama_komponen,
                                                'standar' => $komponen->standar,
                                                'frekuensi' => $komponen->frekuensi,
                                                'is_checkable' => $isCheckable,
                                                'last_check_status' => $lastCheck ? $lastCheck->status_sesuai : null,
                                                'last_check_keterangan' => $lastCheck ? $lastCheck->keterangan : null,
                                                'last_check_date' => $lastCheck && $lastCheck->pengecekanMesin ? 
                                                    $lastCheck->pengecekanMesin->tanggal_pengecekan->format('d/m/Y H:i') : null,
                                                'next_check_date' => $nextCheckDate ? $nextCheckDate->format('d/m/Y') : null,
                                                'status_sesuai' => null,
                                                'keterangan' => null,
                                            ];
                                        })
                                        ->toArray();
                                    
                                    $set('komponen', $komponenList);
                                } else {
                                    $set('komponen', []);
                                }
                            })
                            ->searchable()
                            ->placeholder('Pilih mesin yang akan diperiksa')
                            ->helperText('Hanya mesin yang belum dicek hari ini yang ditampilkan'),
                    ]),

                Section::make('Checklist Komponen Mesin')
                    ->schema([
                        Repeater::make('komponen')
                            ->schema([
                                \Filament\Forms\Components\Hidden::make('komponen_mesin_id'),
                                
                                \Filament\Forms\Components\Hidden::make('nama_komponen'),
                                
                                \Filament\Forms\Components\Hidden::make('standar'),
                                
                                \Filament\Forms\Components\Hidden::make('frekuensi'),
                                
                                \Filament\Forms\Components\Hidden::make('is_checkable'),
                                
                                \Filament\Forms\Components\Hidden::make('last_check_status'),
                                
                                \Filament\Forms\Components\Hidden::make('last_check_keterangan'),
                                
                                \Filament\Forms\Components\Hidden::make('last_check_date'),
                                
                                \Filament\Forms\Components\Hidden::make('next_check_date'),
                                
                                Placeholder::make('info_komponen')
                                    ->label('Komponen')
                                    ->content(fn ($get) => $get('nama_komponen') ?? '-'),
                                
                                Placeholder::make('info_standar')
                                    ->label('Standar')
                                    ->content(fn ($get) => $get('standar') ?? '-'),
                                
                                Placeholder::make('info_frekuensi')
                                    ->label('Frekuensi')
                                    ->content(function ($get) {
                                        $frekuensi = match($get('frekuensi')) {
                                            'harian' => 'Harian',
                                            'mingguan' => 'Mingguan',
                                            'bulanan' => 'Bulanan',
                                            default => '-'
                                        };
                                        
                                        $isCheckable = $get('is_checkable');
                                        $nextCheckDate = $get('next_check_date');
                                        
                                        if (!$isCheckable && $nextCheckDate) {
                                            return new \Illuminate\Support\HtmlString(
                                                '<div>' . $frekuensi . 
                                                '<br><span class="text-xs text-orange-600 dark:text-orange-400">Berikutnya: ' . 
                                                $nextCheckDate . '</span></div>'
                                            );
                                        }
                                        
                                        return $frekuensi;
                                    }),
                                
                                // Tampilkan hasil pengecekan sebelumnya jika tidak bisa dicek
                                Placeholder::make('last_check_info')
                                    ->label('Hasil Pengecekan Terakhir')
                                    ->content(function ($get) {
                                        $lastStatus = $get('last_check_status');
                                        $lastKeterangan = $get('last_check_keterangan');
                                        $lastDate = $get('last_check_date');
                                        
                                        if (!$lastStatus) {
                                            return new \Illuminate\Support\HtmlString(
                                                '<span class="text-sm text-gray-500">Belum pernah dicek</span>'
                                            );
                                        }
                                        
                                        $statusColor = $lastStatus === 'sesuai' ? 'green' : 'red';
                                        $statusText = $lastStatus === 'sesuai' ? 'Sesuai' : 'Tidak Sesuai';
                                        
                                        $html = '<div class="space-y-1">';
                                        $html .= '<div><span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-' . $statusColor . '-100 text-' . $statusColor . '-800 dark:bg-' . $statusColor . '-900 dark:text-' . $statusColor . '-200">' . $statusText . '</span></div>';
                                        if ($lastKeterangan) {
                                            $html .= '<div class="text-sm text-gray-600 dark:text-gray-400">' . $lastKeterangan . '</div>';
                                        }
                                        $html .= '<div class="text-xs text-gray-500">Dicek: ' . $lastDate . '</div>';
                                        $html .= '</div>';
                                        
                                        return new \Illuminate\Support\HtmlString($html);
                                    })
                                    ->visible(fn ($get) => !$get('is_checkable'))
                                    ->columnSpanFull(),
                                
                                Radio::make('status_sesuai')
                                    ->label('Status')
                                    ->options([
                                        'sesuai' => 'Sesuai',
                                        'tidak_sesuai' => 'Tidak Sesuai',
                                    ])
                                    ->inline()
                                    ->required(fn ($get) => $get('is_checkable'))
                                    ->disabled(fn ($get) => !$get('is_checkable'))
                                    ->visible(fn ($get) => $get('is_checkable'))
                                    ->live(),

                                Textarea::make('keterangan')
                                    ->label('Keterangan')
                                    ->visible(fn ($get) => $get('is_checkable') && $get('status_sesuai') === 'tidak_sesuai')
                                    ->required(fn ($get) => $get('is_checkable') && $get('status_sesuai') === 'tidak_sesuai')
                                    ->disabled(fn ($get) => !$get('is_checkable'))
                                    ->rows(3)
                                    ->placeholder('Jelaskan ketidaksesuaian yang ditemukan')
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->defaultItems(0)
                            ->live()
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($get) => filled($get('mesin_id'))),
            ])
            ->statePath('data');
    }

    protected function loadKomponenMesin(?int $mesinId): void
    {
        if (!$mesinId) {
            $this->data['komponen'] = [];
            return;
        }

        $komponenList = KomponenMesin::where('mesin_id', $mesinId)
            ->get()
            ->map(function ($komponen) {
                $isCheckable = $komponen->isCheckable();
                $lastCheck = $komponen->getLastCheck();
                $nextCheckDate = $komponen->getNextCheckDate();
                
                return [
                    'komponen_mesin_id' => $komponen->id,
                    'nama_komponen' => $komponen->nama_komponen,
                    'standar' => $komponen->standar,
                    'frekuensi' => $komponen->frekuensi,
                    'is_checkable' => $isCheckable,
                    'last_check_status' => $lastCheck ? $lastCheck->status_sesuai : null,
                    'last_check_keterangan' => $lastCheck ? $lastCheck->keterangan : null,
                    'last_check_date' => $lastCheck && $lastCheck->pengecekanMesin ? 
                        $lastCheck->pengecekanMesin->tanggal_pengecekan->format('d/m/Y H:i') : null,
                    'next_check_date' => $nextCheckDate ? $nextCheckDate->format('d/m/Y') : null,
                    'status_sesuai' => null,
                    'keterangan' => null,
                ];
            })
            ->toArray();

        $this->data['komponen'] = $komponenList;
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('simpan')
                ->label('Simpan Pengecekan')
                ->action('simpanPengecekan')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Simpan Pengecekan')
                ->modalDescription('Apakah Anda yakin data pengecekan sudah benar?')
                ->modalSubmitActionLabel('Ya, Simpan')
                ->extraAttributes(['class' => 'mt-8']),
        ];
    }

    public function simpanPengecekan(): void
    {
        $data = $this->form->getState();

        // Validasi mesin_id
        if (!isset($data['mesin_id'])) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Silakan pilih mesin terlebih dahulu.')
                ->send();
            return;
        }

        // Validasi komponen
        if (empty($data['komponen'])) {
            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Tidak ada komponen untuk diperiksa.')
                ->send();
            return;
        }

        // Filter hanya komponen yang checkable dan memiliki status
        $checkableKomponen = array_filter($data['komponen'], function ($komponen) {
            return isset($komponen['is_checkable']) && 
                   $komponen['is_checkable'] && 
                   isset($komponen['status_sesuai']);
        });

        // Jika tidak ada komponen yang bisa dicek hari ini
        if (empty($checkableKomponen)) {
            Notification::make()
                ->warning()
                ->title('Tidak Ada Komponen')
                ->body('Tidak ada komponen yang perlu dicek hari ini berdasarkan frekuensi masing-masing.')
                ->send();
            return;
        }

        // Cek apakah user adalah operator dari mesin ini
        $mesin = Mesin::find($data['mesin_id']);
        if ($mesin && $mesin->user_id !== Auth::id()) {
            Notification::make()
                ->danger()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki akses untuk memeriksa mesin ini.')
                ->send();
            return;
        }

        // Cek apakah sudah ada pengecekan hari ini untuk mesin ini
        $today = Carbon::today();
        $existingCheck = PengecekanMesin::where('mesin_id', $data['mesin_id'])
            ->whereDate('tanggal_pengecekan', $today)
            ->exists();

        if ($existingCheck) {
            Notification::make()
                ->warning()
                ->title('Sudah Dicek')
                ->body('Mesin ini sudah diperiksa hari ini.')
                ->send();
            return;
        }

        // Cek apakah ada komponen harian yang perlu dicek
        $hasHarianKomponen = false;
        foreach ($checkableKomponen as $komponen) {
            if (isset($komponen['frekuensi']) && $komponen['frekuensi'] === 'harian') {
                $hasHarianKomponen = true;
                break;
            }
        }

        // Jika tidak ada komponen harian dan semua komponen belum waktunya, berarti tidak perlu buat pengecekan baru
        if (!$hasHarianKomponen && empty($checkableKomponen)) {
            Notification::make()
                ->info()
                ->title('Belum Waktunya')
                ->body('Semua komponen belum mencapai waktu pengecekan berikutnya.')
                ->send();
            return;
        }

        try {
            DB::beginTransaction();

            // Simpan pengecekan dengan timestamp real-time
            $pengecekan = PengecekanMesin::create([
                'mesin_id' => $data['mesin_id'],
                'user_id' => Auth::id(),
                'tanggal_pengecekan' => Carbon::now(), // Real-time datetime
                'status' => 'selesai',
            ]);

            // Simpan detail pengecekan - hanya untuk komponen yang checkable
            $savedCount = 0;
            foreach ($data['komponen'] as $komponen) {
                // Skip jika tidak checkable atau tidak ada status
                if (!isset($komponen['is_checkable']) || 
                    !$komponen['is_checkable'] || 
                    !isset($komponen['status_sesuai'])) {
                    continue;
                }

                DetailPengecekanMesin::create([
                    'pengecekan_mesin_id' => $pengecekan->id,
                    'komponen_mesin_id' => $komponen['komponen_mesin_id'],
                    'status_sesuai' => $komponen['status_sesuai'],
                    'keterangan' => $komponen['keterangan'] ?? null,
                ]);
                
                $savedCount++;
            }

            DB::commit();

            Notification::make()
                ->success()
                ->title('Berhasil')
                ->body("Pengecekan mesin berhasil disimpan. {$savedCount} komponen dicek.")
                ->send();

            // Reset form dan redirect ke list
            $this->data = [];
            $this->redirect(PengecekanMesinResource::getUrl('index'));
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->danger()
                ->title('Error')
                ->body('Gagal menyimpan pengecekan: ' . $e->getMessage())
                ->send();
        }
    }
}
