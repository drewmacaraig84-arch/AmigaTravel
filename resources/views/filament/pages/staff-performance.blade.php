<x-filament-panels::page>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <style>
        /* ═══ Bulletproof KPI 4-Column Grid ═══ */
        .staff-kpi-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            width: 100%;
        }
        @media (min-width: 640px) {
            .staff-kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (min-width: 1024px) {
            .staff-kpi-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .staff-kpi-card {
            border-radius: 1rem;
            padding: 1.25rem;
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 125px;
            transition: all 0.2s ease-in-out;
        }
        .dark .staff-kpi-card {
            border-color: #1e293b;
            background-color: #0f172a;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }
        .staff-kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
        }
        .dark .staff-kpi-card:hover {
            border-color: #334155;
        }

        /* ═══ Dark Mode DateRangePicker ═══ */
        .dark .daterangepicker {
            background-color: #0f172a;
            border-color: #1e293b;
            color: #e2e8f0;
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
            color: #475569;
        }
        .dark .daterangepicker td.available:hover, 
        .dark .daterangepicker th.available:hover {
            background-color: #1e293b;
            color: #fff;
        }
        .dark .daterangepicker td.in-range {
            background-color: #1e293b;
            color: #e2e8f0;
        }
        .dark .daterangepicker td.active, 
        .dark .daterangepicker td.active:hover {
            background-color: #f59e0b;
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
            background-color: #f59e0b;
            color: #fff;
        }
        .dark .daterangepicker .drp-buttons {
            border-top: 1px solid #1e293b;
        }
    </style>

    <div class="space-y-6">
        {{-- ═══ Header Filter Bar ═══ --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-500/20">
                        <x-heroicon-o-user-group class="h-6 w-6" />
                    </div>
                    <span>Staff Performance Overview</span>
                </h2>
                <p class="mt-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                    Comprehensive metrics for staff activity, ticket verifications, payment approvals, and revenue handled.
                </p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2.5 w-full xl:w-auto justify-start xl:justify-end">
                {{-- Quick Period Filter Buttons --}}
                <div class="inline-flex rounded-xl bg-gray-100 p-1 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/60 flex-wrap gap-1 text-xs">
                    <button type="button" 
                        wire:click="setPeriod('all_time')"
                        class="rounded-lg px-3 py-1.5 font-semibold transition {{ $period === 'all_time' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                        All Time
                    </button>
                    <button type="button" 
                        wire:click="setPeriod('today')"
                        class="rounded-lg px-3 py-1.5 font-semibold transition {{ $period === 'today' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                        Today
                    </button>
                    <button type="button" 
                        wire:click="setPeriod('this_week')"
                        class="rounded-lg px-3 py-1.5 font-semibold transition {{ $period === 'this_week' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                        This Week
                    </button>
                    <button type="button" 
                        wire:click="setPeriod('this_month')"
                        class="rounded-lg px-3 py-1.5 font-semibold transition {{ $period === 'this_month' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                        This Month
                    </button>
                </div>

                {{-- DateRangePicker --}}
                <div class="flex-none" wire:ignore
                     x-data="{ 
                         initPicker() {
                             const el = $(this.$refs.picker);
                             el.daterangepicker({
                                 timePicker: false,
                                 opens: 'left',
                                 autoApply: false,
                                 startDate: '{{ $startDate ? \Carbon\Carbon::parse($startDate)->format('m/d/Y') : now()->startOfMonth()->format('m/d/Y') }}',
                                 endDate: '{{ $endDate ? \Carbon\Carbon::parse($endDate)->format('m/d/Y') : now()->endOfMonth()->format('m/d/Y') }}',
                                 ranges: {
                                    'Today': [moment().startOf('day'), moment().endOf('day')],
                                    'Yesterday': [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
                                    'Last 7 Days': [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
                                    'This Month': [moment().startOf('month').startOf('day'), moment().endOf('month').endOf('day')],
                                    'Last Month': [moment().subtract(1, 'month').startOf('month').startOf('day'), moment().subtract(1, 'month').endOf('month').endOf('day')],
                                    'This Year': [moment().startOf('year').startOf('day'), moment().endOf('year').endOf('day')]
                                 },
                                 locale: { 
                                     format: 'MM/DD/YYYY' 
                                 }
                             });
                             el.on('apply.daterangepicker', (ev, picker) => {
                                 @this.updateDateRange(picker.startDate.format('YYYY-MM-DD 00:00:00'), picker.endDate.format('YYYY-MM-DD 23:59:59'));
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
                    <div class="relative w-[210px]">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <x-heroicon-m-calendar class="h-4 w-4 text-amber-500" />
                        </div>
                        <input type="text" x-ref="picker" readonly
                               placeholder="Custom Date Range"
                               class="w-full cursor-pointer rounded-xl bg-white dark:bg-gray-800 py-1.5 pl-9 pr-3 text-xs font-semibold text-gray-900 shadow-sm border border-gray-300 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-center dark:border-gray-700 dark:text-gray-100" />
                    </div>
                </div>

                {{-- Export CSV & PDF --}}
                <div class="flex items-center gap-1.5">
                    <button type="button" wire:click="exportCsv"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 px-3.5 py-1.5 text-xs font-bold text-gray-700 dark:text-gray-200 transition border border-gray-300 dark:border-gray-700 shadow-sm">
                        <x-heroicon-m-arrow-down-tray class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                        CSV
                    </button>
                    <button type="button" wire:click="exportPdf"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white px-3.5 py-1.5 text-xs font-bold transition shadow-sm">
                        <x-heroicon-m-document-arrow-down class="h-4 w-4 text-white" />
                        PDF
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══ Summary KPI 4-Column Grid ═══ --}}
        <div class="staff-kpi-grid">
            {{-- KPI 1: Bookings Handled --}}
            <div class="staff-kpi-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Bookings Handled</span>
                    <div class="rounded-xl bg-amber-50 dark:bg-amber-950/60 p-2.5 text-amber-600 dark:text-amber-400 border border-amber-200/50 dark:border-amber-900/50">
                        <x-heroicon-o-ticket class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ number_format($summaryKpis['total_bookings']) }}</p>
                    <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 mt-0.5">Total actions performed across staff</p>
                </div>
            </div>

            {{-- KPI 2: Revenue Handled --}}
            <div class="staff-kpi-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Revenue Handled</span>
                    <div class="rounded-xl bg-emerald-50 dark:bg-emerald-950/60 p-2.5 text-emerald-600 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-900/50">
                        <x-heroicon-o-banknotes class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">₱{{ number_format($summaryKpis['total_revenue'], 2) }}</p>
                    <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 mt-0.5">Total verified payment volume</p>
                </div>
            </div>

            {{-- KPI 3: Completed Bookings --}}
            <div class="staff-kpi-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Completed Bookings</span>
                    <div class="rounded-xl bg-blue-50 dark:bg-blue-950/60 p-2.5 text-blue-600 dark:text-blue-400 border border-blue-200/50 dark:border-blue-900/50">
                        <x-heroicon-o-check-circle class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ number_format($summaryKpis['total_completed']) }}</p>
                    <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 mt-0.5">Fully confirmed &amp; verified trips</p>
                </div>
            </div>

            {{-- KPI 4: Top Performer --}}
            <div class="staff-kpi-card">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Top Performer</span>
                    <div class="rounded-xl bg-purple-50 dark:bg-purple-950/60 p-2.5 text-purple-600 dark:text-purple-400 border border-purple-200/50 dark:border-purple-900/50">
                        <x-heroicon-o-trophy class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-lg font-extrabold text-purple-600 dark:text-purple-300 truncate">{{ $summaryKpis['top_staff_name'] }}</p>
                    <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 mt-0.5">{{ $summaryKpis['top_staff_count'] }} bookings handled</p>
                </div>
            </div>
        </div>

        {{-- ═══ Active Period Notice ═══ --}}
        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 px-1">
            <span>
                Showing metrics for: 
                <strong class="text-gray-900 dark:text-white font-semibold">
                    @if($period === 'all_time')
                        All Recorded Time
                    @elseif($startDate && $endDate)
                        {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                    @else
                        {{ ucwords(str_replace('_', ' ', $period)) }}
                    @endif
                </strong>
            </span>
            <span>Total Staff Registered: <strong class="text-gray-900 dark:text-white font-semibold">{{ $staffStats->count() }}</strong></span>
        </div>

        {{-- ═══ Staff Table ═══ --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm dark:border-gray-800 bg-white dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="border-b border-gray-200 bg-gray-50/80 dark:border-gray-800 dark:bg-gray-800/80">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Staff Member
                            </th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 text-center">
                                Total Handled
                            </th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 text-center">
                                Completed
                            </th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 text-center">
                                Pending
                            </th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 text-center">
                                Cancelled
                            </th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 text-right">
                                Revenue Handled
                            </th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 text-center">
                                Success Rate
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($staffStats as $staff)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3.5">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-extrabold text-sm bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-sm">
                                            {{ strtoupper(substr($staff['name'], 0, 2)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-900 dark:text-white text-sm">{{ $staff['name'] }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $staff['email'] }}</p>
                                            <div class="mt-1 flex items-center gap-2">
                                                @if($staff['is_admin'])
                                                    <span class="inline-flex items-center rounded-md bg-purple-100 px-2 py-0.5 text-[10px] font-bold text-purple-800 dark:bg-purple-900/60 dark:text-purple-200">
                                                        Admin
                                                    </span>
                                                @elseif($staff['is_staff'])
                                                    <span class="inline-flex items-center rounded-md bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800 dark:bg-blue-900/60 dark:text-blue-200">
                                                        Staff
                                                    </span>
                                                @endif
                                                @if($staff['latest_action_at'])
                                                    <span class="text-[10px] text-gray-400">
                                                        Last active: {{ \Carbon\Carbon::parse($staff['latest_action_at'])->diffForHumans() }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center rounded-xl bg-gray-100 px-3 py-1 font-black text-sm text-gray-900 dark:bg-gray-800 dark:text-white border border-gray-200 dark:border-gray-700">
                                        {{ $staff['total_bookings_handled'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800/60">
                                        {{ $staff['completed_bookings'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800 dark:bg-amber-950/80 dark:text-amber-300 border border-amber-300 dark:border-amber-800/60">
                                        {{ $staff['pending_bookings'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-bold text-red-800 dark:bg-red-950/80 dark:text-red-300 border border-red-300 dark:border-red-800/60">
                                        {{ $staff['cancelled_bookings'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="font-extrabold text-sm text-emerald-600 dark:text-emerald-400 tabular-nums">
                                        ₱{{ number_format($staff['total_revenue_handled'], 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex flex-col items-center gap-1.5">
                                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $staff['completion_rate'] }}%</span>
                                        <div class="w-16 bg-gray-200 rounded-full h-1.5 dark:bg-gray-700 overflow-hidden">
                                            <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-1.5 rounded-full" style="width: {{ min(100, $staff['completion_rate']) }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button type="button" 
                                        x-data 
                                        x-on:click="$dispatch('open-modal', { id: 'staff-bookings-{{ $staff['id'] }}' })"
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 px-3.5 py-1.5 text-xs font-bold text-white transition shadow-sm">
                                        <x-heroicon-m-eye class="h-3.5 w-3.5" />
                                        View Bookings ({{ $staff['total_bookings_handled'] }})
                                    </button>
                                    
                                    <x-filament::modal 
                                        id="staff-bookings-{{ $staff['id'] }}" 
                                        width="5xl"
                                        :heading="'Bookings Handled by ' . $staff['name']">
                                        
                                        @php
                                            $bookings = $this->getStaffBookings($staff['id']);
                                        @endphp
                                        
                                        <div class="overflow-x-auto max-h-[65vh]">
                                            <table class="w-full text-sm text-left">
                                                <thead class="text-xs text-gray-600 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10">
                                                    <tr>
                                                        <th class="px-4 py-3">Transaction #</th>
                                                        <th class="px-4 py-3">Client</th>
                                                        <th class="px-4 py-3">Route / Travel</th>
                                                        <th class="px-4 py-3 text-center">Status</th>
                                                        <th class="px-4 py-3 text-right">Amount</th>
                                                        <th class="px-4 py-3">Handled Date</th>
                                                        <th class="px-4 py-3 text-right">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                                    @forelse($bookings as $booking)
                                                        @php
                                                            $status = strtolower($booking->status ?? 'pending');
                                                            $statusBg = match($status) {
                                                                'confirmed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200',
                                                                'pending', 'pending_rebooking' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200',
                                                                'cancelled', 'operator_cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-200',
                                                                'refunded' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-200',
                                                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
                                                            };
                                                            $handledAt = $booking->verified_at ?? $booking->refund_processed_at ?? $booking->created_at;
                                                        @endphp
                                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                                            <td class="px-4 py-3 font-mono text-xs font-bold text-amber-600 dark:text-amber-400">
                                                                {{ $booking->transaction_number ?: "BK-{$booking->id}" }}
                                                            </td>
                                                            <td class="px-4 py-3">
                                                                <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ $booking->client_name ?: 'Guest User' }}</p>
                                                                <p class="text-[10px] text-gray-500">{{ $booking->client_email }}</p>
                                                            </td>
                                                            <td class="px-4 py-3 text-xs text-gray-600 dark:text-gray-300">
                                                                {{ $booking->origin ?: 'N/A' }} → {{ $booking->destination ?: 'N/A' }}
                                                                @if($booking->departure_date)
                                                                    <span class="block text-[10px] text-gray-400">{{ $booking->departure_date->format('M d, Y') }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-3 text-center">
                                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $statusBg }}">
                                                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                                </span>
                                                            </td>
                                                            <td class="px-4 py-3 font-bold text-gray-900 dark:text-white tabular-nums text-xs text-right">
                                                                ₱{{ number_format($booking->total_price, 2) }}
                                                            </td>
                                                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                                                {{ $handledAt ? \Carbon\Carbon::parse($handledAt)->format('M d, Y h:i A') : 'N/A' }}
                                                            </td>
                                                            <td class="px-4 py-3 text-right">
                                                                <a href="{{ \App\Filament\Resources\BookingResource::getUrl('view', ['record' => $booking->id]) }}" 
                                                                    target="_blank"
                                                                    class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                                                    View
                                                                    <x-heroicon-m-arrow-top-right-on-square class="h-3.5 w-3.5" />
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400 dark:text-gray-500">
                                                                No bookings found for this staff member in the selected period.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </x-filament::modal>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-sm text-gray-400 dark:text-gray-500">
                                    No staff members or performance data found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
