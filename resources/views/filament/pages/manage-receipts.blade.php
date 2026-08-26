<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/admin-proofs.css') }}">
    <style>
        .receipt-card {
            transition: all 0.2s ease;
        }
        .receipt-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
        .receipt-badge-confirmed {
            background-color: rgb(34 197 94 / 0.12);
            color: rgb(22 163 74);
            border: 1px solid rgb(34 197 94 / 0.25);
        }
        .dark .receipt-badge-confirmed {
            background-color: rgb(34 197 94 / 0.2);
            color: rgb(74 222 128);
            border-color: rgb(34 197 94 / 0.4);
        }
        .receipt-badge-rebooked {
            background-color: rgb(168 85 247 / 0.12);
            color: rgb(147 51 234);
            border: 1px solid rgb(168 85 247 / 0.25);
        }
        .dark .receipt-badge-rebooked {
            background-color: rgb(168 85 247 / 0.2);
            color: rgb(192 132 252);
            border-color: rgb(168 85 247 / 0.4);
        }
        .receipt-badge-refunded {
            background-color: rgb(14 165 233 / 0.12);
            color: rgb(2 132 199);
            border: 1px solid rgb(14 165 233 / 0.25);
        }
        .dark .receipt-badge-refunded {
            background-color: rgb(14 165 233 / 0.2);
            color: rgb(56 189 248);
            border-color: rgb(14 165 233 / 0.4);
        }
        .receipt-badge-pending {
            background-color: rgb(245 158 11 / 0.12);
            color: rgb(217 119 6);
            border: 1px solid rgb(245 158 11 / 0.25);
        }
        .dark .receipt-badge-pending {
            background-color: rgb(245 158 11 / 0.2);
            color: rgb(251 191 36);
            border-color: rgb(245 158 11 / 0.4);
        }
        .receipt-badge-default {
            background-color: rgb(107 114 128 / 0.12);
            color: rgb(75 85 99);
            border: 1px solid rgb(107 114 128 / 0.25);
        }
    </style>

    <div class="space-y-6">
        <!-- Filter Tabs & Controls Header Card -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900 space-y-4">
            <!-- Category Tabs -->
            <div
                x-data="{ activeFilter: @entangle('typeFilter').live }"
                class="proof-filter-tabs"
            >
                <button
                    type="button"
                    @click="activeFilter = 'all'; $wire.setTypeFilter('all')"
                    :class="{ 'is-active': activeFilter === 'all' }"
                    class="proof-filter-btn {{ $typeFilter === 'all' ? 'is-active' : '' }}"
                    data-filter="all"
                >
                    <span>All Receipts</span>
                    <span class="proof-filter-badge">
                        {{ $this->counts['all'] }}
                    </span>
                </button>

                <button
                    type="button"
                    @click="activeFilter = 'confirmed'; $wire.setTypeFilter('confirmed')"
                    :class="{ 'is-active': activeFilter === 'confirmed' }"
                    class="proof-filter-btn {{ $typeFilter === 'confirmed' ? 'is-active' : '' }}"
                    data-filter="confirmed"
                >
                    <span class="proof-filter-dot"></span>
                    <span>Confirmed E-Receipts</span>
                    <span class="proof-filter-badge">
                        {{ $this->counts['confirmed'] }}
                    </span>
                </button>

                <button
                    type="button"
                    @click="activeFilter = 'rebooked'; $wire.setTypeFilter('rebooked')"
                    :class="{ 'is-active': activeFilter === 'rebooked' }"
                    class="proof-filter-btn {{ $typeFilter === 'rebooked' ? 'is-active' : '' }}"
                    data-filter="rebooked"
                >
                    <span class="proof-filter-dot"></span>
                    <span>Rebooked</span>
                    <span class="proof-filter-badge">
                        {{ $this->counts['rebooked'] }}
                    </span>
                </button>

                <button
                    type="button"
                    @click="activeFilter = 'refunded'; $wire.setTypeFilter('refunded')"
                    :class="{ 'is-active': activeFilter === 'refunded' }"
                    class="proof-filter-btn {{ $typeFilter === 'refunded' ? 'is-active' : '' }}"
                    data-filter="refunded"
                >
                    <span class="proof-filter-dot"></span>
                    <span>Refund Acknowledgements</span>
                    <span class="proof-filter-badge">
                        {{ $this->counts['refunded'] }}
                    </span>
                </button>
            </div>

            <!-- Search, Date Range & Bulk Actions Bar -->
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Search Input -->
                    <div class="relative min-w-[240px]">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search Transaction #, Name, Ref..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500"
                        />
                        @if($search)
                            <button
                                type="button"
                                wire:click="$set('search', '')"
                                class="absolute right-2.5 top-1.5 text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                            >
                                &times;
                            </button>
                        @endif
                    </div>

                    <!-- Select All Checkbox -->
                    <label class="inline-flex items-center gap-2 text-xs font-medium text-gray-700 dark:text-gray-200">
                        <input
                            type="checkbox"
                            wire:model.live="selectAll"
                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800"
                        />
                        <span>Select all ({{ count($this->selectedReceipts) }})</span>
                    </label>

                    <!-- Date Filter Select -->
                    <select
                        wire:model.live="dateFilter"
                        class="rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="all">All time</option>
                        <option value="today">Today</option>
                        <option value="week">This week</option>
                        <option value="month">This month</option>
                        <option value="year">This year</option>
                        <option value="custom">Custom range</option>
                    </select>

                    <!-- Custom Date Range Inputs -->
                    @if ($dateFilter === 'custom')
                        <div class="flex items-center gap-1.5">
                            <input
                                type="date"
                                wire:model.live="customDateStart"
                                class="rounded-lg border border-gray-300 bg-white px-2 py-1 text-xs text-gray-900 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                            <span class="text-xs text-gray-500">to</span>
                            <input
                                type="date"
                                wire:model.live="customDateEnd"
                                class="rounded-lg border border-gray-300 bg-white px-2 py-1 text-xs text-gray-900 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                            />
                        </div>
                    @endif
                </div>

                <!-- Action Buttons: Download ZIP -->
                <div class="flex flex-wrap items-center gap-2">
                    {{ $this->downloadAllZipAction }}
                    {{ $this->downloadSelectedZipAction }}
                </div>
            </div>
        </div>

        <!-- Receipts Grid -->
        @if ($this->receipts->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-900">
                <div class="rounded-full bg-primary-50 p-3 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400 mb-3">
                    <x-filament::icon icon="heroicon-o-document-text" class="h-8 w-8" />
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">No official receipts found</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    @if ($search || $dateFilter !== 'all' || $typeFilter !== 'all')
                        Try adjusting your filters or search terms.
                    @else
                        Official receipts will appear here automatically when bookings are created and confirmed.
                    @endif
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-4">
                @foreach ($this->receipts as $item)
                    <div
                        wire:key="receipt-card-{{ $item->composite_id }}"
                        class="receipt-card flex min-w-0 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900"
                    >
                        <!-- Card Header -->
                        <div class="flex items-center justify-between border-b border-gray-200 px-3.5 py-2.5 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/60">
                            <label class="inline-flex items-center gap-2 text-xs font-medium text-gray-700 dark:text-gray-200">
                                <input
                                    type="checkbox"
                                    wire:model.live="selectedReceipts"
                                    value="{{ $item->composite_id }}"
                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800"
                                />
                                <span class="font-mono font-bold text-gray-900 dark:text-white text-xs">{{ $item->transaction_number }}</span>
                            </label>

                            <span class="shrink-0 rounded-md px-2 py-0.5 text-[10px] font-bold uppercase {{ $item->status_class }}">
                                {{ $item->status_badge }}
                            </span>
                        </div>

                        <!-- Card Hero Visual Section -->
                        <div class="p-4 bg-gradient-to-b from-gray-50/50 to-white dark:from-gray-800/40 dark:to-gray-900 border-b border-gray-100 dark:border-gray-800/80">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-emerald-500/10 text-emerald-600 dark:bg-emerald-400/15 dark:text-emerald-400 shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-xs font-bold text-gray-900 dark:text-white block truncate" title="{{ $item->client_name }}">
                                            {{ $item->client_name }}
                                        </span>
                                        <span class="text-[11px] text-gray-500 dark:text-gray-400 block truncate" title="{{ $item->client_email }}">
                                            {{ $item->client_email }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400 block">
                                        ₱{{ number_format($item->amount, 2) }}
                                    </span>
                                    <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wider block">
                                        {{ $item->type_label }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Body Details -->
                        <div class="flex flex-1 flex-col gap-1.5 p-3.5 text-xs text-gray-600 dark:text-gray-300">
                            <div class="flex items-center justify-between gap-1 text-[11px]">
                                <span class="text-gray-400 dark:text-gray-500">Route:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200 truncate" title="{{ $item->route }}">
                                    {{ $item->route }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-1 text-[11px]">
                                <span class="text-gray-400 dark:text-gray-500">Operator:</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300 truncate">
                                    {{ $item->operator_name }} ({{ ucfirst($item->mode) }})
                                </span>
                            </div>

                            <div class="flex items-center justify-between gap-1 text-[11px]">
                                <span class="text-gray-400 dark:text-gray-500">Passengers:</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">
                                    {{ $item->passenger_count }} pax
                                </span>
                            </div>

                            @if($item->payment_reference)
                                <div class="flex items-center justify-between gap-1 text-[11px]">
                                    <span class="text-gray-400 dark:text-gray-500">Ref / Method:</span>
                                    <span class="font-mono font-semibold text-emerald-700 dark:text-emerald-400 truncate">
                                        {{ $item->payment_reference }} ({{ $item->payment_method }})
                                    </span>
                                </div>
                            @endif

                            <div class="flex items-center justify-between gap-1 text-[10px] text-gray-400 dark:text-gray-500 pt-1 border-t border-gray-100 dark:border-gray-800 mt-0.5">
                                <span>Issued:</span>
                                <span>{{ $item->issued_at?->format('M d, Y g:i A') ?? '—' }}</span>
                            </div>
                        </div>

                        <!-- Card Actions Footer -->
                        <div class="mt-auto flex items-center gap-1.5 border-t border-gray-200 p-2.5 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/40">
                            @if($item->view_url)
                                <x-filament::button
                                    tag="a"
                                    href="{{ $item->view_url }}"
                                    color="gray"
                                    size="xs"
                                    class="flex-1"
                                >
                                    Booking
                                </x-filament::button>
                            @endif

                            <x-filament::button
                                tag="a"
                                href="{{ $item->receipt_view_url }}"
                                target="_blank"
                                color="success"
                                size="xs"
                                class="flex-1"
                                icon="heroicon-o-arrow-top-right-on-square"
                            >
                                View PDF
                            </x-filament::button>

                            <x-filament::button
                                tag="a"
                                href="{{ $item->receipt_download_url }}"
                                color="primary"
                                size="xs"
                                class="flex-1"
                                icon="heroicon-o-arrow-down-tray"
                            >
                                Download
                            </x-filament::button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
