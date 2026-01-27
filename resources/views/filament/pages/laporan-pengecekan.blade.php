<x-filament-panels::page>
    {{-- Filter Form --}}
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    {{-- Preview Section --}}
    @if($this->showPreview && $this->laporanData->isNotEmpty())
        @php
            $summary = $this->getSummaryData();
        @endphp

        {{-- Summary Cards --}}
        <div wire:key="laporan-summary-{{ $this->tanggalMulai ?? '' }}-{{ $this->tanggalSelesai ?? '' }}-{{ $this->mesinId ?? 'all' }}">
            @livewire(\App\Filament\Widgets\LaporanPengecekanSummary::class, ['summary' => $summary])
        </div>

        {{-- Detail Table --}}
        <x-filament::section>
            <x-slot name="heading">Detail Laporan Pengecekan</x-slot>
            <x-slot name="description">
                Periode: {{ \Carbon\Carbon::parse($this->tanggalMulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($this->tanggalSelesai)->format('d/m/Y') }}
            </x-slot>

            <div class="fi-ta-ctn divide-y divide-gray-200 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:divide-white/10 dark:bg-gray-900 dark:ring-white/10">
                <div class="fi-ta-content relative divide-y divide-gray-200 dark:divide-white/10 dark:border-t-white/10" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5" style="min-width: 1000px;">
                        <thead class="divide-y divide-gray-200 dark:divide-white/5">
                            <tr class="bg-gray-50 dark:bg-white/5">
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Daftar Pengecekan
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5">
                                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Operator
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5">
                                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Komponen
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5">
                                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-start">
                                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Standar
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5">
                                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-center">
                                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            Frekuensi
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5">
                                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-center">
                                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            OK
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5">
                                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-center">
                                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            NG
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5">
                                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-center">
                                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            N/A
                                        </span>
                                    </span>
                                </th>
                                <th class="fi-ta-header-cell px-3 py-3.5 sm:first-of-type:ps-6 sm:last-of-type:pe-6">
                                    <span class="group flex w-full items-center gap-x-1 whitespace-nowrap justify-center">
                                        <span class="fi-ta-header-cell-label text-sm font-semibold text-gray-950 dark:text-white">
                                            % OK
                                        </span>
                                    </span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 whitespace-nowrap dark:divide-white/5">
                            @foreach($this->laporanData as $mesin)
                                @foreach($mesin->komponenMesins as $komponen)
                                    @php
                                        $sesuai = 0;
                                        $tidakSesuai = 0;
                                        $totalPengecekan = $mesin->pengecekan->count();

                                        foreach ($mesin->pengecekan as $pengecekan) {
                                            $detail = $pengecekan->detailPengecekan
                                                ->first(fn($d) => $d->komponen_mesin_id === $komponen->id);
                                            if ($detail) {
                                                if ($detail->status_sesuai === 'sesuai') $sesuai++;
                                                elseif ($detail->status_sesuai === 'tidak_sesuai') $tidakSesuai++;
                                            }
                                        }

                                        $total = $sesuai + $tidakSesuai;
                                        $tidakDicek = $totalPengecekan - $total;
                                        $persentase = $total > 0 ? round(($sesuai / $total) * 100, 1) : 0;
                                    @endphp
                                    <tr class="fi-ta-row [@media(hover:hover)]:transition [@media(hover:hover)]:duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="fi-ta-col-wrp">
                                                <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                                    <div class="flex">
                                                        <div class="flex max-w-max" style="">
                                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5">
                                                                <span class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white font-semibold">
                                                                    {{ $mesin->nama_mesin }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="fi-ta-col-wrp">
                                                <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                                    <div class="flex">
                                                        <div class="flex max-w-max" style="">
                                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5">
                                                                <span class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white">
                                                                    {{ $mesin->operator?->name ?? '-' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="fi-ta-col-wrp">
                                                <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                                    <div class="flex">
                                                        <div class="flex max-w-max" style="">
                                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5">
                                                                <span class="fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white">
                                                                    {{ $komponen->nama_komponen }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="fi-ta-col-wrp">
                                                <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                                    <div class="flex">
                                                        <div class="flex max-w-max" style="">
                                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5">
                                                                <span class="fi-ta-text-item-label text-sm leading-6 text-gray-500 dark:text-gray-400">
                                                                    {{ $komponen->standar ?? '-' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="fi-ta-col-wrp">
                                                <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                                    <div class="flex justify-center">
                                                        <div class="flex max-w-max">
                                                            <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 
                                                                @if(ucfirst($komponen->frekuensi ?? 'harian') === 'Harian') 
                                                                    fi-color-success bg-success-50 text-success-600 ring-success-600/10 dark:bg-success-400/10 dark:text-success-400 dark:ring-success-400/30
                                                                @elseif(ucfirst($komponen->frekuensi ?? 'harian') === 'Mingguan')
                                                                    fi-color-info bg-info-50 text-info-600 ring-info-600/10 dark:bg-info-400/10 dark:text-info-400 dark:ring-info-400/30
                                                                @elseif(ucfirst($komponen->frekuensi ?? 'harian') === 'Bulanan')
                                                                    fi-color-warning bg-warning-50 text-warning-600 ring-warning-600/10 dark:bg-warning-400/10 dark:text-warning-400 dark:ring-warning-400/30
                                                                @else
                                                                    fi-color-gray bg-gray-50 text-gray-600 ring-gray-600/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-400/20
                                                                @endif">
                                                                {{ ucfirst($komponen->frekuensi ?? 'harian') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="fi-ta-col-wrp">
                                                <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                                    <div class="flex justify-center">
                                                        <div class="flex max-w-max">
                                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5">
                                                                <span class="fi-ta-text-item-label text-sm leading-6 text-success-600 dark:text-success-400 font-semibold">
                                                                    {{ $sesuai }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="fi-ta-col-wrp">
                                                <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                                    <div class="flex justify-center">
                                                        <div class="flex max-w-max">
                                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5">
                                                                <span class="fi-ta-text-item-label text-sm leading-6 text-danger-600 dark:text-danger-400 font-semibold">
                                                                    {{ $tidakSesuai }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="fi-ta-col-wrp">
                                                <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                                    <div class="flex justify-center">
                                                        <div class="flex max-w-max">
                                                            <div class="fi-ta-text-item inline-flex items-center gap-1.5">
                                                                <span class="fi-ta-text-item-label text-sm leading-6 text-warning-600 dark:text-warning-400 font-semibold">
                                                                    {{ $tidakDicek }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fi-ta-cell p-0 first-of-type:ps-1 last-of-type:pe-1 sm:first-of-type:ps-3 sm:last-of-type:pe-3">
                                            <div class="fi-ta-col-wrp">
                                                <div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
                                                    <div class="flex justify-center">
                                                        <div class="flex max-w-max">
                                                            @if($total > 0)
                                                                <div class="fi-ta-text-item inline-flex items-center gap-1.5">
                                                                    <span class="fi-ta-text-item-label text-sm leading-6 font-bold
                                                                        @if($persentase >= 80) text-success-600 dark:text-success-400
                                                                        @elseif($persentase >= 50) text-warning-600 dark:text-warning-400
                                                                        @else text-danger-600 dark:text-danger-400
                                                                        @endif">
                                                                        {{ $persentase }}%
                                                                    </span>
                                                                </div>
                                                            @else
                                                                <div class="fi-ta-text-item inline-flex items-center gap-1.5">
                                                                    <span class="fi-ta-text-item-label text-sm leading-6 text-gray-400">
                                                                        -
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </x-filament::section>

        {{-- Keterangan --}}
        <x-filament::section collapsed>
            <x-slot name="heading">Keterangan</x-slot>

            <div class="text-sm text-gray-600 dark:text-gray-400">
                <p class="mb-2"><strong>Keterangan Status:</strong></p>
                <ul class="list-disc list-inside space-y-1 mb-4">
                    <li><span class="text-green-600 font-semibold">OK</span> = Sesuai/Kondisi Baik</li>
                    <li><span class="text-red-600 font-semibold">NG</span> = Tidak Sesuai/Perlu Perbaikan</li>
                </ul>
                <p class="mb-2"><strong>Format Export:</strong></p>
                <ul class="list-disc list-inside space-y-1">
                    <li><strong>PDF:</strong> Format Check Sheet landscape A4</li>
                    <li><strong>Excel:</strong> Multi-sheet dengan detail lengkap</li>
                </ul>
            </div>
        </x-filament::section>

    @elseif($this->showPreview && $this->laporanData->isEmpty())
        {{-- Empty State --}}
        <x-filament::section>
            <div class="text-center py-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tidak Ada Data</h3>
                <p class="mt-1 text-gray-500 dark:text-gray-400">
                    Tidak ditemukan data pengecekan mesin pada periode yang dipilih.
                </p>
            </div>
        </x-filament::section>

    @else
        {{-- Initial State --}}
        <x-filament::section>
            <div class="text-center py-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pilih Filter Laporan</h3>
                <p class="mt-1 text-gray-500 dark:text-gray-400">
                    Atur filter periode dan klik <strong>"Tampilkan Laporan"</strong> untuk melihat preview data.
                </p>
            </div>
        </x-filament::section>
    @endif
</x-filament-panels::page>
