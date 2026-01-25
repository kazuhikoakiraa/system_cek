<x-filament-panels::page>
    {{-- Filter Form --}}
    <x-filament::section>
        {{ $this->form }}
    </x-filament::section>

    {{-- Preview Section --}}
    @if($this->showPreview && $this->laporanData->isNotEmpty())
        @php
            $totalPengecekan = $this->laporanData->sum(fn($m) => $m->pengecekan->count());
            $totalSesuai = 0;
            $totalTidakSesuai = 0;
            $totalTidakDicek = 0;
            foreach ($this->laporanData as $mesin) {
                foreach ($mesin->pengecekan as $p) {
                    $totalSesuai += $p->detailPengecekan->where('status_sesuai', 'sesuai')->count();
                    $totalTidakSesuai += $p->detailPengecekan->where('status_sesuai', 'tidak_sesuai')->count();
                    $totalTidakDicek += $p->detailPengecekan->where('status_sesuai', 'tidak_dicek')->count();
                }
            }
        @endphp

        {{-- Summary Table --}}
        <x-filament::section>
            <x-slot name="heading">Ringkasan Laporan</x-slot>
            <x-slot name="description">Periode: {{ \Carbon\Carbon::parse($this->tanggalMulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($this->tanggalSelesai)->format('d/m/Y') }}</x-slot>

            <div class="overflow-x-auto">
                <table class="w-full text-sm border border-gray-200 dark:border-gray-700">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold border-b border-gray-200 dark:border-gray-700">Keterangan</th>
                            <th class="px-4 py-2 text-center font-semibold border-b border-gray-200 dark:border-gray-700">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="px-4 py-2">Total Mesin</td>
                            <td class="px-4 py-2 text-center font-bold">{{ $this->laporanData->count() }}</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="px-4 py-2">Total Pengecekan</td>
                            <td class="px-4 py-2 text-center font-bold text-blue-600 dark:text-blue-400">{{ $totalPengecekan }}</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="px-4 py-2">Total Sesuai (OK)</td>
                            <td class="px-4 py-2 text-center font-bold text-green-600 dark:text-green-400">{{ $totalSesuai }}</td>
                        </tr>
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="px-4 py-2">Total Tidak Sesuai (NG)</td>
                            <td class="px-4 py-2 text-center font-bold text-red-600 dark:text-red-400">{{ $totalTidakSesuai }}</td>
                        </tr>
                        <tr>
                            <td class="px-4 py-2">Total Tidak Dicek</td>
                            <td class="px-4 py-2 text-center font-bold text-yellow-600 dark:text-yellow-400">{{ $totalTidakDicek }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        {{-- Data Per Mesin --}}
        <x-filament::section>
            <x-slot name="heading">Detail Per Mesin</x-slot>
            <x-slot name="description">Menampilkan {{ $this->laporanData->count() }} mesin</x-slot>

            <div class="space-y-6">
                @foreach($this->laporanData as $mesin)
                    @php
                        $mesinSesuai = 0;
                        $mesinTidakSesuai = 0;
                        $mesinTidakDicek = 0;
                        foreach ($mesin->pengecekan as $p) {
                            $mesinSesuai += $p->detailPengecekan->where('status_sesuai', 'sesuai')->count();
                            $mesinTidakSesuai += $p->detailPengecekan->where('status_sesuai', 'tidak_sesuai')->count();
                            $mesinTidakDicek += $p->detailPengecekan->where('status_sesuai', 'tidak_dicek')->count();
                        }
                    @endphp

                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        {{-- Mesin Header --}}
                        <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white">{{ $mesin->nama_mesin }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Operator: {{ $mesin->operator?->name ?? '-' }} |
                                        {{ $mesin->komponenMesins->count() }} komponen |
                                        {{ $mesin->pengecekan->count() }} pengecekan
                                    </p>
                                </div>
                                <div class="text-sm">
                                    <span class="text-green-600 dark:text-green-400 font-semibold">OK: {{ $mesinSesuai }}</span> |
                                    <span class="text-red-600 dark:text-red-400 font-semibold">NG: {{ $mesinTidakSesuai }}</span> |
                                    <span class="text-yellow-600 dark:text-yellow-400 font-semibold">N/A: {{ $mesinTidakDicek }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Komponen Table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-100 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 w-10">No</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">Komponen</th>
                                        <th class="px-3 py-2 text-left font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">Standar</th>
                                        <th class="px-3 py-2 text-center font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 w-20">Frekuensi</th>
                                        <th class="px-3 py-2 text-center font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 w-16">OK</th>
                                        <th class="px-3 py-2 text-center font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 w-16">NG</th>
                                        <th class="px-3 py-2 text-center font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 w-16">N/A</th>
                                        <th class="px-3 py-2 text-center font-semibold text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 w-20">%OK</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($mesin->komponenMesins as $index => $komponen)
                                        @php
                                            $sesuai = 0;
                                            $tidakSesuai = 0;
                                            $tidakDicek = 0;

                                            foreach ($mesin->pengecekan as $pengecekan) {
                                                $detail = $pengecekan->detailPengecekan
                                                    ->first(fn($d) => $d->komponen_mesin_id === $komponen->id);
                                                if ($detail) {
                                                    if ($detail->status_sesuai === 'sesuai') $sesuai++;
                                                    elseif ($detail->status_sesuai === 'tidak_sesuai') $tidakSesuai++;
                                                    elseif ($detail->status_sesuai === 'tidak_dicek') $tidakDicek++;
                                                }
                                            }

                                            $total = $sesuai + $tidakSesuai;
                                            $persentase = $total > 0 ? round(($sesuai / $total) * 100, 1) : 0;
                                        @endphp
                                        <tr class="border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                            <td class="px-3 py-2 font-medium text-gray-900 dark:text-white">{{ $komponen->nama_komponen }}</td>
                                            <td class="px-3 py-2 text-gray-600 dark:text-gray-400">{{ $komponen->standar ?? '-' }}</td>
                                            <td class="px-3 py-2 text-center text-gray-600 dark:text-gray-400">{{ ucfirst($komponen->frekuensi ?? 'harian') }}</td>
                                            <td class="px-3 py-2 text-center font-semibold text-green-600 dark:text-green-400">{{ $sesuai }}</td>
                                            <td class="px-3 py-2 text-center font-semibold text-red-600 dark:text-red-400">{{ $tidakSesuai }}</td>
                                            <td class="px-3 py-2 text-center font-semibold text-yellow-600 dark:text-yellow-400">{{ $tidakDicek }}</td>
                                            <td class="px-3 py-2 text-center">
                                                @if($total > 0)
                                                    <span class="font-semibold {{ $persentase >= 80 ? 'text-green-600' : ($persentase >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                                        {{ $persentase }}%
                                                    </span>
                                                @else
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-3 py-4 text-center text-gray-500 dark:text-gray-400">
                                                Tidak ada komponen untuk mesin ini
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
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
                    <li><span class="text-yellow-600 font-semibold">N/A</span> = Tidak Dicek</li>
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
