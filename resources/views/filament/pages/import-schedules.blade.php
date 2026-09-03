<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/admin-import-schedules.css') }}">

    <div class="ais-page-container">

        {{-- ──────────────────────────────────────────────
             HERO BANNER
        ────────────────────────────────────────────── --}}
        <div class="ais-hero">
            <div class="ais-hero-pattern"></div>
            <div class="ais-hero-glow-1"></div>
            <div class="ais-hero-glow-2"></div>

            <div class="ais-hero-content">
                <div>
                    <div class="ais-hero-badge">
                        <span class="ais-badge-pulse"></span>
                        Schedule Ingestion Engine • Ready
                    </div>
                    <h1 class="ais-hero-title">Multi-Operator Schedule Import Center</h1>
                    <p class="ais-hero-desc">
                        Intelligently ingest operational timetables, recurring route matrices, vessel configurations, passenger accommodation tiers, and vehicle tariffs across all default and custom operators.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        wire:click="downloadSampleTemplate"
                        type="button"
                        class="ais-hero-btn"
                        title="Download sample standard CSV template"
                    >
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download CSV Template
                    </button>
                </div>
            </div>
        </div>

        {{-- ──────────────────────────────────────────────
             OPERATOR SELECTION HUB
        ────────────────────────────────────────────── --}}
        <div class="ais-card">
            <div class="ais-card-header">
                <div class="ais-card-title-group">
                    <span class="ais-step-number">1</span>
                    <div>
                        <h2 class="ais-card-title">Select Operator Target</h2>
                        <p class="ais-card-subtitle">Choose one of the 5 default travel operators or provision a custom operator on the fly.</p>
                    </div>
                </div>

                <div class="ais-status-pill">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Target: <strong>{{ $selectedOperator === 'custom' ? ($customOperatorName ?: 'New Custom Operator') : $selectedOperator }}</strong></span>
                </div>
            </div>

            <div class="ais-operator-grid">
                @foreach(self::DEFAULT_OPERATORS as $op)
                    @php
                        $isSelected = ($selectedOperator === $op['name']);
                    @endphp
                    <button
                        wire:click="selectOperator('{{ $op['name'] }}')"
                        type="button"
                        class="ais-op-item {{ $isSelected ? 'is-active' : '' }}"
                    >
                        @if($isSelected)
                            <div class="ais-op-active-check">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        @endif

                        <div class="ais-op-logo-box">
                            <img
                                src="{{ asset('images/' . $op['logo']) }}"
                                alt="{{ $op['name'] }}"
                                class="ais-op-logo-img"
                                onerror="this.src='{{ asset('images/amiga-logo-transparent.png') }}'"
                            />
                        </div>

                        <div class="ais-op-name" title="{{ $op['displayName'] ?? $op['name'] }}">
                            {{ $op['displayName'] ?? $op['name'] }}
                        </div>

                        <span class="ais-mode-pill {{ $op['mode'] }}">
                            {{ $op['mode'] }}
                        </span>
                    </button>
                @endforeach

                {{-- + Custom Operator Card --}}
                <button
                    wire:click="selectOperator('custom')"
                    type="button"
                    class="ais-op-item is-custom-btn {{ $selectedOperator === 'custom' ? 'is-active' : '' }}"
                >
                    @if($selectedOperator === 'custom')
                        <div class="ais-op-active-check">
                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    @endif

                    <div class="ais-op-add-circle">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>

                    <div class="ais-op-name">+ New Operator</div>
                    <span class="ais-mode-pill custom" style="background: rgba(245, 158, 11, 0.12); color: #d97706; border: 1px solid rgba(245, 158, 11, 0.25);">
                        Custom
                    </span>
                </button>
            </div>

            {{-- Custom Operator Expandable Form --}}
            @if($selectedOperator === 'custom')
                <div class="ais-custom-panel">
                    <div class="ais-form-group">
                        <label class="ais-label">
                            <span>Operator Name</span>
                            <span class="text-amber-500 font-bold">*</span>
                        </label>
                        <div class="ais-input-wrap">
                            <div class="ais-input-icon">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <input
                                type="text"
                                wire:model.live="customOperatorName"
                                placeholder="e.g. FastCat, OceanJet, Sunlight Air, Island Hopper"
                                class="ais-input ais-input-with-icon"
                            />
                        </div>
                    </div>

                    <div class="ais-form-group">
                        <label class="ais-label">
                            <span>Transport Mode</span>
                        </label>
                        <div class="ais-input-wrap">
                            <div class="ais-input-icon">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </div>
                            <select
                                wire:model="mode"
                                class="ais-input ais-input-with-icon cursor-pointer"
                            >
                                <option value="ferry">🚢 Ferry (Vessels, Cabins & Accommodations)</option>
                                <option value="airline">✈️ Airline (Flights, Seat Classes & Baggage)</option>
                            </select>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- ──────────────────────────────────────────────
             MAIN CONFIGURATION & FILE UPLOAD
        ────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left 2 Columns: Date Horizon, Starlite Info, Upload --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Date Horizon Card --}}
                <div class="ais-card">
                    <div class="ais-card-header">
                        <div class="ais-card-title-group">
                            <span class="ais-step-number">2</span>
                            <div>
                                <h2 class="ais-card-title">Target Date Horizon</h2>
                                <p class="ais-card-subtitle">
                                    Define the schedule generation window for expanding recurring weekly timetables (e.g. Daily, Odd/Even days, Wed/Fri/Sun).
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="ais-form-group">
                            <label class="ais-label">Start Date</label>
                            <div class="ais-input-wrap">
                                <div class="ais-input-icon">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input
                                    type="date"
                                    wire:model="startDate"
                                    class="ais-input ais-input-with-icon cursor-pointer"
                                />
                            </div>
                        </div>

                        <div class="ais-form-group">
                            <label class="ais-label">End Date</label>
                            <div class="ais-input-wrap">
                                <div class="ais-input-icon">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input
                                    type="date"
                                    wire:model="endDate"
                                    class="ais-input ais-input-with-icon cursor-pointer"
                                />
                            </div>
                        </div>
                    </div>

                    {{-- Quick Horizon Presets --}}
                    <div class="ais-preset-buttons">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 mr-1">Quick Horizon:</span>
                        <button type="button" wire:click="setDatePreset(30)" class="ais-preset-btn">Next 30 Days</button>
                        <button type="button" wire:click="setDatePreset(60)" class="ais-preset-btn">Next 60 Days (Default)</button>
                        <button type="button" wire:click="setDatePreset(90)" class="ais-preset-btn">Next 90 Days</button>
                        <button type="button" wire:click="setDatePreset(180)" class="ais-preset-btn">Next 6 Months</button>
                    </div>
                </div>

                {{-- Starlite Timetable Intelligence Preset Banner (Shown when Starlite is active) --}}
                @if($selectedOperator === 'Starlite')
                    <div class="ais-starlite-banner">
                        <div class="ais-banner-flex">
                            <div class="ais-banner-icon">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>

                            <div class="ais-banner-body">
                                <h3 class="ais-banner-title">
                                    Starlite Intelligence Engine Armed & Ready
                                </h3>
                                <p class="ais-banner-desc">
                                    Auto-synchronizes the complete Starlite fleet, multi-port routes with complex odd/even and hourly recurring departures, official June 2026 passenger accommodation fare matrices, and rolling cargo tariffs.
                                </p>

                                <div class="ais-banner-chips">
                                    <span class="ais-banner-chip">
                                        🚢 22+ Vessels (Annapolis, Saga, Archer...)
                                    </span>
                                    <span class="ais-banner-chip">
                                        🗺️ 19 Multi-Port Route Pairs
                                    </span>
                                    <span class="ais-banner-chip">
                                        💰 June 2026 Passenger Tariff
                                    </span>
                                    <span class="ais-banner-chip">
                                        🚗 Vehicle Cargo Rates
                                    </span>
                                    <span class="ais-banner-chip" style="color: #d97706;">
                                        📁 Default: starlite_schedules/VESSEL ROUTE.xlsx
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- 2GO Timetable Intelligence Preset Banner (Shown when 2GO is active) --}}
                @if($selectedOperator === '2GO')
                    <div class="ais-starlite-banner" style="border-color: rgba(245, 158, 11, 0.4); background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(15, 23, 42, 0.6) 100%);">
                        <div class="ais-banner-flex">
                            <div class="ais-banner-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #020617;">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>

                            <div class="ais-banner-body">
                                <h3 class="ais-banner-title" style="color: #fbbf24;">
                                    2GO Travel Intelligence Engine Armed & Ready
                                </h3>
                                <p class="ais-banner-desc">
                                    Auto-synchronizes 2GO flagship fleet (Maligaya, Masagana, St. Michael, St. Francis Xavier, Masigla), all 44 multi-sheet routes, and passenger cabin tariffs (Stateroom, Suite, Business Class 2/4/6/8, Megavalue, Tourist, Supervalue).
                                </p>

                                <div class="ais-banner-chips">
                                    <span class="ais-banner-chip">
                                        🚢 11 Fleet Vessels (MLG, MAS, SMA, SFX, MSN...)
                                    </span>
                                    <span class="ais-banner-chip">
                                        🗺️ 44 Nationwide Routes
                                    </span>
                                    <span class="ais-banner-chip">
                                        💰 Official 2GO Cabin Tariff Matrix
                                    </span>
                                    <span class="ais-banner-chip" style="color: #f59e0b;">
                                        📁 Default: 2go_schedules/2GO_TIMETABLE.xlsx
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- File Upload Dropzone --}}
                <div class="ais-card">
                    <div class="ais-card-header">
                        <div class="ais-card-title-group">
                            <span class="ais-step-number">3</span>
                            <div>
                                <h2 class="ais-card-title">
                                    Spreadsheet Upload
                                    @if($selectedOperator === 'Starlite' || $selectedOperator === '2GO')
                                        <span class="text-xs font-normal text-amber-500">(Optional — Master file pre-loaded)</span>
                                    @else
                                        <span class="text-xs font-normal text-emerald-500">(Required for {{ $selectedOperator === 'custom' ? 'Custom' : $selectedOperator }})</span>
                                    @endif
                                </h2>
                                <p class="ais-card-subtitle">
                                    Drag and drop an Excel (.xlsx) or CSV file. If no file is uploaded for {{ $selectedOperator }}, the system automatically uses the master timetable repository.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        x-data="{ isDragging: false }"
                        x-on:dragover.prevent="isDragging = true"
                        x-on:dragleave.prevent="isDragging = false"
                        x-on:drop.prevent="isDragging = false"
                        class="ais-dropzone"
                        :class="isDragging ? 'is-dragover' : ''"
                    >
                        <input
                            type="file"
                            wire:model="uploadedFile"
                            accept=".xlsx,.csv"
                            class="ais-dropzone-input"
                        />

                        @if($uploadedFile)
                            <div class="ais-file-badge">
                                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $uploadedFile->getClientOriginalName() }}</div>
                                    <div class="text-[11px] opacity-80 font-normal">Ready to process ({{ number_format($uploadedFile->getSize() / 1024, 1) }} KB)</div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400">Click or drag a new file to replace</p>
                        @else
                            <div class="ais-dropzone-icon">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <p class="ais-dropzone-title">
                                <span>Choose a spreadsheet</span> or drag and drop here
                            </p>
                            <p class="ais-dropzone-sub">
                                Microsoft Excel (.xlsx) or CSV format up to 20MB
                            </p>
                        @endif

                        <div wire:loading wire:target="uploadedFile" class="text-xs font-bold text-amber-500 animate-pulse mt-1">
                            Uploading spreadsheet to server...
                        </div>
                    </div>
                </div>

                {{-- Action Bar --}}
                <div class="ais-action-bar">
                    <button
                        wire:click="runImport"
                        wire:loading.attr="disabled"
                        type="button"
                        class="ais-primary-btn"
                    >
                        <span wire:loading.remove wire:target="runImport" class="flex items-center gap-2">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            @if($selectedOperator === 'Starlite' && ! $uploadedFile)
                                Execute Starlite Schedule & Tariff Sync
                            @else
                                Ingest & Import Schedule Spreadsheet
                            @endif
                        </span>

                        <span wire:loading wire:target="runImport" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing Timetables & Expanding Horizon...
                        </span>
                    </button>
                </div>
            </div>

            {{-- Right Column: Live Results & Operator Isolation Guarantee --}}
            <div class="space-y-6">

                {{-- Live Import Result Summary Card --}}
                @if($importSummary)
                    <div class="ais-result-panel">
                        <div class="ais-result-header">
                            <div class="ais-result-icon">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="ais-result-title">Import Execution Report</h3>
                                <p class="ais-result-timestamp">{{ $importSummary['timestamp'] }}</p>
                            </div>
                        </div>

                        <div class="ais-result-stat-grid">
                            @if(isset($importSummary['schedules_count']))
                                <div class="ais-result-stat-box col-span-2">
                                    <span class="ais-result-stat-label">Schedules Generated</span>
                                    <span class="ais-result-stat-value highlight">
                                        {{ number_format($importSummary['schedules_count']) }} trips
                                    </span>
                                </div>
                            @endif

                            @if(isset($importSummary['routes_count']))
                                <div class="ais-result-stat-box">
                                    <span class="ais-result-stat-label">Routes</span>
                                    <span class="ais-result-stat-value">
                                        {{ $importSummary['routes_count'] }}
                                    </span>
                                </div>
                            @endif

                            @if(isset($importSummary['vessels_count']))
                                <div class="ais-result-stat-box">
                                    <span class="ais-result-stat-label">Vessels</span>
                                    <span class="ais-result-stat-value">
                                        {{ $importSummary['vessels_count'] }}
                                    </span>
                                </div>
                            @endif

                            @if(isset($importSummary['accommodations_count']))
                                <div class="ais-result-stat-box col-span-2">
                                    <span class="ais-result-stat-label">Accommodation Tiers</span>
                                    <span class="ais-result-stat-value">
                                        {{ number_format($importSummary['accommodations_count']) }} tiers
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="space-y-2 text-xs border-t border-gray-100 dark:border-gray-800 pt-3 mb-4">
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Target Operator:</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $importSummary['operator'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500 dark:text-gray-400">Date Horizon:</span>
                                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $importSummary['start_date'] }} → {{ $importSummary['end_date'] }}</span>
                            </div>
                        </div>

                        <a
                            href="{{ \App\Filament\Resources\FerryRouteResource::getUrl() }}"
                            class="ais-result-link-btn"
                        >
                            <span>Inspect Routes & Schedules Table</span>
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                @endif

                {{-- Operator Isolation & Safety Guarantee --}}
                <div class="ais-security-card">
                    <div class="ais-security-header">
                        <div class="ais-security-icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="ais-security-title">Operator Isolation Guarantee</h3>
                    </div>

                    <p class="ais-security-desc">
                        Every ingestion process is strictly partitioned by operator. Syncing Starlite schedules will update only Starlite routes, vessels, and accommodations, preserving all 2GO, PAL, Cebu Pacific, and AirAsia data intact without cross-contamination.
                    </p>

                    <ul class="ais-feature-bullets">
                        <li class="ais-feature-bullet">
                            <span class="ais-bullet-check">
                                <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <span>Auto-provisions missing operator records</span>
                        </li>
                        <li class="ais-feature-bullet">
                            <span class="ais-bullet-check">
                                <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <span>Resolves 35+ IATA and Ferry shortcodes</span>
                        </li>
                        <li class="ais-feature-bullet">
                            <span class="ais-bullet-check">
                                <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <span>Attaches vehicle rolling cargo tariffs</span>
                        </li>
                        <li class="ais-feature-bullet">
                            <span class="ais-bullet-check">
                                <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            <span>Safe transaction rollbacks on failure</span>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</x-filament-panels::page>
