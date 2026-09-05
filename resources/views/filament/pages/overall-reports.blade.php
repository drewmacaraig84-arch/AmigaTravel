<x-filament-panels::page>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<style>
    /* Dark mode support for DateRangePicker */
    .dark .daterangepicker {
        background-color: #0f172a; /* slate-950 */
        border-color: #1e293b; /* slate-800 */
        color: #e2e8f0; /* slate-200 */
    }
    .dark .daterangepicker .calendar-table {
        background-color: #0f172a;
        border-color: #1e293b;
    }
    .dark .daterangepicker td.off, 
    .dark .daterangepicker td.off.in-range, 
    .dark .daterangepicker td.off.start-date, 
    .dark .daterangepicker td.off.end-date {
        background-color: #0f172a;
        color: #475569; /* slate-600 */
    }
    .dark .daterangepicker td.available:hover, 
    .dark .daterangepicker th.available:hover {
        background-color: #1e293b; /* slate-800 */
        color: #fff;
    }
    .dark .daterangepicker td.in-range {
        background-color: #1e293b;
        color: #e2e8f0;
    }
    .dark .daterangepicker td.active, 
    .dark .daterangepicker td.active:hover {
        background-color: #6366f1; /* indigo-500 */
        color: #fff;
    }
    .dark .daterangepicker .ranges li {
        color: #e2e8f0;
        background-color: #0f172a;
    }
    .dark .daterangepicker .ranges li:hover {
        background-color: #1e293b;
    }
    .dark .daterangepicker .ranges li.active {
        background-color: #6366f1;
        color: #fff;
    }
    .dark .daterangepicker.show-ranges .drp-calendar.left {
        border-left: 1px solid #1e293b;
    }
    .dark .daterangepicker .drp-buttons {
        border-top: 1px solid #1e293b;
    }
    .dark .daterangepicker .drp-selected {
        color: #94a3b8; /* slate-400 */
    }
    .dark .daterangepicker select.monthselect, 
    .dark .daterangepicker select.yearselect,
    .dark .daterangepicker select.hourselect,
    .dark .daterangepicker select.minuteselect,
    .dark .daterangepicker select.secondselect,
    .dark .daterangepicker select.ampmselect {
        background-color: #1e293b;
        border-color: #334155;
        color: #e2e8f0;
        border-radius: 0.375rem;
        padding: 2px 4px;
    }
    .dark .daterangepicker .calendar-time {
        color: #e2e8f0;
    }
    .dark .daterangepicker:before {
        border-bottom-color: #1e293b;
    }
    .dark .daterangepicker:after {
        border-bottom-color: #0f172a;
    }
</style>

