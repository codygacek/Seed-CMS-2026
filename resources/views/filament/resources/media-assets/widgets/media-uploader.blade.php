<x-filament::section class="w-full">
    <div
        x-data="{ clearT: null }"
        x-on:schedule-media-clear.window="
            clearTimeout(clearT);
            clearT = setTimeout(() => { $wire.clearUploader() }, 1500);
        "
        class="w-full"
    >
        {{ $this->form }}
    </div>
</x-filament::section>