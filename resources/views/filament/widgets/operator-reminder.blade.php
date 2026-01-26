<x-filament-widgets::widget>
    <x-filament::section>
        @php
            $data = $this->getViewData();
        @endphp

        @if($data['sudahMengecek'])
            <div class="flex items-center gap-4 p-4">
                <div class="flex-shrink-0">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-success-100 dark:bg-success-900/20">
                        <svg class="h-10 w-10 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        Terima Kasih, {{ $data['namaUser'] }}! 🎉
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Anda telah menyelesaikan <span class="font-semibold text-success-600 dark:text-success-400">{{ $data['jumlahDicek'] }} pengecekan</span> hari ini.
                    </p>
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-500">
                        Kerja keras Anda sangat membantu kelancaran operasional. Tetap semangat!
                    </p>
                </div>
            </div>
        @else
            <div class="flex items-center gap-4 p-4">
                <div class="flex-shrink-0">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-warning-100 dark:bg-warning-900/20">
                        <svg class="h-10 w-10 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                        Pengingat Pengecekan, {{ $data['namaUser'] }} 📋
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Anda belum melakukan pengecekan mesin hari ini.
                    </p>
                    <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-500">
                        Jangan lupa untuk mengecek mesin yang menjadi tanggung jawab Anda. Pengecekan rutin sangat penting untuk menjaga kualitas produksi.
                    </p>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