<div wire:poll.3s="refreshData" class="space-y-6 w-full">

    {{-- ═══ Header: Period Selector + Custom Dates + Export ═══ --}}
    <div class="rounded-2xl border border-slate-800 bg-slate-950 p-4">
        <div class="flex items-center justify-end gap-4">
            {{-- DateRangePicker --}}
            <div class="flex-none" wire:ignore
                 x-data="{ 
                     initPicker() {
                         const el = $(this.$refs.picker);
                         el.daterangepicker({
                             timePicker: true,
                             timePicker24Hour: false,
                             timePickerIncrement: 1,
                             startDate: '{{ \Carbon\Carbon::parse($startDate)->format('m/d/Y h:i A') }}',
                             endDate: '{{ \Carbon\Carbon::parse($endDate)->format('m/d/Y h:i A') }}',
                             opens: 'left',
                             autoApply: false,
                             ranges: {
                                'Today': [moment().startOf('day'), moment().endOf('day')],
                                'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
                                'Last 7 Days': [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
                                'Last 15 Days': [moment().subtract(14, 'days').startOf('day'), moment().endOf('day')],
                                'Last 30 Days': [moment().subtract(29, 'days').startOf('day'), moment().endOf('day')],
                                'This Month': [moment().startOf('month').startOf('day'), moment().endOf('month').endOf('day')],
                                'Last Month': [moment().subtract(1, 'month').startOf('month').startOf('day'), moment().subtract(1, 'month').endOf('month').endOf('day')],
                                'Last 6 Months': [moment().subtract(6, 'month').startOf('month').startOf('day'), moment().endOf('month').endOf('day')],
                                'This Year': [moment().startOf('year').startOf('day'), moment().endOf('year').endOf('day')]
                             },
                             locale: { 
                                 format: 'MM/DD/YYYY hh:mm A' 
                             }
                         });
                         el.on('apply.daterangepicker', (ev, picker) => {
                             @this.updateDateRange(picker.startDate.format('YYYY-MM-DD HH:mm:ss'), picker.endDate.format('YYYY-MM-DD HH:mm:ss'));
                         });
                     }
                 }"
                 x-init="
                     if (window.jQuery && window.moment && $.fn.daterangepicker) {
                         initPicker();
                     } else {
                         let interval = setInterval(() => {
                             if (window.jQuery && window.moment && $.fn.daterangepicker) {
                                 clearInterval(interval);
                                 initPicker();
                             }
                         }, 100);
                     }
                 ">
                <div class="relative w-[340px] sm:w-[380px]">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <x-heroicon-m-magnifying-glass class="h-4 w-4 text-slate-400" />
                    </div>
                    <input type="text" x-ref="picker" readonly
                           class="w-full cursor-pointer rounded-lg bg-white dark:bg-white/5 py-2 pl-9 pr-3 text-xs sm:text-sm font-medium text-slate-900 shadow-sm border border-slate-300 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-center dark:border-slate-700 dark:text-slate-100" />
                </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('bookings.export.pdf', ['start' => $startDate, 'end' => $endDate]) }}" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition"
                   style="background: #334155; border: 1px solid #475569; color: #cbd5e1;">
                    <x-heroicon-m-arrow-down-tray class="h-4 w-4" />
                    PDF
                </a>
                <a href="{{ route('bookings.export.csv', ['start' => $startDate, 'end' => $endDate]) }}" class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition"
                   style="background: #334155; border: 1px solid #475569; color: #cbd5e1;">
                    <x-heroicon-m-table-cells class="h-4 w-4" />
                    CSV
                </a>
            </div>
        </div>
    </div>

    {{-- ═══ KPI Cards ═══ --}}
    @php
        $bookingTrend = ($stats['prev_total_bookings'] ?? 0) > 0
            ? round((($stats['total_bookings'] - $stats['prev_total_bookings']) / $stats['prev_total_bookings']) * 100, 1)
            : ($stats['total_bookings'] > 0 ? 100 : 0);
        $revenueTrend = ($stats['prev_total_revenue'] ?? 0) > 0
            ? round((($stats['total_revenue'] - $stats['prev_total_revenue']) / $stats['prev_total_revenue']) * 100, 1)
            : ($stats['total_revenue'] > 0 ? 100 : 0);
    @endphp
    <div class="grid w-full gap-5 grid-cols-1 md:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200 min-h-[220px] dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Month to date</p>
                    <p class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Total Bookings</p>
                    <p class="mt-4 text-4xl font-extrabold text-slate-900 dark:text-white">{{ number_format($stats['total_bookings'] ?? 0) }}</p>
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">{{ abs($bookingTrend) }}% vs prev period</p>
                </div>
                <div class="flex flex-col items-end gap-3">
                    <div class="h-14 w-28 rounded-3xl bg-slate-100 dark:bg-slate-800" wire:ignore id="spark-total-bookings"></div>
                    <x-heroicon-o-ticket class="h-6 w-6 text-emerald-500" />
                </div>
            </div>
        </div>

        <div class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200 min-h-[220px] dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Month to date</p>
                    <p class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Total Revenue</p>
                    <p class="mt-4 text-4xl font-extrabold text-slate-900 dark:text-white">₱{{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">{{ abs($revenueTrend) }}% vs prev period</p>
                </div>
                <div class="flex flex-col items-end gap-3">
                    <div class="h-14 w-28 rounded-3xl bg-slate-100 dark:bg-slate-800" wire:ignore id="spark-total-revenue"></div>
                    <x-heroicon-o-banknotes class="h-6 w-6 text-amber-500" />
                </div>
            </div>
        </div>

        <div class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200 min-h-[220px] dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Month to date</p>
                    <p class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Avg Booking Value</p>
                    <p class="mt-4 text-4xl font-extrabold text-slate-900 dark:text-white">₱{{ number_format($stats['avg_booking_value'] ?? 0, 2) }}</p>
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Per booking</p>
                </div>
                <div class="flex flex-col items-end gap-3">
                    <div class="h-14 w-28 rounded-3xl bg-slate-100 dark:bg-slate-800" wire:ignore id="spark-avg-booking"></div>
                    <x-heroicon-o-calculator class="h-6 w-6 text-violet-500" />
                </div>
            </div>
        </div>

        <div class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200 min-h-[220px] dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Month to date</p>
                    <p class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Completion Rate</p>
                    <p class="mt-4 text-4xl font-extrabold text-slate-900 dark:text-white">{{ $stats['completion_rate'] ?? 0 }}%</p>
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">{{ $stats['completed_bookings'] ?? 0 }} confirmed</p>
                </div>
                <div class="flex flex-col items-end gap-3">
                    <div class="h-14 w-28 rounded-3xl bg-slate-100 dark:bg-slate-800" wire:ignore id="spark-completion"></div>
                    <x-heroicon-o-check-circle class="h-6 w-6 text-emerald-500" />
                </div>
            </div>
        </div>

        <div class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200 min-h-[220px] dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Month to date</p>
                    <p class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Cancellation Rate</p>
                    <p class="mt-4 text-4xl font-extrabold text-slate-900 dark:text-white">{{ $stats['cancellation_rate'] ?? 0 }}%</p>
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">{{ $stats['cancelled_bookings'] ?? 0 }} cancelled</p>
                </div>
                <div class="flex flex-col items-end gap-3">
                    <div class="h-14 w-28 rounded-3xl bg-slate-100 dark:bg-slate-800" wire:ignore id="spark-cancellation"></div>
                    <x-heroicon-o-x-circle class="h-6 w-6 text-red-500" />
                </div>
            </div>
        </div>

        <div class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200 min-h-[220px] dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Month to date</p>
                    <p class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Rebookings</p>
                    <p class="mt-4 text-4xl font-extrabold text-slate-900 dark:text-white">{{ number_format($stats['rebooking_count'] ?? 0) }}</p>
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">₱{{ number_format($stats['pending_revenue'] ?? 0, 0) }} pending</p>
                </div>
                <div class="flex flex-col items-end gap-3">
                    <div class="h-14 w-28 rounded-3xl bg-slate-100 dark:bg-slate-800" wire:ignore id="spark-rebookings"></div>
                    <x-heroicon-o-arrow-path class="h-6 w-6 text-sky-500" />
                </div>
            </div>
        </div>

        <div class="rounded-[28px] bg-white p-6 shadow-sm ring-1 ring-gray-200 min-h-[220px] dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-slate-400">Month to date</p>
                    <p class="mt-4 text-lg font-semibold text-slate-900 dark:text-white">Rejection Rate</p>
                    <p class="mt-4 text-4xl font-extrabold text-slate-900 dark:text-white">{{ $stats['rejection_rate'] ?? 0 }}%</p>
                    <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                        {{ $stats['rejected_bookings'] ?? 0 }} rejected
                        @if(($stats['rejected_rebookings'] ?? 0) > 0)
                            · {{ $stats['rejected_rebookings'] }} rebooking
                        @endif
                    </p>
                </div>
                <div class="flex flex-col items-end gap-3">
                    <div class="h-14 w-28 rounded-3xl bg-slate-100 dark:bg-slate-800" wire:ignore id="spark-rejections"></div>
                    <x-heroicon-o-no-symbol class="h-6 w-6 text-rose-500" />
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ Charts Row 1: Revenue + Booking Volume ═══ --}}
    <div class="grid w-full gap-6 grid-cols-1 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-1">Revenue Trend</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Revenue over time for the selected period</p>
            <div wire:ignore id="report-revenue-chart" style="height: 320px; width: 100%;"></div>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-1">Booking Volume</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Number of bookings over time</p>
            <div wire:ignore id="report-booking-volume-chart" style="height: 320px; width: 100%;"></div>
        </div>
    </div>

    {{-- ═══ Charts Row 2: Status Distribution + Transport Mode + Top Routes ═══ --}}
    <div class="grid w-full gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-4">Status Distribution</h3>
            <div wire:ignore id="report-status-chart" style="height: 280px; width: 100%;"></div>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-4">Transport Mode</h3>
            <div wire:ignore id="report-mode-chart" style="height: 280px; width: 100%;"></div>
        </div>
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-4">Top Routes by Revenue</h3>
            <div wire:ignore id="report-routes-chart" style="height: 280px; width: 100%;"></div>
        </div>
    </div>

    {{-- ═══ Tables Row: Recent Bookings + Transactions ═══ --}}
    <div class="grid w-full gap-6 grid-cols-1 xl:grid-cols-2">
        {{-- Recent Bookings --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-1">Recent Bookings</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Latest bookings for the selected period</p>
            <div class="overflow-x-auto rounded-lg ring-1 ring-gray-200 dark:ring-white/10 w-full">
                <table class="min-w-full text-sm w-full">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Transaction</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Client</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Route</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($recentBookings as $booking)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200 font-mono text-xs">{{ $booking['transaction_number'] }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ $booking['client_name'] }}</td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $booking['route'] }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $sc = match($booking['status']) {
                                            'confirmed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                                            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                                            'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400',
                                            'rejected' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
                                            default => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $sc }}">
                                        {{ ucfirst($booking['status']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white tabular-nums">₱{{ number_format($booking['total_price'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">No bookings found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Transactions --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-1">Recent Transactions</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Latest payments and rebooking transactions</p>
            <div class="overflow-x-auto rounded-lg ring-1 ring-gray-200 dark:ring-white/10 w-full">
                <table class="min-w-full text-sm w-full">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Transaction</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Client</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Proof</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($recentTransactions as $transaction)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200 font-mono text-xs">{{ $transaction['transaction_number'] }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-200">{{ $transaction['client_name'] }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $tc = match($transaction['payment_status']) {
                                            'paid' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                                            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                                            default => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold {{ $tc }}">
                                        {{ ucfirst($transaction['payment_status']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($transaction['proof_uploaded'])
                                        <span class="inline-flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400">
                                            <x-heroicon-m-check-circle class="h-3.5 w-3.5" /> Uploaded
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-gray-500 dark:text-gray-400 text-xs">{{ $transaction['created_at'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">No transactions found for this period.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══ Insights Row: Passengers + Staff + Tours ═══ --}}
    <div class="grid w-full gap-6 grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
        {{-- Passenger Demographics --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-4">Passenger Demographics</h3>
            <div wire:ignore id="report-passenger-chart" style="height: 250px; width: 100%;"></div>
        </div>

        {{-- Staff Leaderboard --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-4">Staff Leaderboard</h3>
            <div class="space-y-3">
                @forelse($staffLeaderboard as $index => $staff)
                    <div class="flex items-center gap-3">
                        <span @class([
                            'flex-shrink-0 flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold',
                            'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400' => $index === 0,
                            'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' => $index > 0,
                        ])>
                            {{ $index + 1 }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $staff['name'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $staff['verifications'] }} verifications · ₱{{ number_format($staff['revenue'], 0) }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">No staff activity in this period</p>
                @endforelse
            </div>
        </div>

        {{-- Tour Performance --}}
        <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-4">Tour Performance</h3>
            <div class="space-y-3">
                @forelse($tourPerformance as $tour)
                    <div class="rounded-lg bg-gray-50 dark:bg-white/5 p-3">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $tour['name'] }}</p>
                        <div class="mt-1.5 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1">
                                <x-heroicon-m-ticket class="h-3.5 w-3.5" />
                                {{ $tour['bookings'] }} bookings
                            </span>
                            <span class="flex items-center gap-1 font-medium text-emerald-600 dark:text-emerald-400">
                                ₱{{ number_format($tour['revenue'], 0) }}
                            </span>
                            @if($tour['upcoming_dates'] > 0)
                                <span class="flex items-center gap-1 text-blue-600 dark:text-blue-400">
                                    <x-heroicon-m-calendar class="h-3.5 w-3.5" />
                                    {{ $tour['upcoming_dates'] }} upcoming
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">No active tours</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ═══ Payment Analytics Row ═══ --}}
    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 w-full">
        <h3 class="text-base font-semibold text-gray-950 dark:text-white mb-4">Payment Analytics</h3>
        <div class="grid w-full gap-6 grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
            <div class="text-center">
                <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $paymentAnalytics['paid'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Paid</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $paymentAnalytics['pending'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Pending</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $paymentAnalytics['failed'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Failed</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $paymentAnalytics['rejected'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Rejected</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $paymentAnalytics['total'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 mt-1">Total Transactions</p>
            </div>
            <div class="text-center col-span-2 sm:col-span-1">
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $paymentAnalytics['proof_upload_rate'] ?? 0 }}%</p>
                <p class="text-xs text-gray-500 mt-1">Proof Upload Rate</p>
            </div>
        </div>
    </div>
</div>

{{-- ═══ ApexCharts Initialization & Update Script ═══ --}}
@script
<script>
(function() {
    const reportCharts = {};
    let currentChartData = @js($chartData);
    let initialized = false;

    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    function baseTheme() {
        const dark = isDark();
        return {
            theme: { mode: dark ? 'dark' : 'light' },
            chart: { background: 'transparent', fontFamily: 'inherit', toolbar: { show: false } },
            grid: { borderColor: dark ? '#374151' : '#e5e7eb', strokeDashArray: 4 },
            tooltip: { theme: dark ? 'dark' : 'light' },
            xaxis: {
                labels: {
                    style: {
                        colors: dark ? '#9ca3af' : '#6b7280',
                        fontSize: '11px',
                    },
                },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: {
                labels: {
                    style: {
                        colors: dark ? '#9ca3af' : '#6b7280',
                        fontSize: '11px',
                    },
                },
            },
        };
    }

    function initReportCharts() {
        const data = currentChartData;

        // Clean up existing charts first
        Object.values(reportCharts).forEach(c => c?.destroy());
        Object.keys(reportCharts).forEach(k => delete reportCharts[k]);
        
        if (!window.ApexCharts) {
            console.error('ApexCharts not available for overall reports');
            return;
        }

        const bt = baseTheme();
        const dark = isDark();

        // Revenue Area Chart
        const revEl = document.getElementById('report-revenue-chart');
        if (revEl) {
            reportCharts.revenue = new ApexCharts(revEl, {
                ...bt,
                chart: { ...bt.chart, type: 'area', height: 320, animations: { enabled: true, easing: 'easeinout', speed: 500 } },
                series: data.revenue?.series || [],
                xaxis: { ...bt.xaxis, categories: data.revenue?.categories || [], tickAmount: 8 },
                yaxis: { ...bt.yaxis, labels: { ...bt.yaxis.labels, formatter: (v) => '₱' + (v || 0).toLocaleString() } },
                colors: ['#f59e0b'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
                stroke: { curve: 'smooth', width: 2.5 },
                dataLabels: { enabled: false },
                tooltip: { ...bt.tooltip, y: { formatter: (v) => '₱' + (v || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) } },
            });
            reportCharts.revenue.render();
        }

        // Booking Volume Bar Chart
        const volEl = document.getElementById('report-booking-volume-chart');
        if (volEl) {
            reportCharts.bookingVolume = new ApexCharts(volEl, {
                ...bt,
                chart: { ...bt.chart, type: 'bar', height: 320, animations: { enabled: true, easing: 'easeinout', speed: 500 } },
                series: data.bookingVolume?.series || [],
                xaxis: { ...bt.xaxis, categories: data.bookingVolume?.categories || [], tickAmount: 8 },
                colors: ['#3b82f6'],
                plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
                dataLabels: { enabled: false },
            });
            reportCharts.bookingVolume.render();
        }

        // Status Donut
        const statEl = document.getElementById('report-status-chart');
        if (statEl) {
            reportCharts.status = new ApexCharts(statEl, {
                ...bt,
                chart: { ...bt.chart, type: 'donut', height: 280 },
                series: data.statusDistribution?.series || [0, 0, 0, 0],
                labels: data.statusDistribution?.labels || ['Confirmed', 'Pending', 'Cancelled', 'Rejected'],
                colors: ['#10b981', '#f59e0b', '#64748b', '#e11d48'],
                plotOptions: { pie: { donut: { size: '70%', labels: { show: true, name: { color: dark ? '#fff' : '#1f2937' }, value: { color: dark ? '#fff' : '#1f2937', fontSize: '22px', fontWeight: 700 }, total: { show: true, label: 'Total', color: dark ? '#9ca3af' : '#6b7280', formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0) } } } } },
                stroke: { width: 2, colors: [dark ? '#111827' : '#fff'] },
                legend: { position: 'bottom', labels: { colors: dark ? '#d1d5db' : '#374151' }, fontSize: '12px', markers: { size: 8, shape: 'circle' }, itemMargin: { horizontal: 10, vertical: 4 } },
                dataLabels: { enabled: false },
            });
            reportCharts.status.render();
        }

        // Transport Mode Pie
        const modeEl = document.getElementById('report-mode-chart');
        if (modeEl) {
            reportCharts.mode = new ApexCharts(modeEl, {
                ...bt,
                chart: { ...bt.chart, type: 'pie', height: 280 },
                series: data.transportMode?.series || [0],
                labels: data.transportMode?.labels || ['No Data'],
                colors: ['#3b82f6', '#8b5cf6', '#06b6d4', '#f59e0b'],
                stroke: { width: 2, colors: [dark ? '#111827' : '#fff'] },
                legend: { position: 'bottom', labels: { colors: dark ? '#d1d5db' : '#374151' }, fontSize: '12px', markers: { size: 8, shape: 'circle' }, itemMargin: { horizontal: 10, vertical: 4 } },
                dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 600 }, dropShadow: { enabled: false } },
            });
            reportCharts.mode.render();
        }

        // Top Routes Horizontal Bar
        const routeEl = document.getElementById('report-routes-chart');
        if (routeEl) {
            reportCharts.routes = new ApexCharts(routeEl, {
                ...bt,
                chart: { ...bt.chart, type: 'bar', height: 280 },
                series: data.topRoutes?.series || [],
                xaxis: { ...bt.xaxis, categories: data.topRoutes?.categories || [], labels: { ...bt.xaxis.labels, formatter: (v) => '₱' + (v || 0).toLocaleString() } },
                yaxis: { ...bt.yaxis, labels: { ...bt.yaxis.labels, style: { ...bt.yaxis.labels.style, fontSize: '10px' }, maxWidth: 150 } },
                colors: ['#f59e0b'],
                plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '60%' } },
                dataLabels: { enabled: false },
                tooltip: { ...bt.tooltip, y: { formatter: (v) => '₱' + (v || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }) } },
            });
            reportCharts.routes.render();
        }

        // Passenger Demographics Bar
        const passEl = document.getElementById('report-passenger-chart');
        if (passEl) {
            reportCharts.passengers = new ApexCharts(passEl, {
                ...bt,
                chart: { ...bt.chart, type: 'bar', height: 250 },
                series: data.passengers?.series || [],
                xaxis: { ...bt.xaxis, categories: data.passengers?.categories || [] },
                colors: ['#8b5cf6'],
                plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
                dataLabels: { enabled: false },
            });
            reportCharts.passengers.render();
        }

        // Render small KPI sparklines
        function renderSparklines() {
            try {
                const sparkConfigs = [
                    { id: 'spark-total-bookings', series: data.revenue?.series?.[0]?.data || (data.bookingVolume?.series?.[0]?.data || []) , color: '#10B981' },
                    { id: 'spark-total-revenue', series: data.revenue?.series?.[0]?.data || [], color: '#f59e0b' },
                    { id: 'spark-avg-booking', series: data.bookingVolume?.series?.[0]?.data || [], color: '#8b5cf6' },
                    { id: 'spark-completion', series: data.statusDistribution?.series || [], color: '#10B981' },
                    { id: 'spark-cancellation', series: data.statusDistribution?.series || [], color: '#ef4444' },
                    { id: 'spark-rebookings', series: data.bookingVolume?.series?.[0]?.data || [], color: '#3b82f6' },
                    { id: 'spark-rejections', series: data.statusDistribution?.series || [], color: '#e11d48' },
                ];

                sparkConfigs.forEach(cfg => {
                    const el = document.getElementById(cfg.id);
                    if (!el) return;

                    // clear previous chart if present
                    if (reportCharts[cfg.id]) {
                        try { reportCharts[cfg.id].destroy(); } catch (e) {}
                        delete reportCharts[cfg.id];
                    }

                    const seriesData = Array.isArray(cfg.series) ? cfg.series : (cfg.series[0]?.data || []);

                    reportCharts[cfg.id] = new ApexCharts(el, {
                        chart: { type: 'area', sparkline: { enabled: true }, height: 40 },
                        series: [{ data: seriesData }],
                        stroke: { curve: 'smooth', width: 2 },
                        fill: { opacity: 0.12 },
                        colors: [cfg.color],
                        tooltip: { enabled: false },
                    });

                    reportCharts[cfg.id].render();
                });
            } catch (err) {
                console.error('Error rendering sparklines', err);
            }
        }

        renderSparklines();
        initialized = true;
    }

    function waitForApexCharts() {
        if (window.ApexCharts) {
            initReportCharts();
        } else {
            window.addEventListener('amiga:apexcharts-ready', function() {
                initReportCharts();
            }, { once: true });

            setTimeout(function() {
                if (!initialized && !window.ApexCharts) {
                    console.error('ApexCharts did not load after 10 seconds for overall reports');
                }
            }, 10000);
        }
    }

    // Listen for Livewire updates
    $wire.on('report-charts-updated', ({ chartData }) => {
        if (chartData) {
            currentChartData = chartData;
            initReportCharts();
        }
    });

    // Dark mode observer
    const darkObserver = new MutationObserver(() => {
        initReportCharts();
    });
    darkObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    waitForApexCharts();
})();
</script>
@endscript
</x-filament-panels::page>

