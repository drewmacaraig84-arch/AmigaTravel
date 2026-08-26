<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/admin-proofs.css') }}">

    <div class="space-y-6">
        <!-- Retention Settings & Safety Backup Grid -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-stretch">
            <!-- Left Card: Retention Settings Form (7 cols) -->
            <div class="md:col-span-7 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700/80 dark:bg-gray-900 flex flex-col justify-between">
                <form wire:submit="saveSettings" class="space-y-3">
                    {{ $this->form }}

                    <x-filament::button type="submit" size="sm">
                        Save settings
                    </x-filament::button>
                </form>
            </div>

            <!-- Right Card: Pre-Retention Backup Action (5 cols) -->
            <div class="md:col-span-5 rounded-xl border border-gray-200 bg-gray-50/50 p-5 shadow-sm dark:border-gray-700/80 dark:bg-gray-900/60 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        </span>
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">Pre-Retention Archive</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                        Automatic safety ZIP archives are generated 1 day before proofs hit the retention limit.
                    </p>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700/60 flex items-center">
                    {{ $this->createBackupAction }}
                </div>
            </div>
        </div>

        <!-- Pre-Retention Backups Archive List (if any exist) -->
        @if($this->archives->isNotEmpty())
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700/80 dark:bg-gray-900">
                <h4 class="text-xs font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                    Saved Pre-Retention ZIP Archives ({{ $this->archives->count() }})
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 max-h-48 overflow-y-auto">
                    @foreach($this->archives as $archive)
                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs">
                            <div class="truncate mr-2">
                                <div class="font-mono font-semibold text-gray-900 dark:text-white truncate" title="{{ $archive->filename }}">
                                    📦 {{ $archive->filename }}
                                </div>
                                <div class="text-[10px] text-gray-400">
                                    {{ $archive->created_at->format('M d, Y h:i A') }} &bull; {{ $archive->formatted_size }}
                                </div>
                            </div>
                            <a
                                href="{{ $archive->download_url }}"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-primary-50 text-primary-700 hover:bg-primary-100 dark:bg-primary-900/40 dark:text-primary-300 font-semibold text-[11px] shrink-0 transition"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                Download
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Filter Tabs & Controls Card -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700/80 dark:bg-gray-900 space-y-4">
            <!-- Category Tabs (Responsive Pill Buttons) -->
            <div class="proofs-tabs-container border-b border-gray-100 pb-4 dark:border-gray-800">
                <button
                    type="button"
                    wire:click="setTypeFilter('all')"
                    class="proof-tab {{ $typeFilter === 'all' ? 'proof-tab-all-active' : 'proof-tab-all-inactive' }}"
                >
                    <span>All Transactions</span>
                    <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $typeFilter === 'all' ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        {{ $this->counts['all'] }}
                    </span>
                </button>

                <button
                    type="button"
                    wire:click="setTypeFilter('confirmed')"
                    class="proof-tab {{ $typeFilter === 'confirmed' ? 'proof-tab-confirmed-active' : 'proof-tab-confirmed-inactive' }}"
                >
                    <span class="inline-block w-2 h-2 rounded-full {{ $typeFilter === 'confirmed' ? 'bg-white' : 'bg-emerald-500' }}"></span>
                    <span>Confirmed</span>
                    <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $typeFilter === 'confirmed' ? 'bg-white/20 text-white' : 'bg-emerald-100 dark:bg-emerald-900 text-emerald-800 dark:text-emerald-200' }}">
                        {{ $this->counts['confirmed'] }}
                    </span>
                </button>

                <button
                    type="button"
                    wire:click="setTypeFilter('rebooked')"
                    class="proof-tab {{ $typeFilter === 'rebooked' ? 'proof-tab-rebooked-active' : 'proof-tab-rebooked-inactive' }}"
                >
                    <span class="inline-block w-2 h-2 rounded-full {{ $typeFilter === 'rebooked' ? 'bg-white' : 'bg-purple-500' }}"></span>
                    <span>Rebooked</span>
                    <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $typeFilter === 'rebooked' ? 'bg-white/20 text-white' : 'bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200' }}">
                        {{ $this->counts['rebooked'] }}
                    </span>
                </button>

                <button
                    type="button"
                    wire:click="setTypeFilter('refunded')"
                    class="proof-tab {{ $typeFilter === 'refunded' ? 'proof-tab-refunded-active' : 'proof-tab-refunded-inactive' }}"
                >
                    <span class="inline-block w-2 h-2 rounded-full {{ $typeFilter === 'refunded' ? 'bg-white' : 'bg-sky-500' }}"></span>
                    <span>Refunded / Cancelled</span>
                    <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $typeFilter === 'refunded' ? 'bg-white/20 text-white' : 'bg-sky-100 dark:bg-sky-900 text-sky-800 dark:text-sky-200' }}">
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

                        <!-- Image Preview / Fallback Area -->
                        <div class="proof-image bg-gray-100 dark:bg-gray-800 relative group">
                            @if ($item->has_proof && $item->proof_url)
                                <img
                                    src="{{ $item->proof_url }}"
                                    alt="Proof for {{ $item->display_name }}"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                />
                                <div class="hidden h-36 w-full items-center justify-center bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-400">
                                    Proof image unavailable
                                </div>
                            @else
                                <div class="h-36 w-full flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-800 text-center p-3">
                                    <svg class="w-8 h-8 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span class="text-[11px] font-medium text-gray-500 dark:text-gray-400">Official E-Receipt Available</span>
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
                            <!-- Top row: View Booking & Receipt PDF -->
                            <div class="flex gap-1.5">
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

                                @if($item->receipt_download_url)
                                    <x-filament::button
                                        tag="a"
                                        href="{{ $item->receipt_download_url }}"
                                        target="_blank"
                                        color="success"
                                        size="xs"
                                        class="flex-1"
                                        icon="heroicon-o-document-text"
                                    >
                                        Receipt PDF
                                    </x-filament::button>
                                @endif
                            </div>

                            <!-- Bottom row: Download Proof & Delete -->
                            <div class="flex gap-1.5">
                                @if ($item->has_proof && $item->proof_url)
                                    <x-filament::button
                                        tag="a"
                                        href="{{ $item->proof_url }}"
                                        download
                                        target="_blank"
                                        color="amber"
                                        size="xs"
                                        class="flex-1"
                                    >
                                        Proof Img
                                    </x-filament::button>
                                @endif

                                @if ($item->has_proof)
                                    <div class="flex flex-1">
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
