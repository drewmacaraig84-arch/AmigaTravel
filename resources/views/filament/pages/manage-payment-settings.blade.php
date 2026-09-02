<x-filament-panels::page>
    @php
        $currentQrPath = $this->data['qr_code_path'] ?? null;
        if (blank($currentQrPath)) {
            $currentQrPath = \App\Models\PaymentSetting::current()->qr_code_path ?? null;
        }

        // Normalize FileUpload state: it can be an array when a file is selected/uploaded.
        if (is_array($currentQrPath)) {
            $currentQrPath = array_values(array_filter($currentQrPath))[0] ?? ($currentQrPath['path'] ?? null);
        }
    @endphp

    <form wire:submit="save">
        {{ $this->form }}

        @php $currentQrUrl = storage_asset_path($currentQrPath); @endphp
        @if ($currentQrUrl)
            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="mb-3 text-sm font-semibold text-slate-900">Current QR preview</div>
                <img src="{{ $currentQrUrl }}" alt="Current payment QR code" class="max-h-72 w-auto rounded-xl border border-slate-200 bg-white object-contain shadow-sm">
            </div>
        @endif

        <div class="mt-6">
            <x-filament::button type="submit">
                Save settings
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
