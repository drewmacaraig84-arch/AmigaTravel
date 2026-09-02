<x-filament-panels::page>

    {{-- Class Details Infolist --}}
    @if ($this->hasInfolist())
        {{ $this->infolist }}
    @endif

    {{-- Routes & Schedules Section --}}
    @php
        $transportClass = $this->getRecord();
        $schedules = $transportClass->schedules()
            ->with('ferryRoute')
            ->orderBy('departure_time')
            ->get();
        $groupedByRoute = $schedules->groupBy('ferry_route_id');
    @endphp

    <x-filament::section>
        <x-slot name="heading">
            Routes &amp; Schedules Using This Class
        </x-slot>
        <x-slot name="description">
            {{ $schedules->count() }} schedule(s) across {{ $groupedByRoute->count() }} route(s)
        </x-slot>

        @if ($groupedByRoute->isEmpty())
            <div class="py-8 text-center text-gray-500 dark:text-gray-400">
                <x-heroicon-o-calendar-days class="mx-auto mb-3 h-10 w-10 opacity-40" />
                <p class="text-sm font-medium">No schedules are currently using this transport class.</p>
            </div>
        @else
            <div class="space-y-6">
                @foreach ($groupedByRoute as $routeId => $routeSchedules)
                    @php
                        $route = $routeSchedules->first()->ferryRoute;
                        $vehicleName = $routeSchedules->first()->vehicle_name ?? '—';
                        $operator = $route?->operator ?? '—';
                        $mode = $route?->mode ?? '';
                        $modeLabel = match($mode) { 'airline' => '✈️ Airline', 'ferry' => '🚢 Ferry', default => ucfirst($mode) };
                    @endphp

                    {{-- Route Card --}}
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">

                        {{-- Route Header --}}
                        <div class="flex items-center justify-between gap-4 bg-gray-50 dark:bg-gray-800 px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex items-center gap-2 font-semibold text-gray-900 dark:text-white text-sm">
                                    <span>{{ $route?->origin ?? '?' }}</span>
                                    <x-heroicon-m-arrow-right class="h-4 w-4 text-primary-500 flex-shrink-0" />
                                    <span>{{ $route?->destination ?? '?' }}</span>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    🚌 {{ $vehicleName }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $mode === 'airline' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                                    {{ $modeLabel }}
                                </span>
                                <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                                    {{ $operator }}
                                </span>
                                <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-2.5 py-0.5 text-xs font-medium text-primary-700 dark:text-primary-300">
                                    {{ $routeSchedules->count() }} schedule(s)
                                </span>
                            </div>
                        </div>

                        {{-- Schedules Table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Departure</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Arrival</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Base Price</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Class Add-on</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tickets</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rate Tier</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach ($routeSchedules as $i => $schedule)
                                        @php
                                            $pivot = $schedule->pivot;
                                            $rateType = $pivot->rate_type ?? 'regular';
                                            $rateBadge = match($rateType) {
                                                'promotional'       => ['label' => '🟠 Promotional', 'class' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200'],
                                                'super_promotional' => ['label' => '🟣 Super Promo', 'class' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'],
                                                default             => ['label' => '🔵 Regular',     'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'],
                                            };
                                        @endphp
                                        <tr class="{{ $i % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50/50 dark:bg-gray-800/50' }} hover:bg-primary-50/30 dark:hover:bg-primary-900/10 transition-colors">
                                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ $schedule->departure_time ? \Carbon\Carbon::parse($schedule->departure_time)->format('M j, Y g:i A') : '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                                {{ $schedule->arrival_time ? \Carbon\Carbon::parse($schedule->arrival_time)->format('M j, Y g:i A') : '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                                ₱{{ number_format((float)$schedule->price, 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                                ₱{{ number_format((float)($pivot->additional_price ?? 0), 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                                {{ number_format((int)($pivot->tickets_available ?? 0)) }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $rateBadge['class'] }}">
                                                    {{ $rateBadge['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if ($schedule->is_active)
                                                    <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:text-green-200">Active</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:text-red-200">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>{{-- end route card --}}
                @endforeach
            </div>
        @endif
    </x-filament::section>

</x-filament-panels::page>
