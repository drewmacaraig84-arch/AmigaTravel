<div wire:poll.30s="loadData">
    <div class="fi-wi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" style="max-height: 440px; overflow: hidden; display: flex; flex-direction: column;">
        <div class="flex items-center justify-between mb-4 flex-shrink-0">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Top Routes</h3>
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">By bookings</span>
        </div>

        @php
            $maxBookings = count($routes) > 0 ? max(array_column($routes, 'booking_count')) : 1;
        @endphp

        <div class="space-y-3 overflow-y-auto flex-1">
            @forelse($routes as $index => $route)
                <div class="group">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="flex-shrink-0 flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold
                                {{ $index === 0 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' }}">
                                {{ $index + 1 }}
                            </span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $route['origin'] }} → {{ $route['destination'] }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0 ml-2">
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                {{ number_format($route['booking_count']) }} <span class="font-normal text-gray-400">bookings</span>
                            </span>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="flex items-center gap-3">
                        <div class="flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800 h-1.5">
                            <div class="h-full rounded-full transition-all duration-500 ease-out
                                {{ $index === 0 ? 'bg-amber-500' : ($index === 1 ? 'bg-blue-500' : 'bg-gray-400 dark:bg-gray-500') }}"
                                style="width: {{ round(($route['booking_count'] / $maxBookings) * 100) }}%">
                            </div>
                        </div>
                        <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400 tabular-nums flex-shrink-0 w-24 text-right">
                            ₱{{ number_format($route['total_revenue'], 0) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <x-heroicon-o-map class="h-8 w-8 text-gray-300 dark:text-gray-600" />
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No route data available</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
