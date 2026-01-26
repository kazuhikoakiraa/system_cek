<x-filament-panels::page>
    <form wire:submit="simpanPengecekan">
        {{ $this->form }}

        <div style="margin-top: 3rem;">
            @foreach ($this->getFormActions() as $action)
                {{ $action }}
            @endforeach
        </div>
    </form>
</x-filament-panels::page>
