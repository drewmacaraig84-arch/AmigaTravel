<x-filament-panels::page>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <style>
        .dark .daterangepicker {
            background-color: #0f172a;
            border-color: #1e293b;
            color: #e2e8f0;
            font-family: inherit;
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
            background-color: #d97706;
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
            background-color: #d97706;
            color: #fff;
        }
        .dark .daterangepicker .drp-buttons {
            border-top: 1px solid #1e293b;
        }
    </style>

    <div class="space-y-6">
        {{-- ═══ Header Filter & Control Banner ═══ --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-800">
            <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-xl font-extrabold shadow-sm" style="background-color: #d97706 !important; color: #ffffff !important;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                Staff Performance Overview
                            </h2>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30">
                                Staff Audit &amp; Verification
                            </span>
                        </div>
                        <p class="mt-1 text-sm font-medium text-gray-600 dark:text-gray-300">
                            Real-time metrics for staff transaction volume, verification efficiency, and handled revenue
                        </p>
                    </div>
                </div>

                {{-- Action Controls --}}
                <div class="flex flex-wrap items-center gap-2.5 w-full xl:w-auto justify-start xl:justify-end">
                    {{-- Quick Period Filter Buttons --}}
                    <div class="inline-flex rounded-xl bg-gray-100 p-1 dark:bg-gray-800 border border-gray-200 dark:border-gray-700/60 flex-wrap gap-1 text-xs">
                        <button type="button" 
                            wire:click="setPeriod('all_time')"
                            class="rounded-lg px-3 py-1.5 font-bold transition {{ $period === 'all_time' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            All Time
                        </button>
                        <button type="button" 
                            wire:click="setPeriod('today')"
                            class="rounded-lg px-3 py-1.5 font-bold transition {{ $period === 'today' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            Today
                        </button>
                        <button type="button" 
                            wire:click="setPeriod('this_week')"
                            class="rounded-lg px-3 py-1.5 font-bold transition {{ $period === 'this_week' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                            This Week
                        </button>
                        <button type="button" 
                            wire:click="setPeriod('this_month')"
                            class="rounded-lg px-3 py-1.5 font-bold transition {{ $period === 'this_month' ? 'bg-amber-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-gray-700' }}">
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
                        <div class="relative w-[190px]">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="text" x-ref="picker" readonly
                                   placeholder="Custom Date Range"
                                   class="w-full cursor-pointer rounded-xl bg-gray-50 dark:bg-gray-800 py-1.5 pl-9 pr-3 text-xs font-bold text-gray-900 shadow-sm border border-gray-200 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 text-center dark:border-gray-700 dark:text-gray-100" />
                        </div>
                    </div>

                    {{-- Export CSV & PDF --}}
                    <div class="flex items-center gap-1.5">
                        <button type="button" wire:click="exportCsv"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 px-3.5 py-1.5 text-xs font-bold text-gray-700 dark:text-gray-200 transition border border-gray-200 dark:border-gray-700 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            CSV
                        </button>
                        <button type="button" wire:click="exportPdf"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white px-3.5 py-1.5 text-xs font-bold transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ 4-Column KPI Metric Cards (Matching MyPage Architecture) ═══ --}}
        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Total Handled --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-800 transition-all hover:shadow-md hover:border-amber-300 dark:hover:border-amber-700">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Bookings Handled</span>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums tracking-tight mb-2">
                    {{ number_format($summaryKpis['total_bookings']) }}
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total staff actions performed</p>
            </div>

            {{-- Revenue Handled --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-800 transition-all hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-700">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Revenue Handled</span>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 tabular-nums tracking-tight mb-2">
                    ₱{{ number_format($summaryKpis['total_revenue'], 2) }}
                </div>
                <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Total verified payment volume</p>
            </div>

            {{-- Completed Bookings --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-800 transition-all hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Completed Bookings</span>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums tracking-tight mb-2">
                    {{ number_format($summaryKpis['total_completed']) }}
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Fully confirmed &amp; verified trips</p>
            </div>

            {{-- Top Performer --}}
            <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-800 transition-all hover:shadow-md hover:border-purple-300 dark:hover:border-purple-700">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Top Performer</span>
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-500/10 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                    </div>
                </div>
                <div class="text-xl font-extrabold text-purple-600 dark:text-purple-300 truncate mb-2">
                    {{ $summaryKpis['top_staff_name'] }}
                </div>
                <p class="text-xs text-purple-600 dark:text-purple-400 font-medium">{{ $summaryKpis['top_staff_count'] }} bookings handled</p>
            </div>
        </div>

        {{-- ═══ Active Period Notice ═══ --}}
        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 px-1">
            <span>
                Showing metrics for: 
                <strong class="text-gray-900 dark:text-white font-bold">
                    @if($period === 'all_time')
                        All Recorded Time
                    @elseif($startDate && $endDate)
                        {{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
                    @else
                        {{ ucwords(str_replace('_', ' ', $period)) }}
                    @endif
                </strong>
            </span>
            <span>Total Staff Registered: <strong class="text-gray-900 dark:text-white font-bold">{{ $staffStats->count() }}</strong></span>
        </div>

        {{-- ═══ Staff Performance Table ═══ --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">Staff Audit &amp; Performance Records</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-0.5">Summary of transaction verifications, revenue totals, and completion metrics per staff account</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3.5 py-1 text-xs font-bold text-gray-900 dark:text-white w-fit">
                    {{ $staffStats->count() }} Staff Accounts
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800 text-xs uppercase tracking-wider text-gray-900 dark:text-white font-bold border-b border-gray-200 dark:border-gray-700/60">
                        <tr>
                            <th class="px-6 py-3.5 font-bold text-gray-900 dark:text-white">
                                Staff Member
                            </th>
                            <th class="px-6 py-3.5 font-bold text-gray-900 dark:text-white text-center">
                                Total Handled
                            </th>
                            <th class="px-6 py-3.5 font-bold text-gray-900 dark:text-white text-center">
                                Completed
                            </th>
                            <th class="px-6 py-3.5 font-bold text-gray-900 dark:text-white text-center">
                                Pending
                            </th>
                            <th class="px-6 py-3.5 font-bold text-gray-900 dark:text-white text-center">
                                Cancelled
                            </th>
                            <th class="px-6 py-3.5 font-bold text-gray-900 dark:text-white text-right">
                                Revenue Handled
                            </th>
                            <th class="px-6 py-3.5 font-bold text-gray-900 dark:text-white text-center">
                                Success Rate
                            </th>
                            <th class="px-6 py-3.5 text-right font-bold text-gray-900 dark:text-white">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($staffStats as $staff)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3.5">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-extrabold text-sm shadow-sm" style="background-color: #d97706 !important; color: #ffffff !important;">
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
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
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
                                                <thead class="text-xs text-gray-900 dark:text-white uppercase bg-gray-100 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-10 font-bold">
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
                                                                <p class="text-xs font-bold text-gray-900 dark:text-white">{{ $booking->client_name ?: 'Guest User' }}</p>
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
                                                                    class="inline-flex items-center gap-1 text-xs font-bold text-amber-600 hover:text-amber-500 dark:text-amber-400">
                                                                    View
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                                    </svg>
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
