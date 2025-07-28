<x-filament-panels::page>
    <form wire:submit.prevent="updateProfile">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" form="updateProfile">
                Simpan Perubahan
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>