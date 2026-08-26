<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/admin-proofs.css') }}">
    <style>
        /* ── Receipt-specific badge colours ── */
        .receipt-status-paid {
            background-color: rgb(34 197 94 / 0.12);
            color: rgb(22 163 74);
            border: 1px solid rgb(34 197 94 / 0.3);
        }
        .dark .receipt-status-paid {
            background-color: rgb(34 197 94 / 0.18);
            color: rgb(74 222 128);
            border-color: rgb(34 197 94 / 0.4);
        }
        .receipt-status-pending {
            background-color: rgb(245 158 11 / 0.12);
            color: rgb(180 83 9);
            border: 1px solid rgb(245 158 11 / 0.3);
        }
        .dark .receipt-status-pending {
            background-color: rgb(245 158 11 / 0.18);
            color: rgb(251 191 36);
            border-color: rgb(245 158 11 / 0.4);
        }
        .receipt-status-rebooked {
            background-color: rgb(168 85 247 / 0.12);
            color: rgb(109 40 217);
            border: 1px solid rgb(168 85 247 / 0.3);
        }
        .dark .receipt-status-rebooked {
            background-color: rgb(168 85 247 / 0.18);
            color: rgb(192 132 252);
            border-color: rgb(168 85 247 / 0.4);
        }
        .receipt-status-refunded {
            background-color: rgb(14 165 233 / 0.12);
            color: rgb(2 132 199);
            border: 1px solid rgb(14 165 233 / 0.3);
        }
        .dark .receipt-status-refunded {
            background-color: rgb(14 165 233 / 0.18);
            color: rgb(56 189 248);
            border-color: rgb(14 165 233 / 0.4);
        }
        .receipt-status-default {
            background-color: rgb(107 114 128 / 0.1);
            color: rgb(75 85 99);
            border: 1px solid rgb(107 114 128 / 0.2);
        }

        /* ── Receipt card ── */
        .receipt-card {
            transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
        }
        .receipt-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px -6px rgba(0, 0, 0, 0.12), 0 6px 12px -4px rgba(0, 0, 0, 0.08);
        }

        /* ── Document banner (replaces the proof-image area) ── */
        .receipt-banner {
            position: relative;
            height: 7.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            overflow: hidden;
        }
        /* Confirmed */
        .receipt-banner--confirmed {
            background: linear-gradient(135deg, #064e3b 0%, #059669 60%, #34d399 100%);
        }
        /* Rebooked */
        .receipt-banner--rebooked {
            background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 60%, #a78bfa 100%);
        }
        /* Refunded / Pending */
        .receipt-banner--refunded {
            background: linear-gradient(135deg, #0c4a6e 0%, #0284c7 60%, #38bdf8 100%);
        }
        .receipt-banner--pending {
            background: linear-gradient(135deg, #78350f 0%, #d97706 60%, #fcd34d 100%);
        }
        .receipt-banner--default {
            background: linear-gradient(135deg, #1e293b 0%, #334155 60%, #64748b 100%);
        }

        .receipt-banner__icon {
            width: 2.5rem;
            height: 2.5rem;
            opacity: 0.95;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.25));
            color: #ffffff;
        }
        .receipt-banner__label {
            font-size: 0.6rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.85);
        }
        /* Subtle watermark lines in banner */
        .receipt-banner::after {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(
                -55deg,
                rgba(255,255,255,0.04) 0px,
                rgba(255,255,255,0.04) 1px,
                transparent 1px,
                transparent 14px
            );
        }
    </style>

    <div class="space-y-6">
        <!-- Filter Tabs & Controls -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900 space-y-4">
            <!-- Category Tabs -->
            <div
                x-data="{ activeFilter: @entangle('typeFilter').live }"
                class="proof-filter-tabs"
            >
                <button type="button"
                    @click="activeFilter = 'all'; $wire.setTypeFilter('all')"
                    :class="{ 'is-active': activeFilter === 'all' }"
                    class="proof-filter-btn {{ $typeFilter === 'all' ? 'is-active' : '' }}"
                    data-filter="all"
                >
                    <span>All Receipts</span>
                    <span class="proof-filter-badge">{{ $this->counts['all'] }}</span>
                </button>

                <button type="button"
                    @click="activeFilter = 'confirmed'; $wire.setTypeFilter('confirmed')"
                    :class="{ 'is-active': activeFilter === 'confirmed' }"
                    class="proof-filter-btn {{ $typeFilter === 'confirmed' ? 'is-active' : '' }}"
                    data-filter="confirmed"
                >
                    <span class="proof-filter-dot"></span>
                    <span>Confirmed</span>
                    <span class="proof-filter-badge">{{ $this->counts['confirmed'] }}</span>
                </button>

                <button type="button"
                    @click="activeFilter = 'rebooked'; $wire.setTypeFilter('rebooked')"
                    :class="{ 'is-active': activeFilter === 'rebooked' }"
                    class="proof-filter-btn {{ $typeFilter === 'rebooked' ? 'is-active' : '' }}"
                    data-filter="rebooked"
                >
                    <span class="proof-filter-dot"></span>
                    <span>Rebooked</span>
                    <span class="proof-filter-badge">{{ $this->counts['rebooked'] }}</span>
                </button>

                <button type="button"
                    @click="activeFilter = 'refunded'; $wire.setTypeFilter('refunded')"
                    :class="{ 'is-active': activeFilter === 'refunded' }"
                    class="proof-filter-btn {{ $typeFilter === 'refunded' ? 'is-active' : '' }}"
                    data-filter="refunded"
                >
                    <span class="proof-filter-dot"></span>
                    <span>Refund Acknowledgements</span>
                    <span class="proof-filter-badge">{{ $this->counts['refunded'] }}</span>
                </button>
            </div>

            <!-- Search · Select All · Date · ZIP Actions -->
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Search -->
                    <div class="relative min-w-[220px]">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search Tx #, Name, Ref…"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500"
                        />
                        @if($search)
                            <button type="button" wire:click="$set('search', '')"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                &times;
                            </button>
                        @endif
                    </div>

                    <!-- Select All -->
                    <label class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 cursor-pointer">
                        <input type="checkbox" wire:model.live="selectAll"
                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800" />
                        Select all
                        @if(count($this->selectedReceipts))
                            <span class="ml-0.5 rounded-full bg-primary-100 px-1.5 py-0.5 text-[10px] font-bold text-primary-700 dark:bg-primary-900/50 dark:text-primary-300">
                                {{ count($this->selectedReceipts) }}
                            </span>
                        @endif
                    </label>

                    <!-- Date Filter -->
                    <select wire:model.live="dateFilter"
                        class="rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="all">All time</option>
                        <option value="today">Today</option>
                        <option value="week">This week</option>
                        <option value="month">This month</option>
                        <option value="year">This year</option>
                        <option value="custom">Custom range</option>
                    </select>

                    @if ($dateFilter === 'custom')
                        <div class="flex items-center gap-1.5">
                            <input type="date" wire:model.live="customDateStart"
                                class="rounded-lg border border-gray-300 bg-white px-2 py-1 text-xs shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            <span class="text-xs text-gray-400">to</span>
                            <input type="date" wire:model.live="customDateEnd"
                                class="rounded-lg border border-gray-300 bg-white px-2 py-1 text-xs shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                        </div>
                    @endif
                </div>

                <!-- ZIP Actions -->
                <div class="flex flex-wrap items-center gap-2">
                    {{ $this->downloadAllZipAction }}
                    {{ $this->downloadSelectedZipAction }}
                </div>
            </div>
        </div>

        <!-- Grid -->
        @if ($this->receipts->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-white p-14 text-center dark:border-gray-700 dark:bg-gray-900">
                <div class="mb-3 rounded-full bg-emerald-50 p-3.5 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">No receipts found</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @if ($search || $dateFilter !== 'all' || $typeFilter !== 'all')
                        Try adjusting your filters or search terms.
                    @else
                        E-receipts will appear here automatically when bookings are confirmed.
                    @endif
                </p>
            </div>
        @else
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach ($this->receipts as $item)
                    @php
                        $bannerClass = match($item->type) {
                            'confirmed' => 'receipt-banner--confirmed',
                            'rebooked'  => 'receipt-banner--rebooked',
                            'refunded'  => 'receipt-banner--refunded',
                            default     => ($item->status_badge === 'Pending' ? 'receipt-banner--pending' : 'receipt-banner--default'),
                        };
                        $statusClass = match($item->type) {
                            'confirmed' => 'receipt-status-paid',
                            'rebooked'  => 'receipt-status-rebooked',
                            'refunded'  => 'receipt-status-refunded',
                            default     => 'receipt-status-default',
                        };
                        if ($item->status_badge === 'Pending') $statusClass = 'receipt-status-pending';
                        if ($item->status_badge === 'Paid')    $statusClass = 'receipt-status-paid';
                    @endphp

                    <div
                        wire:key="receipt-card-{{ $item->composite_id }}"
                        class="receipt-card flex min-w-0 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900"
                    >
                        <!-- Checkbox + Status badge row -->
                        <div class="flex items-center justify-between px-3 py-2 bg-gray-50/80 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-700">
                            <label class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-700 dark:text-gray-200 cursor-pointer">
                                <input
                                    type="checkbox"
                                    wire:model.live="selectedReceipts"
                                    value="{{ $item->composite_id }}"
                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800"
                                />
                                Select
                            </label>

                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $statusClass }}">
                                {{ $item->status_badge }}
                            </span>
                        </div>

                        <!-- Coloured Banner -->
                        <div class="receipt-banner {{ $bannerClass }}">
                            <!-- receipt / document SVG icon -->
                            <svg class="receipt-banner__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="receipt-banner__label">{{ $item->type_label }}</span>
                        </div>

                        <!-- Card Body -->
                        <div class="flex flex-1 flex-col gap-1 p-3 text-xs">
                            <!-- Transaction # -->
                            <p class="font-mono font-bold text-gray-900 dark:text-white truncate" title="{{ $item->display_name }}">
                                {{ $item->display_name }}
                            </p>

                            <!-- Client -->
                            <p class="truncate font-semibold text-gray-800 dark:text-gray-200" title="{{ $item->client_name }}">
                                👤 {{ $item->client_name }}
                            </p>
                            <p class="truncate text-gray-500 dark:text-gray-400" title="{{ $item->client_email }}">
                                ✉️ {{ $item->client_email }}
                            </p>

                            <!-- Route -->
                            <p class="truncate text-gray-500 dark:text-gray-400">
                                🚢 {{ $item->route }}
                            </p>

                            <!-- Operator -->
                            <p class="truncate text-gray-500 dark:text-gray-400">
                                🏢 {{ $item->operator_name }} · {{ $item->passenger_count }} pax
                            </p>

                            <!-- Amount -->
                            <p class="font-bold text-gray-900 dark:text-white mt-0.5">
                                ₱{{ number_format($item->amount, 2) }}
                            </p>

                            @if($item->payment_reference)
                                <p class="truncate font-semibold text-emerald-700 dark:text-emerald-400" title="Ref: {{ $item->payment_reference }}">
                                    Ref: {{ $item->payment_reference }}
                                </p>
                            @endif

                            <!-- Issued Date -->
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-auto pt-1 border-t border-gray-100 dark:border-gray-800">
                                🕒 {{ $item->issued_at?->format('M d, Y g:i A') ?? '—' }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-auto flex gap-1.5 border-t border-gray-200 p-2 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/30">
                            @if($item->view_url)
                                <x-filament::button
                                    tag="a"
                                    href="{{ $item->view_url }}"
                                    color="gray"
                                    size="xs"
                                    class="flex-1"
                                >
                                    View
                                </x-filament::button>
                            @endif

                            <x-filament::button
                                tag="a"
                                href="{{ $item->receipt_view_url }}"
                                target="_blank"
                                color="success"
                                size="xs"
                                class="flex-1"
                                icon="heroicon-o-document-text"
                            >
                                Receipt PDF
                            </x-filament::button>

                            <x-filament::button
                                tag="a"
                                href="{{ $item->receipt_download_url }}"
                                color="primary"
                                size="xs"
                                class="flex-shrink-0"
                                icon="heroicon-o-arrow-down-tray"
                            >
                            </x-filament::button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
