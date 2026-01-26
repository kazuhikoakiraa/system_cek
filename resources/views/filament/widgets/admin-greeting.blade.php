<x-filament-widgets::widget>
    @php
        $data = $this->getMessage();
    @endphp
    
    <x-filament::section 
        :icon="$data['icon']"
        icon-color="primary"
    >
        <x-slot name="heading">
            {{ $data['greeting'] }}
        </x-slot>
        
        <div class="text-sm text-gray-600 dark:text-gray-400 italic">
            "{{ $data['quote'] }}"
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
