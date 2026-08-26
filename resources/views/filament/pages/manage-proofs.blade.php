<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/admin-proofs.css') }}">

    <div class="space-y-6">
        <!-- Retention Settings & Backups Card -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900 space-y-4">
            <form wire:submit="saveSettings" class="space-y-4">
                {{ $this->form }}

                <div class="flex flex-wrap items-center gap-3">
                    <x-filament::button type="submit">
                        Save settings
                    </x-filament::button>

                    {{ $this->createBackupAction }}
                </div>
            </form>

            <!-- Saved Backup Archives List -->
            @if($this->archives->isNotEmpty())
                <div class="border-t border-gray-200 dark:border-gray-700/80 pt-5 mt-2">
                    <div class="flex items-center justify-between mb-3.5">
                        <div class="flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-amber-500/10 text-amber-600 dark:bg-amber-400/15 dark:text-amber-400 text-xs">
                                📦
                            </span>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-900 dark:text-gray-100">
                                Saved Backup ZIP Archives
                            </h4>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary-100 text-primary-700 dark:bg-primary-900/50 dark:text-primary-300">
                                {{ $this->archives->count() }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3 max-h-60 overflow-y-auto pr-1">
                        @foreach($this->archives as $archive)
                            <div class="flex items-center justify-between gap-3 p-3 rounded-lg bg-gray-50/80 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition shadow-xs">
                                <div class="min-w-0 flex-1 mr-1">
                                    <div class="font-mono text-xs font-semibold text-gray-900 dark:text-white truncate" title="{{ $archive->filename }}">
                                        {{ $archive->filename }}
                                    </div>
                                    <div class="mt-1 flex items-center gap-1.5 text-[10px] text-gray-500 dark:text-gray-400">
                                        <span>{{ $archive->created_at->format('M d, Y h:i A') }}</span>
                                        <span>&bull;</span>
                                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $archive->formatted_size }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <x-filament::button
                                        tag="a"
                                        href="{{ $archive->download_url }}"
                                        color="primary"
                                        size="xs"
                                        icon="heroicon-o-arrow-down-tray"
                                    >
                                        Download
                                    </x-filament::button>

                                    {{ ($this->deleteArchiveAction)(['filename' => $archive->filename]) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Filter Tabs & Controls -->
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
                    <span>All Proofs</span>
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
                    <span>Confirmed</span>
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
                    <span>Refunded / Cancelled</span>
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
                            placeholder="Search Transaction #, Name..."
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
                        Select all ({{ count($selectedTransactions) }} selected)
                    </label>

                    <!-- Date Filter Dropdown -->
                    <div class="flex items-center gap-2 border-l border-gray-200 pl-3 dark:border-gray-700">
                        <select wire:model.live="dateFilter" class="rounded-md border-gray-300 py-1.5 text-xs shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option value="all">All time</option>
                            <option value="today">This day</option>
                            <option value="week">This week</option>
                            <option value="month">This month</option>
                            <option value="year">This year</option>
                            <option value="custom">Custom range</option>
                        </select>
                        
                        @if($dateFilter === 'custom')
                            <div class="flex items-center gap-1">
                                <input type="date" wire:model.live="customDateStart" class="w-28 rounded-md border-gray-300 py-1.5 text-xs shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                                <span class="text-gray-500">-</span>
                                <input type="date" wire:model.live="customDateEnd" class="w-28 rounded-md border-gray-300 py-1.5 text-xs shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" />
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    {{ $this->downloadAllZipAction }}
                    {{ $this->downloadSelectedZipAction }}
                    {{ $this->deleteSelectedAction }}
                </div>
            </div>
        </div>

        @if ($this->proofs->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center dark:border-gray-600 dark:bg-gray-900">
                <p class="text-base font-medium text-gray-900 dark:text-white">No proofs or receipts found</p>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Try changing your filter criteria or search keyword.</p>
            </div>
        @else
            <div class="proofs-grid">
                @foreach ($this->proofs as $item)
                    <div
                        wire:key="proof-card-{{ $item->composite_id }}"
                        class="flex min-w-0 flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900"
                    >
                        <div class="flex items-center justify-between border-b border-gray-200 px-3 py-2 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                            <label class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-700 dark:text-gray-200">
                                <input
                                    type="checkbox"
                                    wire:model.live="selectedTransactions"
                                    value="{{ $item->composite_id }}"
                                    class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-800"
                                />
                                Select
                            </label>

                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $item->status_class }}">
                                {{ $item->status_badge }}
                            </span>
                        </div>

                        <!-- Image Preview Area -->
                        <div class="proof-image bg-gray-100 dark:bg-gray-800 relative group">
                            @if ($item->has_proof && $item->proof_url)
                                <a href="{{ $item->proof_url }}" target="_blank" class="block w-full h-full">
                                    <img
                                        src="{{ $item->proof_url }}"
                                        alt="Proof for {{ $item->display_name }}"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                    />
                                    <div class="hidden h-36 w-full items-center justify-center bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-400">
                                        Proof image unavailable
                                    </div>
                                </a>
                            @else
                                <div class="h-36 w-full flex items-center justify-center bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-400">
                                    Proof image unavailable
                                </div>
                            @endif
                        </div>

                        <!-- Details Section -->
                        <div class="flex flex-1 flex-col gap-1 p-3 text-xs">
                            <div class="flex items-start justify-between gap-1">
                                <span class="font-bold text-gray-900 dark:text-white break-words" title="{{ $item->display_name }}">
                                    {{ $item->display_name }}
                                </span>
                            </div>

                            <p class="truncate font-semibold text-gray-800 dark:text-gray-200" title="{{ $item->client_name }}">
                                👤 {{ $item->client_name }}
                            </p>
                            <p class="truncate text-gray-500 dark:text-gray-400" title="{{ $item->client_email }}">
                                ✉️ {{ $item->client_email }}
                            </p>
                            <p class="truncate text-gray-500 dark:text-gray-400">
                                🚢 {{ $item->route }}
                            </p>
                            @if($item->payment_reference)
                                <p class="truncate font-semibold text-emerald-700 dark:text-emerald-400" title="Payment Ref: {{ $item->payment_reference }}">
                                    Ref: {{ $item->payment_reference }}
                                </p>
                            @endif
                            <p class="font-bold text-gray-900 dark:text-white mt-0.5">
                                ₱{{ number_format($item->amount, 2) }}
                            </p>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500">
                                🕒 {{ $item->updated_at?->format('M d, Y g:i A') ?? '—' }}
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-auto flex flex-col gap-1.5 border-t border-gray-200 p-2 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/30">
                            <div class="flex items-center gap-1.5">
                                @if($item->view_url)
                                    <x-filament::button
                                        tag="a"
                                        href="{{ $item->view_url }}"
                                        color="gray"
                                        size="xs"
                                        class="flex-1"
                                    >
                                        View Booking
                                    </x-filament::button>
                                @endif

                                @if ($item->has_proof && $item->proof_url)
                                    <x-filament::button
                                        tag="a"
                                        href="{{ $item->proof_url }}"
                                        download
                                        target="_blank"
                                        color="amber"
                                        size="xs"
                                        class="flex-1"
                                        icon="heroicon-o-arrow-down-tray"
                                    >
                                        Download
                                    </x-filament::button>
                                @endif

                                @if ($item->has_proof)
                                    <div class="flex shrink-0">
                                        {{ ($this->deleteProofAction)(['compositeId' => $item->composite_id]) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
