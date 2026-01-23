<x-filament-panels::page>
    <div x-data="{ 
        currentTime: @js(\Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y - HH:mm:ss')),
        updateTime() {
            this.currentTime = new Date().toLocaleString('id-ID', {
                weekday: 'long',
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
        }
    }" 
    x-init="setInterval(() => updateTime(), 1000)">
        
        <div class="mb-4 p-4 bg-primary-50 dark:bg-gray-800 rounded-lg border border-primary-200 dark:border-gray-700">
            <div class="flex items-center gap-2">
                <x-filament::icon
                    icon="heroicon-o-clock"
                    class="h-5 w-5 text-primary-500"
                />
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Waktu Pengecekan Real-time</p>
                    <p class="text-lg font-semibold text-primary-600 dark:text-primary-400" x-text="currentTime"></p>
                </div>
            </div>
        </div>

        <form wire:submit="simpanPengecekan">
            {{ $this->form }}

            <div class="mt-6 flex gap-3">
                @foreach ($this->getFormActions() as $action)
                    {{ $action }}
                @endforeach
            </div>
        </form>
    </div>
</x-filament-panels::page>
