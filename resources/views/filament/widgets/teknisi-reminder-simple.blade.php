<x-filament-widgets::widget>
    @php
        $data = $this->getMessage();
        $color = $data['type'] === 'success' ? 'success' : 'warning';
    @endphp
    
    <x-filament::section 
        :icon="$data['icon']"
        :icon-color="$color"
    >
        <x-slot name="heading">
            {{ $data['title'] }}
        </x-slot>
        
        <div class="text-sm">
            {{ $data['message'] }}
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
