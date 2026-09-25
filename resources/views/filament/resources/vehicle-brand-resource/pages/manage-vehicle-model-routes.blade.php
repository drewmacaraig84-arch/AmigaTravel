<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Summary card for the parent VehicleModel --}}
        <x-filament::section>
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Brand</p>
                    <p class="text-base text-gray-700 dark:text-gray-300">{{ $this->record->brand?->name ?? '—' }}</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white mt-0.5">{{ $this->record->name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Model Base Price</p>
                    <p class="text-lg font-semibold text-primary-600 dark:text-primary-400">
                        ₱{{ number_format($this->record->price, 2) }}
                    </p>
                    <p class="text-xs text-gray-400">(fallback when no route override is set)</p>
                </div>
                <div>
                    @if($this->record->is_active)
                        <span class="inline-flex items-center rounded-full bg-success-100 px-3 py-1 text-xs font-medium text-success-700 dark:bg-success-900 dark:text-success-300">
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-danger-100 px-3 py-1 text-xs font-medium text-danger-700 dark:bg-danger-900 dark:text-danger-300">
                            Inactive
                        </span>
                    @endif
                </div>
            </div>
        </x-filament::section>

        {{-- Route price overrides table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
