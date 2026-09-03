<x-filament-panels::page>

    {{-- ── Class Details Infolist ── --}}
    @if ($this->hasInfolist())
        {{ $this->infolist }}
    @endif

    @php
        $transportClass = $this->getRecord();
        $basePrice      = (float) $transportClass->price;
        $schedules      = $transportClass->schedules()->with('ferryRoute')->orderBy('departure_time')->get();
        $groupedByRoute = $schedules->groupBy('ferry_route_id');
        $totalCount     = $schedules->count();
        $selectedCount  = count($this->selectedSchedules);
    @endphp

    {{-- ── Bulk Action Bar (sticky, appears when something is selected) ── --}}
    @if ($selectedCount > 0)
        <div class="flex items-center justify-between gap-4 rounded-xl border border-primary-300 dark:border-primary-700 bg-white/95 dark:bg-gray-900/95 px-5 py-3 shadow-xl ring-1 ring-primary-500/20 transition-all duration-200 mb-4"
            style="position: sticky; top: 4.5rem; z-index: 15; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center rounded-full bg-primary-600 text-white text-xs font-bold w-6 h-6">{{ $selectedCount }}</span>
                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ $selectedCount }} schedule(s) selected
                </span>
                <button wire:click="clearSelection"
                    class="text-xs text-primary-600 dark:text-primary-400 hover:underline ml-1 font-semibold">
                    Clear
                </button>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="openPromoModal"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-medium px-4 py-2 transition-colors shadow-sm">
                    <x-heroicon-m-sparkles class="h-4 w-4" />
                    Set Promo
                </button>
                <button wire:click="openPriceModal"
                    class="inline-flex items-center gap-2 rounded-lg bg-primary-600 hover:bg-primary-700 active:bg-primary-800 text-white text-sm font-medium px-4 py-2 transition-colors shadow-sm">
                    <x-heroicon-m-pencil-square class="h-4 w-4" />
                    Edit Price
                </button>
                <button x-on:click="if(confirm('Restore {{ $selectedCount }} schedule(s) to base price ₱{{ number_format($basePrice, 2) }}?')) $wire.restorePrice()"
                    class="inline-flex items-center gap-2 rounded-lg bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white text-sm font-medium px-4 py-2 transition-colors shadow-sm">
                    <x-heroicon-m-arrow-path class="h-4 w-4" />
                    Restore Price
                </button>
            </div>
        </div>
    @endif

    {{-- ── Price Edit Modal ── --}}
    @if ($this->showPriceModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4" style="background:rgba(0,0,0,0.65); backdrop-filter: blur(4px);"
            x-trap.noscroll="true">
            <div class="w-full max-w-sm rounded-2xl bg-white dark:bg-gray-900 shadow-2xl border border-gray-200 dark:border-gray-800"
                style="max-height: min(90vh, 500px); display: flex; flex-direction: column; overflow: hidden;">

                {{-- Modal Header --}}
                <div style="flex-shrink: 0;" class="flex items-center justify-between px-5 py-3.5 border-b border-gray-200 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-800/80">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Edit Class Add-on Price</h3>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                            Updating {{ count($this->selectedSchedules) }} schedule(s)
                        </p>
                    </div>
                    <button wire:click="cancelPriceModal"
                        class="rounded-lg p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <x-heroicon-m-x-mark class="h-5 w-5" />
                    </button>
                </div>

                {{-- Modal Body --}}
                <div style="flex: 1 1 auto; overflow-y: auto;" class="px-5 py-4 space-y-3.5">
                    <div class="rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700/60 px-3.5 py-2.5 text-xs text-gray-600 dark:text-gray-300">
                        <span class="font-semibold text-gray-900 dark:text-white">Base price:</span> ₱{{ number_format($basePrice, 2) }}<br>
                        <span class="text-[11px] text-gray-400 dark:text-gray-500">The add-on price is added on top of the base schedule price during booking.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            New Add-on Price <span class="text-gray-400 font-normal">(₱)</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">₱</span>
                            <input type="number"
                                step="0.01"
                                min="0"
                                wire:model="newAdditionalPrice"
                                placeholder="0.00"
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-8 pr-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm font-semibold"
                                autofocus
                            />
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div style="flex-shrink: 0;" class="flex items-center justify-end gap-2 px-5 py-3 border-t border-gray-200 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-800/80">
                    <button wire:click="cancelPriceModal"
                        class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-medium px-3.5 py-1.5 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="applyPriceChange"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold px-4 py-1.5 transition-colors shadow-sm">
                        <x-heroicon-m-check class="h-3.5 w-3.5" />
                        Apply to {{ count($this->selectedSchedules) }} Schedule(s)
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- ── Promo / Super Promo Modal ── --}}
    @if ($this->showPromoModal)
        <style>
            html.dark .promo-theme-input,
            .dark .promo-theme-input {
                background-color: #1f2937 !important;
                color: #f9fafb !important;
                border: 1px solid #374151 !important;
                color-scheme: dark !important;
            }
            .promo-theme-input {
                background-color: #ffffff !important;
                color: #111827 !important;
                border: 1px solid #d1d5db !important;
                color-scheme: light !important;
            }
            .promo-theme-input:focus {
                border-color: #6366f1 !important;
                box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.25) !important;
                outline: none !important;
            }

            /* Rate Tier Cards - High Contrast Selection & Instant Response */
            .rate-tier-card {
                cursor: pointer;
                user-select: none;
                transition: transform 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
            }
            .rate-tier-unselected > div {
                border: 1.5px solid #334155 !important;
                background-color: rgba(15, 23, 42, 0.5) !important;
                opacity: 0.72;
            }
            html:not(.dark) .rate-tier-unselected > div {
                border: 1.5px solid #cbd5e1 !important;
                background-color: rgba(248, 250, 252, 0.8) !important;
                opacity: 0.75;
            }
            .rate-tier-unselected:hover > div {
                border-color: #64748b !important;
                opacity: 1 !important;
                transform: translateY(-1px);
            }

            /* Regular Tier Selected (Vibrant Blue Border & Glow) */
            .rate-tier-selected-regular > div {
                border: 2.5px solid #3b82f6 !important;
                background-color: rgba(59, 130, 246, 0.16) !important;
                box-shadow: 0 0 0 1px #3b82f6, 0 8px 24px -4px rgba(59, 130, 246, 0.45) !important;
                opacity: 1 !important;
                transform: translateY(-2px);
            }

            /* Promotional Tier Selected (Radiant Orange Border & Glow) */
            .rate-tier-selected-promo > div {
                border: 2.5px solid #f97316 !important;
                background-color: rgba(249, 115, 22, 0.16) !important;
                box-shadow: 0 0 0 1px #f97316, 0 8px 24px -4px rgba(249, 115, 22, 0.45) !important;
                opacity: 1 !important;
                transform: translateY(-2px);
            }

            /* Super Promo Tier Selected (Vivid Purple Border & Glow) */
            .rate-tier-selected-super > div {
                border: 2.5px solid #a855f7 !important;
                background-color: rgba(168, 85, 247, 0.16) !important;
                box-shadow: 0 0 0 1px #a855f7, 0 8px 24px -4px rgba(168, 85, 247, 0.45) !important;
                opacity: 1 !important;
                transform: translateY(-2px);
            }

            /* Expiry Behavior Options */
            .promo-expiry-unselected {
                border: 1.5px solid #334155 !important;
                background-color: rgba(15, 23, 42, 0.5) !important;
                opacity: 0.72;
                transition: all 0.15s ease-in-out;
            }
            html:not(.dark) .promo-expiry-unselected {
                border: 1.5px solid #cbd5e1 !important;
                background-color: #ffffff !important;
            }
            .promo-expiry-unselected:hover {
                border-color: #64748b !important;
                opacity: 1 !important;
            }
            .promo-expiry-selected-temp {
                border: 2.5px solid #6366f1 !important;
                background-color: rgba(99, 102, 241, 0.14) !important;
                box-shadow: 0 0 0 1px #6366f1, 0 6px 18px -2px rgba(99, 102, 241, 0.35) !important;
                opacity: 1 !important;
            }
            .promo-expiry-selected-perm {
                border: 2.5px solid #ef4444 !important;
                background-color: rgba(239, 68, 68, 0.14) !important;
                box-shadow: 0 0 0 1px #ef4444, 0 6px 18px -2px rgba(239, 68, 68, 0.35) !important;
                opacity: 1 !important;
            }
        </style>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6" style="background:rgba(0,0,0,0.75); backdrop-filter: blur(6px);"
            x-trap.noscroll="true"
            x-data="{
                rateType: @entangle('modalRateType'),
                promoType: @entangle('modalPromoType')
            }">
            <div class="w-full max-w-xl rounded-2xl bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-2xl border border-gray-200 dark:border-gray-800 flex flex-col"
                style="max-height: calc(100vh - 3rem); overflow: hidden;">

                {{-- Modal Header (Clean star icon with NO outline box) --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-900/90 shrink-0">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <!-- Clean floating star icon with NO box or border -->
                        <svg class="w-6 h-6 text-amber-400 shrink-0 drop-shadow-sm" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>
                        </svg>
                        <div class="min-w-0">
                            <h3 class="text-base font-bold text-gray-900 dark:text-white tracking-tight truncate">Batch Promo &amp; Fare Tier Setup</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                                Configuring <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ count($this->selectedSchedules) }}</span> schedule(s) &bull; <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $transportClass->name }}</span>
                            </p>
                        </div>
                    </div>
                    <button wire:click="cancelPromoModal"
                        class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors shrink-0">
                        <x-heroicon-m-x-mark class="h-5 w-5" />
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="flex-1 overflow-y-auto px-6 py-5" style="scrollbar-width: thin;">
                    <div style="display: flex; flex-direction: column; gap: 24px;">

                        {{-- 1. Rate Tier Selector (Instant Alpine Switching with Distinct Glowing Colored Borders) --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2.5">
                                Select Fare Tier
                            </label>
                            <div style="display: flex; gap: 12px; width: 100%;">
                                {{-- Regular --}}
                                <div 
                                    @click="rateType = 'regular'; $wire.set('modalRateType', 'regular', false)"
                                    class="rate-tier-card"
                                    :class="rateType === 'regular' ? 'rate-tier-selected-regular' : 'rate-tier-unselected'"
                                    style="display: flex; flex: 1; flex-direction: column; margin: 0; min-width: 0;">
                                    <input type="radio" value="regular" x-model="rateType" wire:model="modalRateType" class="sr-only" />
                                    <div class="p-3.5 rounded-xl text-center flex flex-col items-center justify-center gap-1.5 transition-all">
                                        <span class="text-xl">🔵</span>
                                        <span class="text-xs font-bold truncate w-full" :class="rateType === 'regular' ? 'text-blue-600 dark:text-blue-400' : 'text-gray-900 dark:text-white'">Regular</span>
                                        <span class="text-[11px] truncate w-full" :class="rateType === 'regular' ? 'text-blue-600/80 dark:text-blue-300/80 font-medium' : 'text-gray-500 dark:text-gray-400'">Standard</span>
                                        <div class="h-5 flex items-center justify-center">
                                            <span x-show="rateType === 'regular'" x-cloak class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-blue-600 text-white shadow-sm">
                                                <svg class="w-3 h-3 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                Selected
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Promotional --}}
                                <div 
                                    @click="rateType = 'promotional'; $wire.set('modalRateType', 'promotional', false)"
                                    class="rate-tier-card"
                                    :class="rateType === 'promotional' ? 'rate-tier-selected-promo' : 'rate-tier-unselected'"
                                    style="display: flex; flex: 1; flex-direction: column; margin: 0; min-width: 0;">
                                    <input type="radio" value="promotional" x-model="rateType" wire:model="modalRateType" class="sr-only" />
                                    <div class="p-3.5 rounded-xl text-center flex flex-col items-center justify-center gap-1.5 transition-all">
                                        <span class="text-xl">🟠</span>
                                        <span class="text-xs font-bold truncate w-full" :class="rateType === 'promotional' ? 'text-orange-600 dark:text-orange-400' : 'text-gray-900 dark:text-white'">Promotional</span>
                                        <span class="text-[11px] truncate w-full" :class="rateType === 'promotional' ? 'text-orange-600/80 dark:text-orange-300/80 font-medium' : 'text-gray-500 dark:text-gray-400'">Vouchers OK</span>
                                        <div class="h-5 flex items-center justify-center">
                                            <span x-show="rateType === 'promotional'" x-cloak class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-orange-600 text-white shadow-sm">
                                                <svg class="w-3 h-3 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                Selected
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Super Promo --}}
                                <div 
                                    @click="rateType = 'super_promotional'; $wire.set('modalRateType', 'super_promotional', false)"
                                    class="rate-tier-card"
                                    :class="rateType === 'super_promotional' ? 'rate-tier-selected-super' : 'rate-tier-unselected'"
                                    style="display: flex; flex: 1; flex-direction: column; margin: 0; min-width: 0;">
                                    <input type="radio" value="super_promotional" x-model="rateType" wire:model="modalRateType" class="sr-only" />
                                    <div class="p-3.5 rounded-xl text-center flex flex-col items-center justify-center gap-1.5 transition-all">
                                        <span class="text-xl">🟣</span>
                                        <span class="text-xs font-bold truncate w-full" :class="rateType === 'super_promotional' ? 'text-purple-600 dark:text-purple-400' : 'text-gray-900 dark:text-white'">Super Promo</span>
                                        <span class="text-[11px] truncate w-full" :class="rateType === 'super_promotional' ? 'text-purple-600/80 dark:text-purple-300/80 font-medium' : 'text-gray-500 dark:text-gray-400'">Strict Promo</span>
                                        <div class="h-5 flex items-center justify-center">
                                            <span x-show="rateType === 'super_promotional'" x-cloak class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-purple-600 text-white shadow-sm">
                                                <svg class="w-3 h-3 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                Selected
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Editable Price / Add-on Amount (Lowered & Theme-Responsive) --}}
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                                    Class Add-on Price <span class="font-normal text-gray-400">(₱)</span>
                                </label>
                                <span class="text-xs font-medium px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                    Base Class Price: <strong class="text-gray-900 dark:text-white">₱{{ number_format($basePrice, 2) }}</strong>
                                </span>
                            </div>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-base select-none">₱</span>
                                <input type="number"
                                    step="0.01"
                                    min="0"
                                    wire:model="modalPrice"
                                    placeholder="0.00"
                                    class="promo-theme-input w-full rounded-xl pl-9 pr-4 py-2.5 text-sm font-semibold border transition shadow-sm"
                                />
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                                Leave as is to keep the current price, or enter a custom promo amount for the selected schedule(s).
                            </p>
                        </div>

                        {{-- 3. Dynamic Fields: Promo Expiry Mode & Duration (Instantly Toggled via Alpine) --}}
                        <div x-show="rateType !== 'regular'" x-cloak class="rounded-2xl border border-indigo-200 dark:border-indigo-900/60 bg-indigo-50/40 dark:bg-indigo-950/20 p-6 space-y-6">
                            
                            {{-- Expiry Behavior Option (Spacious & Non-touching) --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-indigo-950 dark:text-indigo-300 mb-3">
                                    Promo Expiry Behavior
                                </label>
                                <div style="display: flex; gap: 14px; width: 100%;">
                                    {{-- Temporary --}}
                                    <div 
                                        @click="promoType = 'temporary'; $wire.set('modalPromoType', 'temporary', false)"
                                        class="p-4 rounded-xl flex flex-col gap-2 min-h-[110px] justify-center cursor-pointer select-none"
                                        :class="promoType === 'temporary' ? 'promo-expiry-selected-temp' : 'promo-expiry-unselected'"
                                        style="display: flex; flex: 1; flex-direction: column; margin: 0; min-width: 0;">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <input type="radio" value="temporary" x-model="promoType" wire:model="modalPromoType" class="sr-only" />
                                                <span class="text-xs font-bold" :class="promoType === 'temporary' ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-900 dark:text-white'">⏳ Temporary</span>
                                            </div>
                                            <span x-show="promoType === 'temporary'" x-cloak class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-indigo-600 text-white shadow-sm">Selected</span>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                            After promo end date, the schedule <strong>reverts back to Regular fare</strong> &amp; restores base price.
                                        </p>
                                    </div>

                                    {{-- Permanent --}}
                                    <div 
                                        @click="promoType = 'permanent'; $wire.set('modalPromoType', 'permanent', false)"
                                        class="p-4 rounded-xl flex flex-col gap-2 min-h-[110px] justify-center cursor-pointer select-none"
                                        :class="promoType === 'permanent' ? 'promo-expiry-selected-perm' : 'promo-expiry-unselected'"
                                        style="display: flex; flex: 1; flex-direction: column; margin: 0; min-width: 0;">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-2">
                                                <input type="radio" value="permanent" x-model="promoType" wire:model="modalPromoType" class="sr-only" />
                                                <span class="text-xs font-bold" :class="promoType === 'permanent' ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white'">🔒 Permanent</span>
                                            </div>
                                            <span x-show="promoType === 'permanent'" x-cloak class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-red-600 text-white shadow-sm">Selected</span>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                                            After promo end date, the schedule <strong>will not display on the user booking page</strong>.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Duration Date/Time Pickers (Spacious & Theme-Responsive) --}}
                            <div class="pt-1">
                                <div style="display: flex; gap: 14px; width: 100%;">
                                    <div style="display: flex; flex: 1; flex-direction: column; min-width: 0;">
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 truncate">
                                            Promo Start Date &amp; Time
                                        </label>
                                        <input type="datetime-local"
                                            wire:model="modalDurationStart"
                                            class="promo-theme-input w-full rounded-xl px-3.5 py-2.5 text-xs font-medium border transition shadow-sm"
                                        />
                                    </div>
                                    <div style="display: flex; flex: 1; flex-direction: column; min-width: 0;">
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2 truncate">
                                            Promo End Date &amp; Time
                                        </label>
                                        <input type="datetime-local"
                                            wire:model="modalDurationEnd"
                                            class="promo-theme-input w-full rounded-xl px-3.5 py-2.5 text-xs font-medium border transition shadow-sm"
                                        />
                                    </div>
                                </div>
                            </div>

                            {{-- Notice / Policy Disclaimer for Promo Tiers --}}
                            {{-- Super Promo Policy --}}
                            <div x-show="rateType === 'super_promotional'" x-cloak class="rounded-xl p-4 text-xs leading-relaxed border mt-2 bg-purple-50/80 dark:bg-purple-950/40 border-purple-300 dark:border-purple-800 text-purple-950 dark:text-purple-200" style="border-color: #a855f7 !important;">
                                <p class="font-bold flex items-center gap-1.5 text-xs text-purple-700 dark:text-purple-300 mb-1.5">
                                    <span>🟣 Super Promo Policy:</span>
                                </p>
                                <ul class="list-disc pl-5 space-y-1 text-xs text-purple-900 dark:text-purple-200/90">
                                    <li>Government mandate discounts (Senior, PWD, Student) are <strong>disabled</strong> (₱0.00).</li>
                                    <li>Website vouchers &amp; promo codes are <strong>blocked</strong>.</li>
                                    <li>Strictly non-refundable and non-transferable.</li>
                                </ul>
                            </div>

                            {{-- Promotional Policy --}}
                            <div x-show="rateType === 'promotional'" x-cloak class="rounded-xl p-4 text-xs leading-relaxed border mt-2 bg-orange-50/80 dark:bg-orange-950/40 border-orange-300 dark:border-orange-800 text-orange-950 dark:text-orange-200" style="border-color: #f97316 !important;">
                                <p class="font-bold flex items-center gap-1.5 text-xs text-orange-700 dark:text-orange-300 mb-1.5">
                                    <span>🟠 Promotional Policy:</span>
                                </p>
                                <ul class="list-disc pl-5 space-y-1 text-xs text-orange-900 dark:text-orange-200/90">
                                    <li>Government mandate discounts (Senior, PWD, Student) are <strong>disabled</strong> (₱0.00).</li>
                                    <li>Website vouchers &amp; promo codes <strong>can still be added and applied</strong>.</li>
                                    <li>Non-refundable ticket fare.</li>
                                </ul>
                            </div>

                        </div>

                        {{-- Regular Fare Policy Notice --}}
                        <div x-show="rateType === 'regular'" x-cloak class="rounded-2xl border border-blue-300 dark:border-blue-800 bg-blue-50/70 dark:bg-blue-950/30 p-4 text-xs text-blue-900 dark:text-blue-200 leading-relaxed" style="border-color: #3b82f6 !important;">
                            <p class="font-bold flex items-center gap-1.5 text-blue-700 dark:text-blue-300 mb-1">
                                <span>🔵 Regular Fare Policy:</span>
                            </p>
                            <p class="text-blue-900/80 dark:text-blue-200/80 leading-relaxed">
                                This will remove promo flags and restore standard fare rules. Government mandate discounts (Senior, PWD, Student) and website vouchers will both be permitted.
                            </p>
                        </div>

                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-900/90 shrink-0">
                    <button wire:click="cancelPromoModal"
                        class="rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-semibold px-4 py-2.5 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="applyPromoModal"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-5 py-2.5 transition-colors shadow-lg shadow-indigo-600/30">
                        <x-heroicon-m-check class="h-4 w-4" />
                        Save for {{ count($this->selectedSchedules) }} Schedule(s)
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- ── Routes & Schedules Section ── --}}
    <x-filament::section>
        <x-slot name="heading">Routes &amp; Schedules Using This Class</x-slot>
        <x-slot name="description">
            {{ $totalCount }} schedule(s) across {{ $groupedByRoute->count() }} route(s)
        </x-slot>

        @if ($groupedByRoute->isEmpty())
            <div class="py-12 text-center text-gray-500 dark:text-gray-400">
                <x-heroicon-o-calendar-days class="mx-auto mb-3 h-12 w-12 opacity-30" />
                <p class="text-sm font-medium">No schedules are currently using this transport class.</p>
            </div>
        @else
            <div class="space-y-5">
                @foreach ($groupedByRoute as $routeId => $routeSchedules)
                    @php
                        $route          = $routeSchedules->first()->ferryRoute;
                        $vehicleName    = $routeSchedules->first()->vehicle_name ?? '—';
                        $operator       = $route?->operator ?? '—';
                        $mode           = $route?->mode ?? '';
                        $routeIds       = $routeSchedules->pluck('id')->toArray();
                        $selectedInRoute = array_values(array_intersect($routeIds, $this->selectedSchedules));
                        $allSelected    = count($selectedInRoute) === count($routeIds) && count($routeIds) > 0;
                        $someSelected   = count($selectedInRoute) > 0 && !$allSelected;
                    @endphp

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">

                        {{-- Route Header --}}
                        <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">

                            {{-- Select-All Checkbox for this Route --}}
                            <input
                                type="checkbox"
                                wire:click="toggleRouteAll({{ (int) $routeId }})"
                                {{ $allSelected ? 'checked' : '' }}
                                x-init="$el.indeterminate = {{ $someSelected ? 'true' : 'false' }}"
                                class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 cursor-pointer flex-shrink-0"
                                title="{{ $allSelected ? 'Deselect all in this route' : ($someSelected ? 'Select remaining in this route' : 'Select all in this route') }}"
                            />

                            {{-- Route Info --}}
                            <div class="flex flex-1 items-center gap-3 min-w-0">
                                <div class="flex items-center gap-2 font-semibold text-gray-900 dark:text-white text-sm truncate">
                                    <span class="truncate">{{ $route?->origin ?? '?' }}</span>
                                    <x-heroicon-m-arrow-right class="h-3.5 w-3.5 text-primary-500 flex-shrink-0" />
                                    <span class="truncate">{{ $route?->destination ?? '?' }}</span>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 truncate hidden sm:inline">
                                    🚌 {{ $vehicleName }}
                                </span>
                            </div>

                            {{-- Badges + Count --}}
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $mode === 'airline' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' : 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' }}">
                                    {{ $mode === 'airline' ? '✈️' : '🚢' }} {{ ucfirst($mode) }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 hidden md:inline">{{ $operator }}</span>
                                <span class="inline-flex items-center rounded-full bg-primary-100 dark:bg-primary-900/30 px-2 py-0.5 text-xs font-medium text-primary-700 dark:text-primary-300">
                                    {{ $routeSchedules->count() }} sched.
                                </span>
                            </div>

                            {{-- Route-level bulk actions when some in this route are selected --}}
                            @if (count($selectedInRoute) > 0)
                                <div class="flex items-center gap-1.5 border-l border-gray-300 dark:border-gray-600 pl-3 ml-1 flex-shrink-0">
                                    <button
                                        wire:click="openPromoModal"
                                        title="Set promo for {{ count($selectedInRoute) }} selected"
                                        class="inline-flex items-center gap-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-2.5 py-1.5 transition-colors">
                                        <x-heroicon-m-sparkles class="h-3.5 w-3.5" />
                                        Promo ({{ count($selectedInRoute) }})
                                    </button>
                                    <button
                                        wire:click="openPriceModal"
                                        title="Edit price for {{ count($selectedInRoute) }} selected"
                                        class="inline-flex items-center gap-1 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium px-2.5 py-1.5 transition-colors">
                                        <x-heroicon-m-pencil-square class="h-3.5 w-3.5" />
                                        Edit ({{ count($selectedInRoute) }})
                                    </button>
                                    <button
                                        x-on:click="if(confirm('Restore {{ count($selectedInRoute) }} schedule(s) to ₱{{ number_format($basePrice, 2) }}?')) $wire.restorePrice()"
                                        title="Restore price for {{ count($selectedInRoute) }} selected"
                                        class="inline-flex items-center gap-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium px-2.5 py-1.5 transition-colors">
                                        <x-heroicon-m-arrow-path class="h-3.5 w-3.5" />
                                        Restore ({{ count($selectedInRoute) }})
                                    </button>
                                </div>
                            @endif

                        </div>{{-- end route header --}}

                        {{-- Schedules Table --}}
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 dark:border-gray-700/60 bg-white dark:bg-gray-900">
                                        <th class="w-10 px-3 py-2"></th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Departure</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Arrival</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Base Price</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Class Add-on</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Tickets</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Rate Tier</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Status</th>
                                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach ($routeSchedules as $i => $schedule)
                                        @php
                                            $pivot       = $schedule->pivot;
                                            $isSelected  = in_array($schedule->id, $this->selectedSchedules, true);
                                            $rateType    = $pivot->rate_type ?? 'regular';
                                            $isPromo     = (bool) ($pivot->is_promo ?? false) || in_array($rateType, ['promotional', 'super_promotional'], true);
                                            $promoType   = $pivot->promo_type ?? 'temporary';
                                            $promoStart  = $pivot->promo_duration_start ? \Carbon\Carbon::parse($pivot->promo_duration_start) : null;
                                            $promoEnd    = $pivot->promo_duration_end ? \Carbon\Carbon::parse($pivot->promo_duration_end) : null;
                                            $addOnPrice  = (float) ($pivot->additional_price ?? 0);
                                            $isBasePrice = abs($addOnPrice - $basePrice) < 0.01;
                                            $now         = now();

                                            $rateBadge = match($rateType) {
                                                'promotional'       => ['label' => '🟠 Promotional', 'class' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300'],
                                                'super_promotional' => ['label' => '🟣 Super Promo',  'class' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300'],
                                                default             => ['label' => '🔵 Regular',      'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300'],
                                            };
                                        @endphp

                                        <tr class="group transition-colors duration-75
                                            {{ $isSelected
                                                ? 'bg-primary-50 dark:bg-primary-900/20 ring-inset ring-1 ring-primary-200 dark:ring-primary-700'
                                                : ($i % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50/40 dark:bg-gray-800/30') }}
                                            hover:bg-primary-50/60 dark:hover:bg-primary-900/10 cursor-pointer"
                                            wire:click="toggleSchedule({{ $schedule->id }})"
                                        >
                                            {{-- Row Checkbox --}}
                                            <td class="px-3 py-3" wire:click.stop>
                                                <input
                                                    type="checkbox"
                                                    wire:click="toggleSchedule({{ $schedule->id }})"
                                                    {{ $isSelected ? 'checked' : '' }}
                                                    class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 text-primary-600 focus:ring-primary-500 cursor-pointer"
                                                />
                                            </td>

                                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                                {{ $schedule->departure_time ? \Carbon\Carbon::parse($schedule->departure_time)->format('M j, Y g:i A') : '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                                {{ $schedule->arrival_time ? \Carbon\Carbon::parse($schedule->arrival_time)->format('M j, Y g:i A') : '—' }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200 whitespace-nowrap">
                                                ₱{{ number_format((float) $schedule->price, 2) }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="font-semibold {{ $isBasePrice ? 'text-gray-700 dark:text-gray-200' : 'text-primary-600 dark:text-primary-400' }}">
                                                    ₱{{ number_format($addOnPrice, 2) }}
                                                </span>
                                                @if (!$isBasePrice)
                                                    <span class="ml-1 text-xs text-gray-400 dark:text-gray-500 line-through">₱{{ number_format($basePrice, 2) }}</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-gray-700 dark:text-gray-200">
                                                {{ number_format((int) ($pivot->tickets_available ?? 0)) }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex flex-col gap-1 items-start">
                                                    <div class="flex items-center gap-1.5 flex-wrap">
                                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $rateBadge['class'] }}">
                                                            {{ $rateBadge['label'] }}
                                                        </span>
                                                        @if ($isPromo)
                                                            <span class="inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-bold {{ $promoType === 'permanent' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' }}">
                                                                {{ $promoType === 'permanent' ? '🔒 Perm' : '⏳ Temp' }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    @if ($isPromo && $promoEnd)
                                                        <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                                            @if ($now->isAfter($promoEnd))
                                                                <span class="text-red-500 font-semibold">{{ $promoType === 'permanent' ? 'Expired (Hidden)' : 'Expired (Reverted)' }}</span>
                                                            @else
                                                                Until {{ $promoEnd->format('M j, Y g:i A') }}
                                                            @endif
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if ($schedule->is_active)
                                                    <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900/40 px-2 py-0.5 text-xs font-medium text-green-800 dark:text-green-300">Active</span>
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900/40 px-2 py-0.5 text-xs font-medium text-red-800 dark:text-red-300">Inactive</span>
                                                @endif
                                            </td>

                                            {{-- Per-row Actions --}}
                                            <td class="px-4 py-3 text-right whitespace-nowrap" wire:click.stop>
                                                <div class="flex items-center justify-end gap-1.5 opacity-60 group-hover:opacity-100 transition-opacity">
                                                    <button
                                                        wire:click="openPromoModal({{ $schedule->id }})"
                                                        title="Set Promo / Super Promo"
                                                        class="inline-flex items-center gap-1 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-medium px-2 py-1 transition-colors">
                                                        <x-heroicon-m-sparkles class="h-3 w-3" />
                                                        Promo
                                                    </button>
                                                    <button
                                                        wire:click="openPriceModal({{ $schedule->id }})"
                                                        title="Edit add-on price"
                                                        class="inline-flex items-center gap-1 rounded-md bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium px-2 py-1 transition-colors">
                                                        <x-heroicon-m-pencil-square class="h-3 w-3" />
                                                        Edit
                                                    </button>
                                                    <button
                                                        x-on:click="if(confirm('Restore this schedule to base price ₱{{ number_format($basePrice, 2) }}?')) $wire.restorePrice({{ $schedule->id }})"
                                                        title="Restore to base price ₱{{ number_format($basePrice, 2) }}"
                                                        class="inline-flex items-center gap-1 rounded-md bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium px-2 py-1 transition-colors">
                                                        <x-heroicon-m-arrow-path class="h-3 w-3" />
                                                        Restore
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>{{-- end table --}}

                    </div>{{-- end route card --}}
                @endforeach
            </div>
        @endif
    </x-filament::section>

</x-filament-panels::page>
