<div>
    <div class="fi-wi-stats-overview-stat rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" style="max-height: 440px; overflow: hidden; display: flex; flex-direction: column;">
        <div class="flex items-center justify-between mb-4 flex-shrink-0">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Recent Activity</h3>
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
        </div>

        <div class="space-y-1 overflow-y-auto flex-1 -mx-2 px-2" style="scrollbar-width: thin;">
            @forelse($activities as $activity)
                <div class="flex items-start gap-3 rounded-lg px-3 py-2.5 transition-colors hover:bg-gray-50 dark:hover:bg-white/5">
                    {{-- Icon --}}
                    <div @class([
                        'mt-0.5 flex-shrink-0 rounded-lg p-2',
                        'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400' => $activity['type'] === 'booking',
                        'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' => $activity['type'] === 'transaction',
                        'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400' => $activity['type'] === 'inquiry',
                    ])>
                        @if($activity['type'] === 'booking')
                            <x-heroicon-o-ticket class="h-4 w-4" />
                        @elseif($activity['type'] === 'transaction')
                            <x-heroicon-o-banknotes class="h-4 w-4" />
                        @else
                            <x-heroicon-o-envelope class="h-4 w-4" />
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-medium text-gray-950 dark:text-white truncate">{{ $activity['title'] }}</p>
                            @php
                                $statusColor = match($activity['status']) {
                                    'confirmed', 'paid' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                                    'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                                    'cancelled', 'failed', 'rejected' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400',
                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-semibold {{ $statusColor }}">
                                {{ ucfirst($activity['status']) }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ $activity['description'] }}</p>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">
                            {{ \Carbon\Carbon::parse($activity['time'])->diffForHumans() }}
                            @if($activity['amount'])
                                · <span class="font-medium text-gray-600 dark:text-gray-300">₱{{ number_format($activity['amount'], 2) }}</span>
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <x-heroicon-o-inbox class="h-8 w-8 text-gray-300 dark:text-gray-600" />
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No recent activity</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
