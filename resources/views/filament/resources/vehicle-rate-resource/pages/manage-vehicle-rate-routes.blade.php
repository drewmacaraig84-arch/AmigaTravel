<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Summary card for the parent VehicleRate --}}
        <x-filament::section>
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Vehicle Category</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $this->record->name }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Default / Fallback Price</p>
                    <p class="text-lg font-semibold text-primary-600 dark:text-primary-400">
                        ₱{{ number_format($this->record->price, 2) }}
                    </p>
                    <p class="text-xs text-gray-400">(used for non-Starlite routes)</p>
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

        {{-- Route pricing table --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
