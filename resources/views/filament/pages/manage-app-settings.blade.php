<x-filament-panels::page>
    @php
        $settings = \App\Models\WebsiteSetting::getAppMaintenanceSettings();
        $isActive = $settings['is_active'];
        $endsAt = $settings['ends_at'] ? \Carbon\Carbon::parse($settings['ends_at']) : null;
        $remainingSeconds = $settings['remaining_seconds'];
    @endphp

    <div class="mb-6">
        @if ($isActive)
            <div class="rounded-2xl border-2 border-rose-300 bg-rose-50/80 p-5 shadow-sm dark:border-rose-700/60 dark:bg-rose-950/40">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-rose-500 text-white shadow-md">
                        <x-heroicon-s-wrench-screwdriver class="h-6 w-6" />
                    </div>
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-rose-600 px-2.5 py-0.5 text-xs font-black uppercase tracking-wider text-white">
                                Active Maintenance Break
                            </span>
                            <span class="text-xs font-semibold text-rose-700 dark:text-rose-300">
                                Estimated Duration: {{ $settings['estimated_duration'] }}
                            </span>
                        </div>
                        <h3 class="mt-2 text-base font-bold text-rose-900 dark:text-rose-100">
                            {{ $settings['title'] }}
                        </h3>
                        <p class="mt-1 text-sm text-rose-700 dark:text-rose-200">
                            {{ $settings['message'] }}
                        </p>
                        @if ($endsAt)
                            <div class="mt-3 text-xs text-rose-600 dark:text-rose-300">
                                <strong>Expected completion:</strong> {{ $endsAt->format('M d, Y h:i A') }}
                                @if ($remainingSeconds > 0)
                                    ({{ ceil($remainingSeconds / 60) }} minutes remaining)
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-5 shadow-sm dark:border-emerald-800/60 dark:bg-emerald-950/40">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-md">
                        <x-heroicon-s-check-circle class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-emerald-600 px-2.5 py-0.5 text-xs font-black uppercase tracking-wider text-white">
                                Mobile App Normal Operations
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-emerald-800 dark:text-emerald-200">
                            All mobile app features, APIs, schedules, and bookings are running normally.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" size="lg" icon="heroicon-o-check">
                Save & Apply Settings
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
