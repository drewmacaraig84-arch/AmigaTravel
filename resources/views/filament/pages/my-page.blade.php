@php use App\Filament\Pages\AdminNotifications; @endphp

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- ═══ User Profile Banner ═══ --}}
        <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-800">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-xl font-extrabold shadow-sm" style="background-color: #d97706 !important; color: #ffffff !important;">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 2)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ auth()->user()?->name ?? 'Admin User' }}
                            </h2>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold bg-amber-500/15 text-amber-800 dark:text-amber-300 border border-amber-500/30">
                                Staff Profile
                            </span>
                        </div>
                        <p class="mt-1 text-sm font-medium text-gray-600 dark:text-gray-300">
                            {{ auth()->user()?->email ?? 'admin@amigagracia.com' }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Member since {{ auth()->user()?->created_at?->format('F d, Y') ?? 'July 2026' }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-col sm:items-end gap-1.5">
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-500/15 border border-emerald-500/30 px-3.5 py-1.5 text-xs font-semibold text-emerald-800 dark:text-emerald-300">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Staff Account Active
                    </span>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                        Staff Performance Connected
                    </p>
                </div>
            </div>
        </div>

        {{-- ═══ Staff Performance KPI Dashboard Cards ═══ --}}
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 pb-1 border-b border-gray-200/60 dark:border-gray-800">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">
                        My Staff Performance & Transactions
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                        Your personal transaction breakdown: completed, pending, and cancelled bookings
                    </p>
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-3 py-1 rounded-full border border-amber-200/60 dark:border-amber-800/60 w-fit">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Connected to your account
                </span>
            </div>

            <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 md:grid-cols-3">
                {{-- Total Handled --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-800 transition-all hover:shadow-md hover:border-blue-300 dark:hover:border-blue-800">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Handled Transactions</span>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums tracking-tight mb-2">
                        {{ number_format($stats['total_transactions'] ?? 0) }}
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Total bookings assigned to you</p>
                </div>

                {{-- Completed --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-800 transition-all hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-800">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Completed</span>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 tabular-nums tracking-tight mb-2">
                        {{ number_format($stats['completed'] ?? 0) }}
                    </div>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Successfully verified transactions</p>
                </div>

                {{-- Pending --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-800 transition-all hover:shadow-md hover:border-amber-300 dark:hover:border-amber-800">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pending</span>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-amber-600 dark:text-amber-400 tabular-nums tracking-tight mb-2">
                        {{ number_format($stats['pending'] ?? 0) }}
                    </div>
                    <p class="text-xs text-amber-600 dark:text-amber-400 font-medium">Awaiting processing</p>
                </div>

                {{-- Cancelled --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-800 transition-all hover:shadow-md hover:border-red-300 dark:hover:border-red-800">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Cancelled</span>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-500/10 text-red-600 dark:bg-red-500/20 dark:text-red-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-red-600 dark:text-red-400 tabular-nums tracking-tight mb-2">
                        {{ number_format($stats['cancelled'] ?? 0) }}
                    </div>
                    <p class="text-xs text-red-600 dark:text-red-400 font-medium">Cancelled / rejected transactions</p>
                </div>

                {{-- Revenue Handled --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-800 transition-all hover:shadow-md hover:border-emerald-300 dark:hover:border-emerald-800">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Revenue Handled</span>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-gray-900 dark:text-white tabular-nums tracking-tight mb-2">
                        ₱{{ number_format($stats['revenue_handled'] ?? 0, 2) }}
                    </div>
                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Total value of completed bookings</p>
                </div>

                {{-- Completion Rate --}}
                <div class="rounded-2xl bg-white p-6 shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-800 transition-all hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-800">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Completion Rate</span>
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-extrabold text-indigo-600 dark:text-indigo-400 tabular-nums tracking-tight mb-2">
                        {{ $stats['completion_rate'] ?? 100 }}%
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Staff verification success rate</p>
                </div>
            </div>
        </div>

        {{-- ═══ My Handled Transactions Table ═══ --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">My Handled Transactions</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-0.5">Live record of bookings completed, pending, and cancelled under your staff account</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3.5 py-1 text-xs font-bold text-gray-900 dark:text-white w-fit">
                    Latest {{ count($recentBookings) }} Transactions
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-100 dark:bg-gray-800 text-xs uppercase tracking-wider text-gray-900 dark:text-white font-bold border-b border-gray-200 dark:border-gray-700/60">
                        <tr>
                            <th class="px-6 py-3.5 font-bold text-gray-900 dark:text-white">Reference</th>
                            <th class="px-6 py-3.5 font-bold text-gray-900 dark:text-white">Client</th>
                            <th class="px-6 py-3.5 font-bold text-gray-900 dark:text-white">Route</th>
                            <th class="px-6 py-3.5 font-bold text-gray-900 dark:text-white">Staff Status</th>
                            <th class="px-6 py-3.5 font-bold text-gray-900 dark:text-white">Payment</th>
                            <th class="px-6 py-3.5 text-right font-bold text-gray-900 dark:text-white">Amount</th>
                            <th class="px-6 py-3.5 text-right font-bold text-gray-900 dark:text-white">Date Handled</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($recentBookings as $booking)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-6 py-4 font-mono font-medium text-gray-900 dark:text-white text-xs">{{ $booking['reference'] }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300 text-xs">{{ $booking['client'] }}</td>
                                <td class="px-6 py-4 text-gray-600 dark:text-gray-400 text-xs">{{ $booking['route'] }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $sc = match($booking['status']) {
                                            'confirmed' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200',
                                            'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200',
                                            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-200',
                                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $sc }}">
                                        {{ ucfirst($booking['status']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $pc = match($booking['payment_status']) {
                                            'paid' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200',
                                            'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-200',
                                            default => 'bg-red-100 text-red-800 dark:bg-red-900/60 dark:text-red-200',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $pc }}">
                                        {{ ucfirst($booking['payment_status']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white tabular-nums text-sm">₱{{ number_format($booking['total_amount'], 2) }}</td>
                                <td class="px-6 py-4 text-right text-gray-500 dark:text-gray-400 text-xs">{{ $booking['date'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-sm font-medium text-gray-500 dark:text-gray-400">
                                    No transactions handled under your staff profile yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═══ Downloadable Reports Suite ═══ --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-2 border-b border-gray-200/60 dark:border-gray-800">
                <div>
                    <h3 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">
                        Downloadable Reports &amp; Data Exports (PDF &amp; CSV)
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-0.5">
                        Export your personal transaction logs or general system reports instantly in PDF or CSV format.
                    </p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-3.5 py-1 text-xs font-bold text-gray-900 dark:text-white w-fit">
                    Instant Download
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- My Handled Transactions --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-800/40 gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm">My Handled Transactions</h4>
                                <span class="rounded-full bg-amber-500/15 px-2.5 py-0.5 text-[10px] font-bold text-amber-700 dark:text-amber-300">
                                    Personal Log
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Export only your completed, pending &amp; cancelled bookings</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            type="button"
                            wire:click="downloadReport('my_transactions')"
                            style="background-color: #d97706 !important; color: #ffffff !important;"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold shadow-sm hover:opacity-90 focus:outline-none whitespace-nowrap transition-all cursor-pointer"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>CSV</span>
                        </button>
                        <button
                            type="button"
                            wire:click="downloadPdf('my_transactions')"
                            style="background-color: #dc2626 !important; color: #ffffff !important;"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold shadow-sm hover:opacity-90 focus:outline-none whitespace-nowrap transition-all cursor-pointer"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>PDF</span>
                        </button>
                    </div>
                </div>

                {{-- All System Bookings --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-800/40 gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-purple-500/15 text-purple-600 dark:text-purple-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm">System Bookings</h4>
                                <span class="rounded-full bg-purple-500/15 px-2.5 py-0.5 text-[10px] font-bold text-purple-700 dark:text-purple-300">
                                    All Records
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Full reservation list across all staff members</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            type="button"
                            wire:click="downloadReport('bookings')"
                            style="background-color: #d97706 !important; color: #ffffff !important;"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold shadow-sm hover:opacity-90 focus:outline-none whitespace-nowrap transition-all cursor-pointer"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>CSV</span>
                        </button>
                        <button
                            type="button"
                            wire:click="downloadPdf('bookings')"
                            style="background-color: #dc2626 !important; color: #ffffff !important;"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold shadow-sm hover:opacity-90 focus:outline-none whitespace-nowrap transition-all cursor-pointer"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>PDF</span>
                        </button>
                    </div>
                </div>

                {{-- Ferry & Airline Routes --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-800/40 gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm">Ferry &amp; Airline Routes</h4>
                                <span class="rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 dark:text-emerald-300">
                                    Directory
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">All routes, operators, travel modes, and active status</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            type="button"
                            wire:click="downloadReport('ferry_routes')"
                            style="background-color: #d97706 !important; color: #ffffff !important;"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold shadow-sm hover:opacity-90 focus:outline-none whitespace-nowrap transition-all cursor-pointer"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>CSV</span>
                        </button>
                        <button
                            type="button"
                            wire:click="downloadPdf('ferry_routes')"
                            style="background-color: #dc2626 !important; color: #ffffff !important;"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold shadow-sm hover:opacity-90 focus:outline-none whitespace-nowrap transition-all cursor-pointer"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>PDF</span>
                        </button>
                    </div>
                </div>

                {{-- Schedules Report --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 rounded-2xl border border-gray-200 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-800/40 gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-500/15 text-blue-600 dark:text-blue-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-gray-900 dark:text-white text-sm">Trip Schedules</h4>
                                <span class="rounded-full bg-blue-500/15 px-2.5 py-0.5 text-[10px] font-bold text-blue-700 dark:text-blue-300">
                                    Schedules
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Departure &amp; arrival times, vehicles, and pricing</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            type="button"
                            wire:click="downloadReport('schedules')"
                            style="background-color: #d97706 !important; color: #ffffff !important;"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold shadow-sm hover:opacity-90 focus:outline-none whitespace-nowrap transition-all cursor-pointer"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>CSV</span>
                        </button>
                        <button
                            type="button"
                            wire:click="downloadPdf('schedules')"
                            style="background-color: #dc2626 !important; color: #ffffff !important;"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold shadow-sm hover:opacity-90 focus:outline-none whitespace-nowrap transition-all cursor-pointer"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span>PDF</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
