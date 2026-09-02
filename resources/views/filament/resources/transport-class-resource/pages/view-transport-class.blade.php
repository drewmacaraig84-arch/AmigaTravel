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
        <div class="sticky top-0 z-40 flex items-center justify-between gap-4 rounded-xl border border-primary-300 dark:border-primary-700 bg-primary-50 dark:bg-primary-900/30 px-5 py-3 shadow-lg ring-1 ring-primary-200 dark:ring-primary-800">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center rounded-full bg-primary-600 text-white text-xs font-bold w-6 h-6">{{ $selectedCount }}</span>
                <span class="text-sm font-medium text-primary-900 dark:text-primary-100">
                    {{ $selectedCount }} schedule(s) selected
                </span>
                <button wire:click="clearSelection"
                    class="text-xs text-primary-600 dark:text-primary-400 hover:underline ml-1">
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
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5);">
            <div class="w-full max-w-sm rounded-2xl bg-white dark:bg-gray-900 shadow-2xl ring-1 ring-black/10 dark:ring-white/10 overflow-hidden"
                x-trap.noscroll="true">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Edit Class Add-on Price</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Updating {{ count($this->selectedSchedules) }} schedule(s)
                        </p>
                    </div>
                    <button wire:click="cancelPriceModal"
                        class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <x-heroicon-m-x-mark class="h-5 w-5" />
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="px-5 py-4 space-y-4">
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-800 px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                        <span class="font-medium">Base price:</span> ₱{{ number_format($basePrice, 2) }}<br>
                        <span class="text-xs text-gray-400 dark:text-gray-500">The add-on price is added on top of the base schedule price during booking.</span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                            New Add-on Price <span class="text-gray-400">(₱)</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 font-medium text-sm">₱</span>
                            <input type="number"
                                step="0.01"
                                min="0"
                                wire:model="newAdditionalPrice"
                                placeholder="0.00"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-8 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition"
                                autofocus
                            />
                        </div>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-2 px-5 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <button wire:click="cancelPriceModal"
                        class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-medium px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="applyPriceChange"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium px-4 py-2 transition-colors shadow-sm">
                        <x-heroicon-m-check class="h-4 w-4" />
                        Apply to {{ count($this->selectedSchedules) }} Schedule(s)
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- ── Promo / Super Promo Modal ── --}}
    @if ($this->showPromoModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.55);"
            x-trap.noscroll="true">
            <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-gray-900 shadow-2xl ring-1 ring-black/10 dark:ring-white/10 overflow-hidden">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-50/50 to-purple-50/50 dark:from-indigo-950/30 dark:to-purple-950/30">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-indigo-600 text-white shadow-md">
                            <x-heroicon-m-sparkles class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Batch Promo & Fare Tier Setup</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Configuring <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ count($this->selectedSchedules) }}</span> schedule(s) for <span class="font-medium text-gray-700 dark:text-gray-300">{{ $transportClass->name }}</span>
                            </p>
                        </div>
                    </div>
                    <button wire:click="cancelPromoModal"
                        class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <x-heroicon-m-x-mark class="h-5 w-5" />
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="px-6 py-5 space-y-5 max-h-[75vh] overflow-y-auto">

                    {{-- 1. Rate Tier Selector --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400 mb-2">
                            Select Fare Tier
                        </label>
                        <div class="grid grid-cols-3 gap-2.5">
                            {{-- Regular --}}
                            <label class="relative flex flex-col items-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $this->modalRateType === 'regular' ? 'border-blue-500 bg-blue-50/60 dark:bg-blue-950/40 shadow-sm' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                                <input type="radio" value="regular" wire:model.live="modalRateType" class="sr-only" />
                                <span class="text-lg">🔵</span>
                                <span class="text-xs font-bold text-gray-900 dark:text-white mt-1">Regular</span>
                                <span class="text-[10px] text-gray-500 text-center mt-0.5 leading-tight">Standard policy</span>
                            </label>

                            {{-- Promotional --}}
                            <label class="relative flex flex-col items-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $this->modalRateType === 'promotional' ? 'border-orange-500 bg-orange-50/60 dark:bg-orange-950/40 shadow-sm' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                                <input type="radio" value="promotional" wire:model.live="modalRateType" class="sr-only" />
                                <span class="text-lg">🟠</span>
                                <span class="text-xs font-bold text-gray-900 dark:text-white mt-1">Promotional</span>
                                <span class="text-[10px] text-orange-600 dark:text-orange-400 text-center mt-0.5 leading-tight">Voucher enabled</span>
                            </label>

                            {{-- Super Promo --}}
                            <label class="relative flex flex-col items-center p-3 rounded-xl border-2 cursor-pointer transition-all {{ $this->modalRateType === 'super_promotional' ? 'border-purple-500 bg-purple-50/60 dark:bg-purple-950/40 shadow-sm' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                                <input type="radio" value="super_promotional" wire:model.live="modalRateType" class="sr-only" />
                                <span class="text-lg">🟣</span>
                                <span class="text-xs font-bold text-gray-900 dark:text-white mt-1">Super Promo</span>
                                <span class="text-[10px] text-purple-600 dark:text-purple-400 text-center mt-0.5 leading-tight">Strict promo</span>
                            </label>
                        </div>
                    </div>

                    {{-- 2. Editable Price / Add-on Amount --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">
                                Class Add-on Price <span class="text-gray-400 font-normal">(₱)</span>
                            </label>
                            <span class="text-xs text-gray-400">Base Class Price: ₱{{ number_format($basePrice, 2) }}</span>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 font-bold text-sm">₱</span>
                            <input type="number"
                                step="0.01"
                                min="0"
                                wire:model="modalPrice"
                                placeholder="0.00"
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white pl-9 pr-4 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm"
                            />
                        </div>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">
                            Leave as is to keep the current price, or enter a custom promo amount for the selected schedule(s).
                        </p>
                    </div>

                    {{-- 3. Dynamic Fields: Promo Expiry Mode & Duration (Shown when Promotional or Super Promo) --}}
                    @if ($this->modalRateType !== 'regular')
                        <div class="rounded-xl border border-indigo-200 dark:border-indigo-800/60 bg-indigo-50/40 dark:bg-indigo-950/20 p-4 space-y-4">
                            
                            {{-- Expiry Behavior Option --}}
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-indigo-950 dark:text-indigo-200 mb-2">
                                    Promo Expiry Behavior
                                </label>
                                <div class="grid grid-cols-2 gap-3">
                                    {{-- Temporary --}}
                                    <label class="flex items-start gap-2.5 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $this->modalPromoType === 'temporary' ? 'border-indigo-500 bg-white dark:bg-gray-900 shadow-sm' : 'border-indigo-200/80 dark:border-indigo-900 bg-white/60 dark:bg-gray-900/60 hover:border-indigo-300' }}">
                                        <input type="radio" value="temporary" wire:model.live="modalPromoType" class="mt-0.5 text-indigo-600 focus:ring-indigo-500" />
                                        <div>
                                            <div class="flex items-center gap-1">
                                                <span class="text-xs font-bold text-gray-900 dark:text-white">⏳ Temporary</span>
                                            </div>
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 leading-snug">
                                                After the promo end date, the schedule <strong>reverts back to Regular fare</strong> &amp; restores base price.
                                            </p>
                                        </div>
                                    </label>

                                    {{-- Permanent --}}
                                    <label class="flex items-start gap-2.5 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $this->modalPromoType === 'permanent' ? 'border-red-500 bg-white dark:bg-gray-900 shadow-sm' : 'border-indigo-200/80 dark:border-indigo-900 bg-white/60 dark:bg-gray-900/60 hover:border-red-300' }}">
                                        <input type="radio" value="permanent" wire:model.live="modalPromoType" class="mt-0.5 text-red-600 focus:ring-red-500" />
                                        <div>
                                            <div class="flex items-center gap-1">
                                                <span class="text-xs font-bold text-gray-900 dark:text-white">🔒 Permanent</span>
                                            </div>
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1 leading-snug">
                                                After the promo end date, the schedule <strong>will not display on the user/website booking page</strong>.
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            {{-- Duration Date/Time Pickers --}}
                            <div class="grid grid-cols-2 gap-3 pt-1">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Promo Start Date &amp; Time
                                    </label>
                                    <input type="datetime-local"
                                        wire:model="modalDurationStart"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                        Promo End Date &amp; Time
                                    </label>
                                    <input type="datetime-local"
                                        wire:model="modalDurationEnd"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition"
                                    />
                                </div>
                            </div>

                            {{-- Notice / Policy Disclaimer --}}
                            <div class="rounded-lg p-3 text-xs leading-relaxed {{ $this->modalRateType === 'super_promotional' ? 'bg-purple-100/70 text-purple-900 dark:bg-purple-950/60 dark:text-purple-200' : 'bg-orange-100/70 text-orange-900 dark:bg-orange-950/60 dark:text-orange-200' }}">
                                @if ($this->modalRateType === 'super_promotional')
                                    <p class="font-semibold flex items-center gap-1.5">
                                        <span>🟣 Super Promo Policy:</span>
                                    </p>
                                    <ul class="list-disc pl-4 mt-1 space-y-0.5 text-[11px]">
                                        <li>Government mandate discounts (Senior, PWD, Student) are <strong>disabled</strong> (₱0.00).</li>
                                        <li>Website vouchers &amp; promo codes are <strong>blocked</strong>.</li>
                                        <li>Non-refundable &amp; non-transferable.</li>
                                    </ul>
                                @else
                                    <p class="font-semibold flex items-center gap-1.5">
                                        <span>🟠 Promotional Policy:</span>
                                    </p>
                                    <ul class="list-disc pl-4 mt-1 space-y-0.5 text-[11px]">
                                        <li>Government mandate discounts (Senior, PWD, Student) are <strong>disabled</strong> (₱0.00).</li>
                                        <li>Website vouchers &amp; promo codes <strong>can still be added and applied</strong>.</li>
                                        <li>Non-refundable ticket fare.</li>
                                    </ul>
                                @endif
                            </div>

                        </div>
                    @else
                        {{-- Regular Fare Policy Notice --}}
                        <div class="rounded-xl border border-blue-200 dark:border-blue-900 bg-blue-50/60 dark:bg-blue-950/30 p-3.5 text-xs text-blue-900 dark:text-blue-200 leading-relaxed">
                            <p class="font-semibold flex items-center gap-1.5 mb-1">
                                <span>🔵 Regular Fare Policy:</span>
                            </p>
                            <p class="text-[11px] text-blue-700 dark:text-blue-300">
                                This will remove promo flags and restore standard fare rules. Government mandate discounts (Senior, PWD, Student) and website vouchers will both be permitted.
                            </p>
                        </div>
                    @endif

                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <button wire:click="cancelPromoModal"
                        class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm font-medium px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="applyPromoModal"
                        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-5 py-2 transition-colors shadow-sm">
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
