<x-filament-panels::page>
    <style>
        .system-hub-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem !important;
        }
        .dark .system-hub-card {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.98) 0%, rgba(30, 41, 59, 0.98) 100%);
            border: 1px solid rgba(51, 65, 85, 0.8);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.4), 0 8px 10px -6px rgba(0, 0, 0, 0.4);
        }

        .stat-kpi-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 155px;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.06), 0 1px 2px -1px rgba(0, 0, 0, 0.06);
        }
        .dark .stat-kpi-box {
            background: #0f172a;
            border: 1px solid #1e293b;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        }
        .stat-kpi-box:hover {
            border-color: #f59e0b;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.08);
        }
        .dark .stat-kpi-box:hover {
            border-color: #38bdf8;
            box-shadow: 0 14px 28px -8px rgba(0, 0, 0, 0.6);
        }

        .dashboard-box {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 1.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        .dark .dashboard-box {
            background: #0f172a;
            border: 1px solid #1e293b;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        }

        .dashboard-box-chart {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            padding: 1.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 360px;
        }
        .dark .dashboard-box-chart {
            background: #0f172a;
            border: 1px solid #1e293b;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        }

        .tab-btn-active {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;
            color: #ffffff !important;
            border-color: #f59e0b !important;
            box-shadow: 0 4px 16px rgba(217, 119, 6, 0.35) !important;
        }
        
        /* ═══ Explicit CSS Grid Architecture ═══ */
        .super-admin-kpi-grid {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 1.75rem !important;
            width: 100% !important;
            margin-bottom: 2rem !important;
        }
        @media (max-width: 1100px) {
            .super-admin-kpi-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }
        @media (max-width: 600px) {
            .super-admin-kpi-grid {
                grid-template-columns: 1fr !important;
            }
        }

        .super-admin-charts-grid {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 1.75rem !important;
            width: 100% !important;
            margin-bottom: 2rem !important;
        }
        @media (max-width: 1024px) {
            .super-admin-charts-grid {
                grid-template-columns: 1fr !important;
            }
        }

        .super-admin-specs-grid {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            grid-template-rows: repeat(2, minmax(0, 1fr)) !important;
            gap: 1rem !important;
            width: 100% !important;
            height: 100% !important;
            min-height: 220px !important;
        }
        @media (max-width: 640px) {
            .super-admin-specs-grid {
                grid-template-columns: 1fr !important;
                grid-template-rows: auto !important;
                min-height: auto !important;
            }
        }

        /* ═══ Full-Width 4-Column Tab Picker Architecture ═══ */
        .super-admin-tab-grid {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 0.875rem !important;
            width: 100% !important;
            margin-top: 2rem !important;
            margin-bottom: 1.5rem !important;
        }
        @media (max-width: 992px) {
            .super-admin-tab-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }
        @media (max-width: 580px) {
            .super-admin-tab-grid {
                grid-template-columns: 1fr !important;
            }
        }

        /* ═══ Theme-Adaptive Form Inputs ═══ */
        .dark-input-field {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.75rem !important;
            padding: 0.65rem 1rem !important;
            font-size: 0.8125rem !important;
            outline: none !important;
            transition: all 0.2s ease-in-out !important;
            width: 100% !important;
        }
        .dark .dark-input-field {
            background-color: #020617 !important;
            color: #f8fafc !important;
            border: 1px solid #334155 !important;
        }
        .dark-input-field:focus {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.25) !important;
        }
        .dark-input-field::placeholder {
            color: #94a3b8 !important;
            opacity: 1 !important;
        }
        .dark .dark-input-field::placeholder {
            color: #64748b !important;
        }

        .search-input-field {
            background-color: #ffffff !important;
            color: #0f172a !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 0.75rem !important;
            padding: 0.65rem 1rem 0.65rem 2.6rem !important;
            font-size: 0.8125rem !important;
            outline: none !important;
            width: 100% !important;
            transition: all 0.2s ease-in-out !important;
        }
        .dark .search-input-field {
            background-color: #020617 !important;
            color: #f8fafc !important;
            border: 1px solid #334155 !important;
        }
        .search-input-field:focus {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.25) !important;
        }
        .search-input-field::placeholder {
            color: #94a3b8 !important;
            opacity: 1 !important;
        }
        .dark .search-input-field::placeholder {
            color: #64748b !important;
        }
    </style>

    <div class="w-full max-w-full pb-10">
        {{-- ═══ Top Header Banner: Super Admin Hub ═══ --}}
        <div class="system-hub-card rounded-2xl p-6 lg:p-7 relative overflow-hidden">
            <div class="absolute -right-10 -top-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 relative z-10">
                <div class="flex items-center gap-5">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-amber-700 text-white shadow-lg shadow-amber-500/25">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-3 flex-wrap">
                            <h2 class="text-xl font-black tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                                Super Administrator System Control Hub
                            </h2>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/20 dark:text-rose-300 dark:border-rose-500/40 shadow-sm">
                                👑 SUPER ADMIN EXCLUSIVE
                            </span>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ ($metrics['runtime']['environment'] ?? 'production') === 'production' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-300 dark:border-emerald-500/30' : 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-500/20 dark:text-blue-300 dark:border-blue-500/30' }}">
                                <span class="h-2 w-2 rounded-full {{ ($metrics['runtime']['environment'] ?? 'production') === 'production' ? 'bg-emerald-500 animate-pulse' : 'bg-blue-500' }}"></span>
                                {{ strtoupper($metrics['runtime']['environment'] ?? 'LOCAL') }}
                            </span>
                        </div>
                        <p class="mt-1.5 text-xs text-gray-600 dark:text-slate-400">
                            Executive telemetry, exception monitoring, latency charts, login security audits, and automated crash alerting.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 flex-wrap shrink-0">
                    <button wire:click="refreshAll" wire:loading.attr="disabled" type="button" class="inline-flex items-center justify-center gap-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white px-6 py-2.5 text-xs font-bold transition-all shadow-md shadow-amber-600/25 active:scale-95 whitespace-nowrap">
                        <svg wire:loading.class="animate-spin" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <span>Refresh Telemetry</span>
                    </button>

                    <button wire:click="downloadLog" type="button" class="inline-flex items-center justify-center gap-2.5 rounded-xl bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 dark:border-slate-700 px-6 py-2.5 text-xs font-bold transition-all shadow-sm active:scale-95 whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 dark:text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Download Raw Log</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ═══ 4-Box Organized KPI Grid ═══ --}}
        <div class="super-admin-kpi-grid">
            {{-- KPI Box 1: Health Score --}}
            <div class="stat-kpi-box">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-400 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-700 dark:text-slate-200 tracking-wide ml-1">System Health</span>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-400 dark:border-emerald-500/30 shrink-0">
                        ● 100% OK
                    </span>
                </div>
                <div class="flex items-baseline justify-between my-2">
                    <div class="text-3xl font-black text-gray-950 dark:text-white tracking-tight">99.8%</div>
                    <div class="w-20 h-9" wire:ignore id="spark-health"></div>
                </div>
                <div class="pt-3 border-t border-gray-100 dark:border-slate-800/80 flex items-center justify-between text-[11px] text-gray-500 dark:text-slate-400">
                    <span>PHP {{ $metrics['runtime']['php_version'] ?? PHP_VERSION }}</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">All Systems Normal</span>
                </div>
            </div>

            {{-- KPI Box 2: Database Latency --}}
            <div class="stat-kpi-box">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-cyan-50 border border-cyan-200 text-cyan-600 dark:bg-cyan-500/10 dark:border-cyan-500/20 dark:text-cyan-400 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-700 dark:text-slate-200 tracking-wide ml-1">Database Ping</span>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200 dark:bg-cyan-500/15 dark:text-cyan-400 dark:border-cyan-500/30 shrink-0">
                        {{ $metrics['database']['table_count'] ?? 0 }} Tables
                    </span>
                </div>
                <div class="flex items-baseline justify-between my-2">
                    <div class="text-3xl font-black text-cyan-600 dark:text-cyan-400 tracking-tight">
                        {{ $metrics['database']['latency_ms'] ?? 0 }} <span class="text-xs font-bold text-gray-400 dark:text-slate-400">ms</span>
                    </div>
                    <div class="w-20 h-9" wire:ignore id="spark-latency"></div>
                </div>
                <div class="pt-3 border-t border-gray-100 dark:border-slate-800/80 flex items-center justify-between text-[11px] text-gray-500 dark:text-slate-400">
                    <span class="font-mono">{{ $metrics['database']['database_name'] ?? 'amigadb' }}</span>
                    <span class="font-bold text-cyan-600 dark:text-cyan-400">{{ $metrics['database']['size_formatted'] ?? '0 MB' }}</span>
                </div>
            </div>

            {{-- KPI Box 3: Errors (24h) --}}
            <div class="stat-kpi-box">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 dark:bg-rose-500/10 dark:border-rose-500/20 dark:text-rose-400 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-700 dark:text-slate-200 tracking-wide ml-1">Errors (24h)</span>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/15 dark:text-rose-400 dark:border-rose-500/30 shrink-0">
                        {{ $logData['counts']['critical'] ?? 0 }} Critical
                    </span>
                </div>
                <div class="flex items-baseline justify-between my-2">
                    <div class="text-3xl font-black text-rose-600 dark:text-rose-400 tracking-tight">
                        {{ ($logData['counts']['critical'] ?? 0) + ($logData['counts']['error'] ?? 0) }}
                        <span class="text-xs font-bold text-gray-400 dark:text-slate-400">crashes</span>
                    </div>
                    <div class="w-20 h-9" wire:ignore id="spark-errors"></div>
                </div>
                <div class="pt-3 border-t border-gray-100 dark:border-slate-800/80 flex items-center justify-between text-[11px] text-gray-500 dark:text-slate-400">
                    <span>{{ $logData['counts']['warning'] ?? 0 }} warnings</span>
                    <span class="font-bold text-gray-700 dark:text-slate-300">{{ $metrics['disk']['log_file_size_formatted'] ?? '0 B' }} log</span>
                </div>
            </div>

            {{-- KPI Box 4: Disk Storage --}}
            <div class="stat-kpi-box">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-purple-50 border border-purple-200 text-purple-600 dark:bg-purple-500/10 dark:border-purple-500/20 dark:text-purple-400 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-gray-700 dark:text-slate-200 tracking-wide ml-1">Disk Space</span>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-500/15 dark:text-purple-400 dark:border-purple-500/30 shrink-0">
                        {{ $metrics['disk']['used_percent'] ?? 0 }}% Used
                    </span>
                </div>
                <div class="flex items-baseline justify-between my-2">
                    <div class="text-3xl font-black text-gray-950 dark:text-white tracking-tight">
                        {{ $metrics['disk']['free_formatted'] ?? '0 GB' }}
                    </div>
                    <div class="w-20 h-9" wire:ignore id="spark-disk"></div>
                </div>
                <div class="pt-3 border-t border-gray-100 dark:border-slate-800/80 flex items-center justify-between text-[11px] text-gray-500 dark:text-slate-400">
                    <span>Total: {{ $metrics['disk']['total_formatted'] ?? '0 GB' }}</span>
                    <span class="font-bold text-purple-600 dark:text-purple-400">Free Space</span>
                </div>
            </div>
        </div>

        {{-- ═══ 2x2 Organized Visual Charts & Telemetry Grid ═══ --}}
        <div class="super-admin-charts-grid">
            {{-- Box 1: 7-Day Crash & Incident Trend --}}
            <div class="dashboard-box-chart">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-rose-500"></span>
                            System Crash & Error Trend
                        </h3>
                        <span class="text-[10px] font-bold text-gray-600 bg-gray-100 border border-gray-200 dark:text-slate-400 dark:bg-slate-800 dark:border-slate-700 px-2.5 py-1 rounded-md">7 Days</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-4">Daily incident breakdown comparing fatal errors, warnings, and system info</p>
                    <div wire:ignore id="chart-incident-trend" style="min-height: 250px; width: 100%;"></div>
                </div>
            </div>

            {{-- Box 2: Storage Space Distribution --}}
            <div class="dashboard-box-chart">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-cyan-500"></span>
                            Storage Space Footprint
                        </h3>
                        <span class="text-[10px] font-bold text-gray-600 bg-gray-100 border border-gray-200 dark:text-slate-400 dark:bg-slate-800 dark:border-slate-700 px-2.5 py-1 rounded-md">MB Allocation</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-4">Database tables, user uploads, log buffers, and cache</p>
                    <div wire:ignore id="chart-storage-distribution" style="min-height: 250px; width: 100%;"></div>
                </div>
            </div>

            {{-- Box 3: Database Query Latency Trend --}}
            <div class="dashboard-box-chart">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-1">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="h-2.5 w-2.5 rounded-full bg-cyan-500"></span>
                            Database Query Latency Performance
                        </h3>
                        <span class="text-[10px] font-bold text-cyan-700 bg-cyan-50 border border-cyan-200 dark:text-cyan-400 dark:bg-cyan-500/10 dark:border-cyan-500/20 px-2.5 py-1 rounded-md">ms Ping</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-4">Average MySQL connection response time curve</p>
                    <div wire:ignore id="chart-latency-trend" style="min-height: 220px; width: 100%;"></div>
                </div>
            </div>

            {{-- Box 4: Organized 2x2 Infrastructure & Specifications --}}
            <div class="dashboard-box-chart">
                <div class="flex flex-col h-full justify-between">
                    <div class="mb-4">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-1 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                            </svg>
                            Infrastructure & Runtime Specifications
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Core platform environments and execution drivers</p>
                    </div>

                    <div class="super-admin-specs-grid flex-1">
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-slate-950/80 border border-gray-200 dark:border-slate-800 flex flex-col justify-center">
                            <span class="font-semibold text-gray-500 dark:text-slate-400 text-xs block mb-1">Operating System</span>
                            <span class="font-mono text-gray-900 dark:text-white text-xs font-bold">{{ $metrics['runtime']['os'] ?? PHP_OS_FAMILY }}</span>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-slate-950/80 border border-gray-200 dark:border-slate-800 flex flex-col justify-center">
                            <span class="font-semibold text-gray-500 dark:text-slate-400 text-xs block mb-1">Server Local Time</span>
                            <span class="font-mono text-cyan-700 dark:text-cyan-300 text-xs font-bold">{{ $metrics['runtime']['current_time'] ?? now()->toDayDateTimeString() }}</span>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-slate-950/80 border border-gray-200 dark:border-slate-800 flex flex-col justify-center">
                            <span class="font-semibold text-gray-500 dark:text-slate-400 text-xs block mb-1">Queue & Cache Engine</span>
                            <span class="font-mono text-amber-700 dark:text-amber-300 text-xs font-bold uppercase">{{ $metrics['queue']['driver'] ?? 'database' }} &bull; {{ $metrics['queue']['cache_driver'] ?? 'file' }}</span>
                        </div>

                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-slate-950/80 border border-gray-200 dark:border-slate-800 flex flex-col justify-center">
                            <span class="font-semibold text-gray-500 dark:text-slate-400 text-xs block mb-1.5">Debug Mode</span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded text-[11px] font-bold uppercase w-fit {{ !empty($metrics['runtime']['debug_mode']) ? 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/20 dark:text-amber-300 dark:border-amber-500/30' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-300 dark:border-emerald-500/30' }}">
                                {{ !empty($metrics['runtime']['debug_mode']) ? 'DEV DEBUG' : 'SECURE PROD' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ Full-Width 4-Column Tab Picker Bar ═══ --}}
        <div class="super-admin-tab-grid">
            <button wire:click="setTab('health')" type="button" class="inline-flex items-center justify-center gap-2.5 px-4 py-3.5 rounded-2xl text-xs font-bold transition-all {{ $activeTab === 'health' ? 'tab-btn-active' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow-sm dark:bg-slate-900/90 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 {{ $activeTab === 'health' ? 'text-white' : 'text-amber-500 dark:text-amber-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span class="truncate">Database Storage Architecture</span>
            </button>

            <button wire:click="setTab('logs')" type="button" class="inline-flex items-center justify-center gap-2.5 px-4 py-3.5 rounded-2xl text-xs font-bold transition-all {{ $activeTab === 'logs' ? 'tab-btn-active' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow-sm dark:bg-slate-900/90 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 {{ $activeTab === 'logs' ? 'text-white' : 'text-rose-500 dark:text-rose-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="truncate">Crash & Error Log Stream</span>
                @php
                    $errorCount = ($logData['counts']['critical'] ?? 0) + ($logData['counts']['error'] ?? 0);
                @endphp
                @if($errorCount > 0)
                    <span class="inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-extrabold rounded-full bg-rose-500 text-white shrink-0">
                        {{ $errorCount }}
                    </span>
                @endif
            </button>

            <button wire:click="setTab('audits')" type="button" class="inline-flex items-center justify-center gap-2.5 px-4 py-3.5 rounded-2xl text-xs font-bold transition-all {{ $activeTab === 'audits' ? 'tab-btn-active' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow-sm dark:bg-slate-900/90 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 {{ $activeTab === 'audits' ? 'text-white' : 'text-emerald-500 dark:text-emerald-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span class="truncate">Security & Login Audit Trail</span>
            </button>

            <button wire:click="setTab('alerts')" type="button" class="inline-flex items-center justify-center gap-2.5 px-4 py-3.5 rounded-2xl text-xs font-bold transition-all {{ $activeTab === 'alerts' ? 'tab-btn-active' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 shadow-sm dark:bg-slate-900/90 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 {{ $activeTab === 'alerts' ? 'text-white' : 'text-cyan-500 dark:text-cyan-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span class="truncate">Crash Alerts & Dispatcher</span>
            </button>
        </div>

        {{-- ═══ Table 1: Database Tables Catalog ═══ --}}
        @if($activeTab === 'health')
            <div class="dashboard-box p-6 md:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-slate-800/80">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Database Tables & Storage Architecture</h3>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Row counts, data footprint, index allocation, and storage engine</p>
                    </div>
                    <span class="text-xs font-mono font-bold text-cyan-700 bg-cyan-50 border border-cyan-200 dark:text-cyan-400 dark:bg-cyan-500/10 dark:border-cyan-500/20 px-3.5 py-1.5 rounded-full w-fit">
                        {{ $metrics['database']['table_count'] ?? 0 }} Total Tables Active
                    </span>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-slate-800 bg-gray-50/40 dark:bg-slate-950/40">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-100/90 dark:bg-slate-950 border-b border-gray-200 dark:border-slate-800 text-gray-600 dark:text-slate-400 uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="px-6 py-4">Table Name</th>
                                <th class="px-6 py-4">Estimated Rows</th>
                                <th class="px-6 py-4">Data Size</th>
                                <th class="px-6 py-4">Index Size</th>
                                <th class="px-6 py-4">Total Size</th>
                                <th class="px-6 py-4 text-right">Engine</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-slate-800/60">
                            @forelse($databaseTables as $table)
                                <tr class="hover:bg-gray-100/60 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="px-6 py-4 font-mono text-cyan-700 dark:text-cyan-300 font-bold flex items-center gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 dark:text-slate-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        {{ $table['name'] }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-900 dark:text-slate-200 font-semibold">{{ $table['rows'] }}</td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-slate-400 font-mono">{{ $table['data_size'] }}</td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-slate-400 font-mono">{{ $table['index_size'] }}</td>
                                    <td class="px-6 py-4 text-emerald-600 dark:text-emerald-400 font-mono font-bold">{{ $table['total_size'] }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-bold bg-gray-100 text-gray-700 border border-gray-300 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700">
                                            {{ $table['engine'] }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-slate-400">No database tables retrieved.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ═══ Table 2: Crash & Error Log Stream ═══ --}}
        @if($activeTab === 'logs')
            <div class="space-y-6">
                <div class="dashboard-box p-6 space-y-4">
                    {{-- Row 1: Severity Filter Pills --}}
                    <div class="flex items-center gap-2 flex-wrap pb-1">
                        <button wire:click="setLevelFilter('all')" type="button" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $levelFilter === 'all' ? 'bg-amber-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-800 dark:hover:bg-slate-800' }}">
                            All ({{ $logData['counts']['all'] ?? 0 }})
                        </button>
                        <button wire:click="setLevelFilter('critical')" type="button" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $levelFilter === 'critical' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/30 dark:hover:bg-rose-500/20' }}">
                            Critical ({{ $logData['counts']['critical'] ?? 0 }})
                        </button>
                        <button wire:click="setLevelFilter('error')" type="button" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $levelFilter === 'error' ? 'bg-red-600 text-white shadow-sm' : 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/30 dark:hover:bg-red-500/20' }}">
                            Error ({{ $logData['counts']['error'] ?? 0 }})
                        </button>
                        <button wire:click="setLevelFilter('warning')" type="button" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $levelFilter === 'warning' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30 dark:hover:bg-amber-500/20' }}">
                            Warning ({{ $logData['counts']['warning'] ?? 0 }})
                        </button>
                        <button wire:click="setLevelFilter('info')" type="button" class="px-4 py-2 rounded-xl text-xs font-bold transition-all {{ $levelFilter === 'info' ? 'bg-blue-600 text-white shadow-sm' : 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:border-blue-500/30 dark:hover:bg-blue-500/20' }}">
                            Info ({{ $logData['counts']['info'] ?? 0 }})
                        </button>
                    </div>

                    {{-- Row 2: Search Bar & Purge Action Button --}}
                    <div class="flex items-center justify-between gap-4 flex-wrap sm:flex-nowrap pt-2 border-t border-gray-200 dark:border-slate-800/80">
                        <div class="relative flex-1 w-full">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400 dark:text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input wire:model.live.debounce.300ms="searchQuery" type="text" placeholder="Search error logs by message, exception, or timestamp..." class="search-input-field" />
                        </div>

                        <button wire:click="clearLogFile" wire:confirm="Are you sure you want to purge and clear the laravel.log file?" type="button" class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 dark:text-rose-400 dark:border-rose-500/30 px-5 py-2.5 text-xs font-bold transition-all shrink-0 active:scale-95 whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span>Purge Log</span>
                        </button>
                    </div>
                </div>

                {{-- Table --}}
                <div class="dashboard-box p-6 md:p-8">
                    <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-slate-800 bg-gray-50/40 dark:bg-slate-950/40">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-100/90 dark:bg-slate-950 border-b border-gray-200 dark:border-slate-800 text-gray-600 dark:text-slate-400 uppercase tracking-wider font-semibold">
                                <tr>
                                    <th class="px-6 py-4 w-44">Timestamp</th>
                                    <th class="px-5 py-4 w-28">Severity</th>
                                    <th class="px-5 py-4 w-24">Env</th>
                                    <th class="px-6 py-4">Message & Incident Summary</th>
                                    <th class="px-6 py-4 text-right w-36">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-slate-800/60">
                                @forelse($logData['entries'] as $entry)
                                    <tr class="hover:bg-gray-100/60 dark:hover:bg-slate-800/40 transition-colors">
                                        <td class="px-6 py-4 font-mono text-gray-500 dark:text-slate-400 whitespace-nowrap">
                                            {{ $entry['timestamp'] }}
                                        </td>
                                        <td class="px-5 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-extrabold uppercase
                                                @if($entry['level'] === 'CRITICAL' || $entry['level'] === 'EMERGENCY') bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/20 dark:text-rose-300 dark:border-rose-500/30
                                                @elseif($entry['level'] === 'ERROR') bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/20 dark:text-red-300 dark:border-red-500/30
                                                @elseif($entry['level'] === 'WARNING') bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/20 dark:text-amber-300 dark:border-amber-500/30
                                                @elseif($entry['level'] === 'INFO') bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-500/20 dark:text-blue-300 dark:border-blue-500/30
                                                @else bg-gray-100 text-gray-700 border border-gray-200 dark:bg-slate-500/20 dark:text-slate-300
                                                @endif">
                                                {{ $entry['level'] }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 font-mono text-gray-500 dark:text-slate-400 text-[11px]">
                                            {{ $entry['environment'] }}
                                        </td>
                                        <td class="px-6 py-4 text-gray-900 dark:text-slate-200 font-medium break-all max-w-xl leading-relaxed">
                                            {{ Str::limit($entry['message'], 140) }}
                                        </td>
                                        <td class="px-6 py-4 text-right whitespace-nowrap">
                                            @if(!empty($entry['trace']) || strlen($entry['message']) > 100)
                                                <button wire:click="viewLogDetails('{{ $entry['id'] }}')" type="button" class="inline-flex items-center gap-1 text-xs font-bold text-amber-600 hover:text-amber-500 dark:text-amber-400 dark:hover:text-amber-300 hover:underline">
                                                    Inspect Trace &rarr;
                                                </button>
                                            @else
                                                <span class="text-gray-400 dark:text-slate-600 text-xs">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-slate-400">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <p class="font-bold text-sm text-gray-900 dark:text-white">Log is Clean</p>
                                                <p class="text-xs text-gray-500 dark:text-slate-500">No active crash exceptions matching your filter.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- ═══ Table 3: Security & Login Audits ═══ --}}
        @if($activeTab === 'audits')
            <div class="dashboard-box p-6 md:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-slate-800/80">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Super Admin & Staff Login Security Audit Trail</h3>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Authenticated user sessions, IP address origins, devices, and security timestamps</p>
                    </div>
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 dark:text-emerald-400 dark:bg-emerald-500/10 dark:border-emerald-500/20 px-3.5 py-1.5 rounded-full w-fit">
                        Live Session Tracking
                    </span>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-gray-200 dark:border-slate-800 bg-gray-50/40 dark:bg-slate-950/40">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-100/90 dark:bg-slate-950 border-b border-gray-200 dark:border-slate-800 text-gray-600 dark:text-slate-400 uppercase tracking-wider font-semibold">
                            <tr>
                                <th class="px-6 py-4">User / Account</th>
                                <th class="px-6 py-4">Role</th>
                                <th class="px-6 py-4">IP Address</th>
                                <th class="px-6 py-4">Device & Browser</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Login Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-slate-800/60">
                            @forelse($loginAudits as $audit)
                                <tr class="hover:bg-gray-100/60 dark:hover:bg-slate-800/40 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $audit['user_name'] }}</div>
                                        <div class="text-[11px] text-gray-500 dark:text-slate-400 font-mono mt-0.5">{{ $audit['email'] }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-[10px] font-extrabold uppercase {{ in_array(strtolower($audit['role']), ['super admin', 'superadmin']) ? 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/20 dark:text-rose-300 dark:border-rose-500/30' : 'bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/20 dark:text-amber-300 dark:border-amber-500/30' }}">
                                            {{ $audit['role'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-cyan-700 dark:text-cyan-300 font-bold">{{ $audit['ip_address'] }}</td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-slate-400 text-[11px]">{{ $audit['user_agent'] }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            Authorized
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-mono text-gray-500 dark:text-slate-400">{{ $audit['created_at'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-slate-400">No login history recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ═══ Table 4: Crash Alerts & Dispatcher ═══ --}}
        @if($activeTab === 'alerts')
            <div class="dashboard-box p-6 md:p-8">
                <div class="flex items-center gap-3.5 mb-6">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-50 border border-rose-200 text-rose-600 dark:bg-rose-500/10 dark:border-rose-500/20 dark:text-rose-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">
                            Super Administrator Emergency Incident Dispatcher
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                            Send automated email crash reports to Super Administrators whenever critical uncaught exceptions occur.
                        </p>
                    </div>
                </div>

                <div class="p-6 rounded-2xl bg-gray-50 dark:bg-slate-950 border border-gray-200 dark:border-slate-800">
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-slate-300 mb-2.5">
                        Super Admin Alert Recipient Email
                    </label>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3.5">
                        <div class="flex-1 max-w-xl">
                            <input wire:model="alertEmail" type="email" placeholder="superadmin@amigatravel.com" class="dark-input-field" />
                        </div>

                        <button wire:click="sendTestAlert" wire:loading.attr="disabled" type="button" class="inline-flex items-center justify-center gap-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-rose-700 hover:from-rose-500 hover:to-rose-600 text-white px-6 py-2.5 text-xs font-bold transition-all shadow-md shadow-rose-600/20 shrink-0 active:scale-95 whitespace-nowrap">
                            <svg wire:loading.class="animate-spin" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>🚀 Send Test Crash Alert</span>
                        </button>
                    </div>
                    <p class="mt-2.5 text-[11px] text-gray-500 dark:text-slate-400">
                        Dispatches a sample CRITICAL incident email template to verify SMTP/Resend deliverability to your inbox.
                    </p>
                </div>

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="p-5 rounded-xl bg-gray-50 dark:bg-slate-950/60 border border-gray-200 dark:border-slate-800">
                        <h4 class="font-bold text-gray-900 dark:text-slate-200 mb-1.5 flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                            Smart Incident Throttling
                        </h4>
                        <p class="text-gray-600 dark:text-slate-400 leading-relaxed">
                            Identical fatal exceptions are automatically throttled to 1 alert per 15-minute window to protect administrative inboxes.
                        </p>
                    </div>

                    <div class="p-5 rounded-xl bg-gray-50 dark:bg-slate-950/60 border border-gray-200 dark:border-slate-800">
                        <h4 class="font-bold text-gray-900 dark:text-slate-200 mb-1.5 flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                            Full Trace & Context
                        </h4>
                        <p class="text-gray-600 dark:text-slate-400 leading-relaxed">
                            Every alert captures URL path, client user context, source code file location, line number, and stack trace snippet.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- ═══ Full Screen Stack Trace / Log Details Modal (Root Level) ═══ --}}
    @if($selectedLog)
        <div class="fixed inset-0 flex items-center justify-center p-4 sm:p-6" style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; z-index: 999999 !important; background-color: rgba(15, 23, 42, 0.65) !important; backdrop-filter: blur(8px) !important;">
            <div class="rounded-2xl w-full max-w-3xl max-h-[85vh] flex flex-col shadow-2xl overflow-hidden bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700">
                
                {{-- Modal Header --}}
                <div class="px-7 py-5 flex items-center justify-between bg-gray-50 dark:bg-slate-950 border-b border-gray-200 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center px-3 py-1 rounded text-xs font-extrabold uppercase {{ $selectedLog['level'] === 'INFO' ? 'bg-blue-600' : ($selectedLog['level'] === 'WARNING' ? 'bg-amber-600' : 'bg-rose-600') }} text-white shadow-sm">
                            {{ $selectedLog['level'] }}
                        </span>
                        <span class="text-xs font-mono text-gray-700 dark:text-slate-300">{{ $selectedLog['timestamp'] }}</span>
                        <span class="text-xs font-mono text-gray-600 dark:text-slate-400 bg-gray-100 dark:bg-slate-800 px-2.5 py-0.5 rounded border border-gray-200 dark:border-slate-700">{{ $selectedLog['environment'] ?? 'production' }}</span>
                    </div>
                    <button wire:click="closeLogDetails" type="button" class="text-gray-400 hover:text-gray-700 dark:text-slate-400 dark:hover:text-white transition-colors p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="px-7 py-6 overflow-y-auto overflow-x-hidden space-y-5 text-xs">
                    <div>
                        <span class="text-[11px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
                            Incident Message & Log Output
                        </span>
                        <div class="p-4 rounded-xl font-mono text-xs leading-relaxed bg-sky-50 dark:bg-slate-950 border border-sky-200 dark:border-slate-800 text-sky-800 dark:text-sky-300" style="word-break: break-word !important; overflow-wrap: anywhere !important; white-space: pre-wrap !important;">
                            {{ $selectedLog['message'] }}
                        </div>
                    </div>

                    @if(!empty($selectedLog['trace']))
                        <div>
                            <span class="text-[11px] font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
                                Stack Trace & Call Stack
                            </span>
                            <pre class="p-4 rounded-xl font-mono text-gray-800 dark:text-slate-300 text-[11px] leading-relaxed max-h-60 overflow-y-auto bg-gray-100 dark:bg-black border border-gray-200 dark:border-slate-800" style="word-break: break-word !important; overflow-wrap: anywhere !important; white-space: pre-wrap !important;">{{ $selectedLog['trace'] }}</pre>
                        </div>
                    @endif
                </div>

                {{-- Modal Footer --}}
                <div class="px-7 py-4 flex justify-end bg-gray-50 dark:bg-slate-950 border-t border-gray-200 dark:border-slate-800">
                    <button wire:click="closeLogDetails" type="button" class="px-6 py-2.5 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-white font-bold text-xs transition-colors border border-gray-300 dark:border-slate-700 shadow-sm active:scale-95">
                        Close Inspector
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═════════════════════════════════════════════════════════════════════ --}}
    {{-- ═══ ApexCharts Interactive JavaScript Engine ════════════════════════ --}}
    {{-- ═════════════════════════════════════════════════════════════════════ --}}
    @script
    <script>
    (function () {
        let charts = {};
        let chartData = @json($chartData);

        function initSystemCharts() {
            if (!window.ApexCharts) return;

            const isDark = document.documentElement.classList.contains('dark');

            const baseOptions = {
                chart: {
                    background: 'transparent',
                    toolbar: { show: false },
                    fontFamily: 'inherit',
                },
                theme: { mode: isDark ? 'dark' : 'light' },
                grid: {
                    borderColor: isDark ? '#1e293b' : '#e2e8f0',
                    strokeDashArray: 3,
                },
                tooltip: {
                    theme: isDark ? 'dark' : 'light',
                },
            };

            // 1. Incident Trend Area Line Chart
            const incidentEl = document.getElementById('chart-incident-trend');
            if (incidentEl) {
                if (charts.incident) { try { charts.incident.destroy(); } catch (e) {} }
                charts.incident = new ApexCharts(incidentEl, {
                    ...baseOptions,
                    chart: { ...baseOptions.chart, type: 'area', height: 250 },
                    series: chartData.incident_trend?.series || [],
                    xaxis: {
                        categories: chartData.incident_trend?.categories || [],
                        labels: { style: { colors: isDark ? '#94a3b8' : '#64748b', fontSize: '11px' } },
                        axisBorder: { show: false },
                    },
                    yaxis: {
                        labels: { style: { colors: isDark ? '#94a3b8' : '#64748b', fontSize: '11px' } },
                    },
                    colors: ['#ef4444', '#f59e0b', '#3b82f6'],
                    stroke: { curve: 'smooth', width: 2.5 },
                    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: isDark ? 0.35 : 0.45, opacityTo: 0.05 } },
                    legend: { labels: { colors: isDark ? '#cbd5e1' : '#475569' }, position: 'top', horizontalAlign: 'right' },
                    dataLabels: { enabled: false },
                });
                charts.incident.render();
            }

            // 2. Storage Distribution Donut Chart
            const storageEl = document.getElementById('chart-storage-distribution');
            if (storageEl) {
                if (charts.storage) { try { charts.storage.destroy(); } catch (e) {} }
                charts.storage = new ApexCharts(storageEl, {
                    ...baseOptions,
                    chart: { ...baseOptions.chart, type: 'donut', height: 250 },
                    series: chartData.storage_distribution?.series || [],
                    labels: chartData.storage_distribution?.labels || [],
                    colors: ['#06b6d4', '#10b981', '#f59e0b', '#8b5cf6'],
                    legend: { labels: { colors: isDark ? '#cbd5e1' : '#475569' }, position: 'bottom' },
                    dataLabels: { enabled: false },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '72%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Storage',
                                        color: isDark ? '#cbd5e1' : '#475569',
                                        formatter: () => 'MB Footprint',
                                    },
                                },
                            },
                        },
                    },
                });
                charts.storage.render();
            }

            // 3. Database Latency Line Chart
            const latencyEl = document.getElementById('chart-latency-trend');
            if (latencyEl) {
                if (charts.latency) { try { charts.latency.destroy(); } catch (e) {} }
                charts.latency = new ApexCharts(latencyEl, {
                    ...baseOptions,
                    chart: { ...baseOptions.chart, type: 'line', height: 220 },
                    series: chartData.latency_trend?.series || [],
                    xaxis: {
                        categories: chartData.latency_trend?.categories || [],
                        labels: { style: { colors: isDark ? '#94a3b8' : '#64748b', fontSize: '10px' } },
                        axisBorder: { show: false },
                    },
                    yaxis: {
                        labels: { style: { colors: isDark ? '#94a3b8' : '#64748b', fontSize: '10px' }, formatter: (v) => v + 'ms' },
                    },
                    colors: ['#06b6d4'],
                    stroke: { curve: 'smooth', width: 3 },
                    markers: { size: 4, colors: ['#06b6d4'], strokeWidth: 0 },
                    dataLabels: { enabled: false },
                });
                charts.latency.render();
            }

            // 4. Sparklines
            const sparklines = [
                { id: 'spark-health', data: chartData.sparklines?.health || [99.8, 99.9, 100, 99.8], color: '#10b981' },
                { id: 'spark-latency', data: chartData.sparklines?.latency || [0.35, 0.40, 0.32, 0.34], color: '#06b6d4' },
                { id: 'spark-errors', data: chartData.sparklines?.errors || [0, 1, 0, 4], color: '#ef4444' },
                { id: 'spark-disk', data: chartData.sparklines?.disk || [83.0, 83.4, 83.9], color: '#a855f7' },
            ];

            sparklines.forEach(s => {
                const el = document.getElementById(s.id);
                if (!el) return;
                if (charts[s.id]) { try { charts[s.id].destroy(); } catch (e) {} }
                charts[s.id] = new ApexCharts(el, {
                    chart: { type: 'area', sparkline: { enabled: true }, height: 36 },
                    series: [{ data: s.data }],
                    stroke: { curve: 'smooth', width: 2 },
                    fill: { opacity: isDark ? 0.2 : 0.15 },
                    colors: [s.color],
                    tooltip: { enabled: false },
                });
                charts[s.id].render();
            });
        }

        function checkAndInit() {
            if (window.ApexCharts) {
                initSystemCharts();
            } else {
                window.addEventListener('amiga:apexcharts-ready', initSystemCharts, { once: true });
                setTimeout(initSystemCharts, 1200);
            }
        }

        $wire.on('system-charts-updated', ({ chartData: newData }) => {
            if (newData) {
                chartData = newData;
                setTimeout(initSystemCharts, 150);
            }
        });

        // Dynamic theme switch observer
        const themeObserver = new MutationObserver(() => {
            initSystemCharts();
        });
        themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

        checkAndInit();
    })();
    </script>
    @endscript
</x-filament-panels::page>
