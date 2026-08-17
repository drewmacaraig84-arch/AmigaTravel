<div class="min-h-screen bg-transparent">
    <div class="min-h-screen w-full bg-transparent overflow-visible">
            {{-- Modern Gradient Header --}}
            <div class="relative bg-pink-600 px-4 sm:px-6 lg:px-10 py-8 sm:py-10 overflow-hidden">
                <div class="absolute inset-0 opacity-10">
                    <svg class="absolute top-0 right-0 w-[400px] h-[400px] -translate-y-1/4 translate-x-1/4 text-white" viewBox="0 0 200 200" fill="currentColor">
                        <circle cx="100" cy="100" r="100" opacity="0.08"/>
                    </svg>
                    <svg class="absolute bottom-0 left-0 w-[300px] h-[300px] translate-y-1/4 -translate-x-1/4 text-white" viewBox="0 0 200 200" fill="currentColor">
                        <circle cx="100" cy="100" r="80" opacity="0.06"/>
                    </svg>
                </div>
                <div class="relative max-w-6xl mx-auto z-10">
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">Amiga Gracia Travel Booking</h1>
                    <p class="mt-3 text-pink-100 max-w-2xl text-base sm:text-lg">Complete your travel booking in a few easy steps. Your confirmation email and payment QR code are created automatically when you submit.</p>
                </div>
            </div>
            

            <div class="px-3 sm:px-6 lg:px-10 py-5 sm:py-10">
                <div class="max-w-6xl mx-auto">
                <div class="mb-8">
                    @php
                        $isTourPackage = $tour_id || $prefilled_from_package;
                        $steps = $isTourPackage ? ['Route','Discount','Stay','Submit'] : ['Route','Schedule','Discount','Stay','Submit'];

                        $getIcon = function($label) {
                            return match($label) {
                                'Route' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 sm:w-6 sm:h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" /></svg>',
                                'Schedule' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 sm:w-6 sm:h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>',
                                'Discount' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 sm:w-6 sm:h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>',
                                'Stay' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 sm:w-6 sm:h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z" /></svg>',
                                'Submit' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 sm:w-6 sm:h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>',
                                default => ''
                            };
                        };
                    @endphp
                    <div class="relative pt-4 pb-6">
                        <div class="relative z-10 flex w-full items-start justify-between">
                            @foreach($steps as $index => $label)
                                @php $isLast = $index === count($steps) - 1; @endphp
                                <div class="relative flex flex-col items-center justify-center text-center" style="min-width:0;flex:1">
                                    {{-- Connector line to next step -- renders behind circle via z-0 --}}
                                    @if(!$isLast)
                                        <div class="absolute left-1/2 top-[1.125rem] sm:top-[1.5rem] w-full h-[3px] sm:h-[4px] -translate-y-1/2 z-0">
                                            <div class="h-full w-full bg-slate-200"></div>
                                            {{-- Green fill: active when the NEXT step is reached or passed --}}
                                            <div class="absolute inset-y-0 left-0 h-full bg-[#216417] transition-all duration-500 {{ $step >= $index + 2 ? 'w-full' : 'w-0' }}"></div>
                                        </div>
                                    @endif

                                    {{-- Circle with icon or check --}}
                                    <div class="relative z-10 flex h-9 w-9 sm:h-12 sm:w-12 items-center justify-center rounded-full border-2 transition-colors duration-500 {{ $step === $index + 1 ? 'border-[#216417] bg-[#216417] text-white shadow-lg shadow-black/10' : ($step > $index + 1 ? 'border-[#216417] bg-white text-[#216417]' : 'border-slate-600 bg-white text-black') }}">
                                        @if($step > $index + 1)
                                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            {!! $getIcon($label) !!}
                                        @endif
                                    </div>
                                    <div class="mt-1.5 sm:mt-3 text-[9px] sm:text-[10px] font-extrabold uppercase tracking-wider leading-tight {{ $step === $index + 1 ? 'text-black' : ($step > $index + 1 ? 'text-[#216417]' : 'text-slate-400 sm:text-black') }} {{ $step === $index + 1 ? '' : 'hidden sm:block' }}">{{ $label }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @if(!empty($package_name) || !empty($package_price))
                    <div class="mt-4 mb-8 rounded-2xl border border-slate-200 bg-white p-5 max-w-3xl mx-6 sm:mx-10 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Selected Package</div>
                                <div class="font-bold text-xl text-slate-900 mt-1">{{ $package_name }}</div>
                                @if(!empty($package_price))
                                    <div class="text-sm font-medium text-slate-500 mt-1">Starting from &#8369;{{ $package_price }}</div>
                                @endif
                            </div>
                            <div>
                                <a href="{{ url('/tour-package') }}" class="text-sm text-[#216417] font-semibold hover:text-[#216417]/80 hover:underline transition">Change package</a>
                            </div>
                        </div>
                    </div>
                @endif

                <form wire:submit.prevent="submit" class="space-y-8 booking-form">
                    <style>
                        form.booking-form input,
                        form.booking-form select,
                        form.booking-form textarea {
                            border-color: #e2e8f0; /* slate-200 */
                            accent-color: #216417;
                        }
                        form.booking-form input:focus,
                        form.booking-form select:focus,
                        form.booking-form textarea:focus {
                            outline: none;
                            box-shadow: 0 0 0 3px rgba(33, 100, 23, 0.15);
                            border-color: #216417;
                        }
                        form.booking-form input[type=date],
                        form.booking-form select {
                            background: #f8fafc; /* slate-50 */
                            color: #0f172a; /* slate-900 */
                        }
                        form.booking-form input[type=date]::-webkit-calendar-picker-indicator {
                            filter: invert(30%) sepia(50%) saturate(800%) hue-rotate(80deg) brightness(95%) contrast(90%); /* Matches #216417 roughly */
                        }
                        form.booking-form select option,
                        form.booking-form select optgroup {
                            background: #ffffff;
                            color: #0f172a;
                        }
                        form.booking-form select option:hover,
                        form.booking-form select option:focus,
                        form.booking-form select option:checked {
                            background: #216417 !important;
                            color: #ffffff !important;
                        }
                        form.booking-form select::-ms-expand {
                            display: none;
                        }

                        .hide-scrollbar {
                            -ms-overflow-style: none;
                            scrollbar-width: none;
                        }
                        .hide-scrollbar::-webkit-scrollbar {
                            display: none;
                            width: 0;
                            height: 0;
                        }
                    </style>
                    @if ($step === 1)
                        <div class="relative">
                            <div class="space-y-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative inline-grid grid-cols-2 bg-slate-100 rounded-full p-1 border border-slate-200 shadow-inner w-full sm:w-auto">
                                        <div class="absolute top-1 bottom-1 w-[calc(50%-4px)] rounded-full bg-[#216417] shadow-sm transition-all duration-300 ease-in-out z-0 {{ $trip_type === 'round_trip' ? 'left-[calc(50%+2px)]' : 'left-1' }}"></div>
                                    
                                    <button type="button" wire:click="setTripType('one_way')" @disabled($prefilled_from_package || $tour_id) class="relative z-10 px-4 sm:px-8 py-2.5 text-sm font-bold rounded-full transition-colors duration-300 {{ $trip_type === 'one_way' ? 'text-white' : 'text-black hover:text-slate-900 font-extrabold' }} {{ ($prefilled_from_package || $tour_id) ? 'opacity-50 cursor-not-allowed' : '' }}">
                                        One-way Trip
                                    </button>
                                    
                                    <button type="button" wire:click="setTripType('round_trip')" @disabled($prefilled_from_package || $tour_id) class="relative z-10 px-4 sm:px-8 py-2.5 text-sm font-bold rounded-full transition-colors duration-300 {{ $trip_type === 'round_trip' ? 'text-white' : 'text-black hover:text-slate-900 font-extrabold' }} {{ ($prefilled_from_package || $tour_id) ? 'opacity-50 cursor-not-allowed' : '' }}">
                                        Round Trip
                                    </button>
                                </div>
                                
                                @if($prefilled_from_package || $tour_id)
                                    <span class="text-xs text-slate-500 font-medium">(Locked for tour packages)</span>
                                @endif
                            </div>

                            <div class="grid gap-4 grid-cols-2 lg:grid-cols-4 mt-4">
                                <div class="relative block" data-error="mode">
                                    <span class="text-black font-extrabold text-sm">Mode</span>
                                    <button type="button" wire:click.prevent="toggleModeDropdown" @if($prefilled_from_package || $isModePreselected) disabled @endif class="mt-2 flex h-12 w-full items-center justify-between rounded-xl border border-slate-300 bg-white px-4 py-3 text-left text-slate-900 shadow-sm transition hover:border-[#216417] focus:outline-none focus:ring-2 focus:ring-[#216417]/20 disabled:cursor-not-allowed disabled:bg-slate-50">
                                        <div class="flex items-center gap-2">
                                            @if($mode === 'ferry')
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l6-3 6 3 6-3v13l-6 3-6-3-6 3V7z"/></svg>
                                            @elseif($mode === 'airline')
                                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                            @endif
                                            <span>{{ $mode ? ucfirst($mode) : 'Select mode' }}</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.045l3.71-3.815a.75.75 0 111.08 1.04l-4.25 4.375a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    @if($isModePreselected)
                                        <span class="text-xs text-slate-500 font-medium mt-1">(Pre-selected from your booking link)</span>
                                    @endif
                                    @error('mode')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror

                                    @if ($showModeDropdown)
                                        <div class="absolute left-0 right-0 top-full mt-1 z-30 rounded-xl border border-slate-200 bg-white shadow-lg overflow-hidden">
                                            <div class="max-h-64 overflow-y-auto px-2 py-2 space-y-1">
                                                @php
                                                    $modeOptions = collect($this->getModeOptions());
                                                @endphp

                                                @foreach($modeOptions as $key => $label)
                                                    <button type="button" wire:click.prevent="selectMode('{{ $key }}')" class="w-full rounded-lg px-4 py-3 text-left text-slate-700 transition hover:bg-slate-50 hover:text-slate-900 {{ $mode === $key ? 'bg-slate-50 font-semibold' : '' }}">
                                                        <div class="flex items-center justify-between gap-3">
                                                            <div class="flex items-center gap-2">
                                                                @if($key === 'ferry')
                                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l6-3 6 3 6-3v13l-6 3-6-3-6 3V7z"/></svg>
                                                                @elseif($key === 'airline')
                                                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                                                @endif
                                                                <span>{{ $label }}</span>
                                                            </div>
                                                            @if($mode === $key)
                                                                <span class="rounded-full bg-[#db2777] px-2 py-0.5 text-[10px] font-bold text-white uppercase tracking-wider">Selected</span>
                                                            @endif
                                                        </div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="relative block" data-error="operator">
                                    <span class="text-black font-extrabold text-sm">Operator</span>
                                    @php
                                        $availableOperators = $this->operators;
                                        $operatorButtonsDisabled = $prefilled_from_package || blank($mode) || $isOperatorPreselected;
                                        $selectedOpLogo = $operator ? ($this->operatorLogos[$operator] ?? null) : null;
                                    @endphp
                                    <button type="button" wire:click.prevent="toggleOperatorDropdown" @disabled($operatorButtonsDisabled) class="mt-2 flex h-12 w-full items-center justify-between rounded-xl border border-slate-300 bg-white px-4 py-3 text-left text-slate-900 shadow-sm transition hover:border-[#216417] focus:outline-none focus:ring-2 focus:ring-[#216417]/20 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500">
                                        <div class="flex items-center gap-2 truncate">
                                            @if($selectedOpLogo)
                                                <div class="w-5 h-5 shrink-0 bg-white rounded flex items-center justify-center overflow-hidden">
                                                    <img src="{{ $selectedOpLogo }}" alt="{{ $operator }}" class="w-full h-full object-contain">
                                                </div>
                                            @endif
                                            <span class="truncate">{{ $operator ?: (blank($mode) ? 'Select mode first' : (empty($availableOperators) ? 'No operators available' : 'All operators')) }}</span>
                                        </div>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.045l3.71-3.815a.75.75 0 111.08 1.04l-4.25 4.375a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    @if($isOperatorPreselected)
                                        <span class="text-xs text-slate-500 font-medium mt-1 block">(Pre-selected from your booking link)</span>
                                    @endif

                                    @error('operator')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror

                                    @if ($showOperatorDropdown && !blank($mode) && !empty($availableOperators))
                                        <div class="absolute left-0 right-0 top-full mt-1 z-30 rounded-xl border border-slate-200 bg-white shadow-lg overflow-hidden">
                                            <div class="max-h-64 overflow-y-auto px-2 py-2 space-y-1">
                                                <button type="button" wire:click.prevent="selectOperator(null)" class="w-full rounded-lg px-4 py-3 text-left text-slate-700 transition hover:bg-slate-50 hover:text-slate-900 {{ blank($operator) ? 'bg-slate-50 font-semibold' : '' }}">
                                                    <div class="flex items-center justify-between gap-3">
                                                        <span>All operators</span>
                                                        @if(blank($operator))
                                                            <span class="rounded-full bg-[#216417] px-2 py-0.5 text-[10px] font-bold text-white uppercase tracking-wider">Selected</span>
                                                        @endif
                                                    </div>
                                                </button>
                                                @foreach($availableOperators as $op)
                                                    @php
                                                        $opLogo = $this->operatorLogos[$op] ?? null;
                                                    @endphp
                                                    <button type="button" wire:click.prevent="selectOperator('{{ $op }}')" class="w-full rounded-lg px-4 py-3 text-left text-slate-700 transition hover:bg-slate-50 hover:text-slate-900 {{ $operator === $op ? 'bg-slate-50 font-semibold' : '' }}">
                                                        <div class="flex items-center justify-between gap-3">
                                                            <div class="flex items-center gap-2">
                                                                @if($opLogo)
                                                                    <div class="w-6 h-6 shrink-0 bg-white rounded flex items-center justify-center overflow-hidden">
                                                                        <img src="{{ $opLogo }}" alt="{{ $op }}" class="w-full h-full object-contain">
                                                                    </div>
                                                                @endif
                                                                <span>{{ $op }}</span>
                                                            </div>
                                                            @if($operator === $op)
                                                                <span class="rounded-full bg-[#216417] px-2 py-0.5 text-[10px] font-bold text-white uppercase tracking-wider">Selected</span>
                                                            @endif
                                                        </div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                            <div class="col-span-2 lg:col-span-2">
                            <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                            <div class="relative block" data-error="origin">
                                <span class="text-black font-extrabold text-sm">Origin</span>
                                <button type="button" wire:click.prevent="toggleOriginDropdown" @if($prefilled_from_package || $mode === '') disabled @endif class="mt-2 flex h-12 w-full items-center justify-between rounded-xl border border-slate-300 bg-white px-4 py-3 text-left text-slate-900 shadow-sm transition hover:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500">
                                    <span>{{ $origin ?: ($mode === '' ? 'Select mode first' : 'Select origin') }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.045l3.71-3.815a.75.75 0 111.08 1.04l-4.25 4.375a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                @error('origin')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror

                                @if ($showOriginDropdown && $mode !== '')
                                    <div class="absolute left-0 right-0 top-full mt-1 z-30 max-h-96 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                                        <div class="p-3 border-b border-slate-100">
                                            <input type="text" wire:model.live.debounce.150ms="originSearch" placeholder="Search origins" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2 text-sm text-slate-900 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20" />
                                        </div>
                                        <div class="max-h-[14rem] overflow-y-auto hide-scrollbar px-2 py-2 space-y-1">
                                            @forelse($this->filteredOrigins as $originOption)
                                                <button type="button" wire:click.prevent="selectOrigin('{{ $originOption }}')" class="w-full rounded-lg px-4 py-2.5 text-left text-sm text-slate-700 transition hover:bg-slate-50 hover:text-slate-900 {{ $origin === $originOption ? 'bg-slate-50 font-semibold' : '' }}">
                                                    {{ $originOption }}
                                                </button>
                                            @empty
                                                <div class="px-4 py-6 text-center text-sm text-slate-500">
                                                    No origins match your search.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="relative block" data-error="destination">
                                <span class="text-black font-extrabold text-sm">Destination</span>
                                <button type="button" wire:click.prevent="toggleDestinationDropdown" @if($prefilled_from_package || $mode === '' || $origin === '') disabled @endif class="mt-2 flex h-12 w-full items-center justify-between rounded-xl border border-slate-300 bg-white px-4 py-3 text-left text-slate-900 shadow-sm transition hover:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500">
                                    <span>{{ $destination ?: (blank($origin) ? 'Select origin first' : 'Select destination') }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.045l3.71-3.815a.75.75 0 111.08 1.04l-4.25 4.375a.75.75 0 01-1.08 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                @error('destination')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror

                                @if ($showDestinationDropdown && filled($origin))
                                    <div class="absolute left-0 right-0 top-full mt-1 z-30 max-h-96 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg">
                                        <div class="p-3 border-b border-slate-100">
                                            <input type="text" wire:model.live.debounce.150ms="destinationSearch" placeholder="Search destinations" class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2 text-sm text-slate-900 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20" />
                                        </div>
                                        <div class="max-h-[14rem] overflow-y-auto hide-scrollbar px-2 py-2 space-y-1">
                                            @forelse($this->filteredDestinations as $destinationOption)
                                                <button type="button" wire:click.prevent="selectDestination('{{ $destinationOption }}')" class="w-full rounded-lg px-4 py-2.5 text-left text-sm text-slate-700 transition hover:bg-slate-50 hover:text-slate-900 {{ $destination === $destinationOption ? 'bg-slate-50 font-semibold' : '' }}">
                                                    {{ $destinationOption }}
                                                </button>
                                            @empty
                                                <div class="px-4 py-6 text-center text-sm text-slate-500">
                                                    No destinations match your search.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                @endif
                            </div>
                            </div>
                            </div>
                        </div>

                        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                            <div class="block" data-error="departure_date">
                                    <label class="block text-black font-extrabold text-sm">Departure Date</label>
                                        <div class="mt-2">
                                            @php
                                                $isPackage = $prefilled_from_package || $tour_id;
                                                $hasRouteSelected = $isPackage || (!empty($origin) && !empty($destination));
                                                $enabledDepartureDates = [];

                                                if ($isPackage) {
                                                    $enabledDepartureDates = $available_package_dates ?? [];
                                                } elseif ($hasRouteSelected) {
                                                    $enabledDepartureDates = $available_schedule_dates ?? [];
                                                }
                                            @endphp

                                            @if(!$hasRouteSelected)
                                                <livewire:date-picker wire:key="departure-no-route" wire:model.live="departure_date" field="departure_date" label="" :disabled="true" placeholder="Select origin & destination first" />
                                            @elseif(empty($enabledDepartureDates))
                                                <livewire:date-picker wire:key="departure-no-schedules" wire:model.live="departure_date" field="departure_date" label="" :disabled="true" placeholder="No schedules available" />
                                            @else
                                                <livewire:date-picker wire:key="departure-restricted-{{ md5(json_encode($enabledDepartureDates)) }}" wire:model.live="departure_date" field="departure_date" :enabled-dates="$enabledDepartureDates" :value="$departure_date" label="" min="{{ date('Y-m-d') }}" />
                                            @endif
                                        </div>
                                    @error('departure_date')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>

                                @if($trip_type === 'round_trip')
                                    <div class="block" data-error="return_date">
                                        <label class="block text-black font-extrabold text-sm">Return Date</label>
                                        <div class="mt-2">
                                            @php
                                                $enabledReturnDates = [];
                                                if ($hasRouteSelected) {
                                                    $enabledReturnDates = $available_return_schedule_dates ?? [];
                                                }
                                            @endphp

                                            @if(!$hasRouteSelected)
                                                <livewire:date-picker wire:key="return-no-route" wire:model.live="return_date" field="return_date" label="" :disabled="true" placeholder="Select origin & destination first" />
                                            @elseif(empty($enabledReturnDates))
                                                <livewire:date-picker wire:key="return-no-schedules" wire:model.live="return_date" field="return_date" label="" :disabled="true" placeholder="No return schedules available" />
                                            @else
                                                <livewire:date-picker wire:key="return-restricted-{{ md5(json_encode($enabledReturnDates)) }}" wire:model.live="return_date" field="return_date" :enabled-dates="$enabledReturnDates" label="" min="{{ $departure_date ?? date('Y-m-d') }}" />
                                            @endif
                                        </div>
                                        @error('return_date')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                @endif
                        </div>

                        <div class="space-y-4">
                            <div class="grid gap-4 rounded-xl border border-slate-200 bg-white p-6 shadow-sm lg:grid-cols-[1fr_auto] lg:items-center">
                                <div>
                                    <p class="text-slate-900 font-semibold">Travelers</p>
                                    <p class="mt-2 text-sm text-slate-600">Limit 8 travelers total for adults and children combined.</p>
                                </div>
                                <button type="button" wire:click.prevent="togglePassengerInfoModal" class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                                    Learn more
                                </button>
                            </div>

                            @if($mode === 'airline')
                                <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" data-error="adults">
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p class="text-slate-900 font-semibold">Adults</p>
                                                <p class="mt-1 text-sm text-slate-500">Age 11 and above</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button" wire:click.prevent="decrementAdults" @if($adults <= 1) disabled @endif class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 transition hover:border-[#db2777] hover:text-[#db2777] disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg></button>
                                                <span class="min-w-[3rem] text-center text-lg font-semibold text-slate-900">{{ $adults }}</span>
                                                <button type="button" wire:click.prevent="incrementAdults" @if($adults + $children + ($mode === 'airline' ? $minors + $infants : 0) >= 8) disabled @endif class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 transition hover:border-[#db2777] hover:text-[#db2777] disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400">+</button>
                                            </div>
                                        </div>
                                        @error('adults')<p class="mt-3 text-sm text-rose-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" data-error="minors">
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p class="text-slate-900 font-semibold">Minor</p>
                                                <p class="mt-1 text-sm text-slate-500">Age 7 to 11</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button" wire:click.prevent="decrementMinors" @if($minors <= 0) disabled @endif class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 transition hover:border-[#db2777] hover:text-[#db2777] disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg></button>
                                                <span class="min-w-[3rem] text-center text-lg font-semibold text-slate-900">{{ $minors }}</span>
                                                <button type="button" wire:click.prevent="incrementMinors" @if($adults + $children + ($mode === 'airline' ? $minors + $infants : 0) >= 8) disabled @endif class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 transition hover:border-[#db2777] hover:text-[#db2777] disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400">+</button>
                                            </div>
                                        </div>
                                        @error('minors')<p class="mt-3 text-sm text-rose-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" data-error="children">
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p class="text-slate-900 font-semibold">Child</p>
                                                <p class="mt-1 text-sm text-slate-500">Age 2 to 6</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button" wire:click.prevent="decrementChildren" @if($children <= 0) disabled @endif class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 transition hover:border-[#db2777] hover:text-[#db2777] disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg></button>
                                                <span class="min-w-[3rem] text-center text-lg font-semibold text-slate-900">{{ $children }}</span>
                                                <button type="button" wire:click.prevent="incrementChildren" @if($adults + $children + ($mode === 'airline' ? $minors + $infants : 0) >= 8) disabled @endif class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 transition hover:border-[#db2777] hover:text-[#db2777] disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400">+</button>
                                            </div>
                                        </div>
                                        @error('children')<p class="mt-3 text-sm text-rose-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" data-error="infants">
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p class="text-slate-900 font-semibold">Infants</p>
                                                <p class="mt-1 text-sm text-slate-500">0 to 23 months</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button" wire:click.prevent="decrementInfants" @if($infants <= 0) disabled @endif class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 transition hover:border-[#db2777] hover:text-[#db2777] disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg></button>
                                                <span class="min-w-[3rem] text-center text-lg font-semibold text-slate-900">{{ $infants }}</span>
                                                <button type="button" wire:click.prevent="incrementInfants" @if($infants >= $adults || $adults + $children + ($mode === 'airline' ? $minors + $infants : 0) >= 8) disabled @endif class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 transition hover:border-[#db2777] hover:text-[#db2777] disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400">+</button>
                                            </div>
                                        </div>
                                        @error('infants')<p class="mt-3 text-sm text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            @else
                                <div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
                                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" data-error="adults">
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p class="text-slate-900 font-semibold">Adults</p>
                                                <p class="mt-1 text-sm text-slate-500">Age 11 and above</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button" wire:click.prevent="decrementAdults" @if($adults <= 1) disabled @endif class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 transition hover:border-[#db2777] hover:text-[#db2777] disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg></button>
                                                <span class="min-w-[3rem] text-center text-lg font-semibold text-slate-900">{{ $adults }}</span>
                                                <button type="button" wire:click.prevent="incrementAdults" @if($adults + $children + ($mode === 'airline' ? $minors + $infants : 0) >= 8) disabled @endif class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 transition hover:border-[#db2777] hover:text-[#db2777] disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400">+</button>
                                            </div>
                                        </div>
                                        @error('adults')<p class="mt-3 text-sm text-rose-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" data-error="children">
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p class="text-slate-900 font-semibold">Minor</p>
                                                <p class="mt-1 text-sm text-slate-500">Age 2 to 11</p>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button" wire:click.prevent="decrementChildren" @if($children <= 0) disabled @endif class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 transition hover:border-[#db2777] hover:text-[#db2777] disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg></button>
                                                <span class="min-w-[3rem] text-center text-lg font-semibold text-slate-900">{{ $children }}</span>
                                                <button type="button" wire:click.prevent="incrementChildren" @if($adults + $children + ($mode === 'airline' ? $minors + $infants : 0) >= 8) disabled @endif class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-slate-700 transition hover:border-[#db2777] hover:text-[#db2777] disabled:cursor-not-allowed disabled:border-slate-200 disabled:text-slate-400">+</button>
                                            </div>
                                        </div>
                                        @error('children')<p class="mt-3 text-sm text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            @endif

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-700">
                                Total travelers: <span class="font-bold text-slate-900">{{ $adults + $children + ($mode === 'airline' ? $minors + $infants : 0) }}</span> / 8 
                            </div>

                            @if($mode === 'ferry' && stripos($operator ?? '', 'Starlite') !== false)
                                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                                    <div class="flex flex-wrap items-center justify-between gap-4">
                                        <div>
                                            <p class="text-slate-900 font-semibold">Vehicle booking</p>
                                            <p class="mt-1 text-sm text-slate-600">Add a vehicle to your ferry trip (optional).</p>
                                        </div>
                                        <label class="relative inline-flex cursor-pointer items-center gap-3">
                                            <input type="checkbox" wire:model.live="has_vehicle" class="peer sr-only" />
                                            <span class="relative h-7 w-12 shrink-0 rounded-full bg-slate-200 transition peer-checked:bg-[#db2777] peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#db2777]/30 after:absolute after:left-0.5 after:top-0.5 after:h-6 after:w-6 after:rounded-full after:bg-white after:shadow after:transition-transform after:content-[''] peer-checked:after:translate-x-5"></span>
                                            <span class="text-sm font-semibold text-slate-700">{{ $has_vehicle ? 'Yes' : 'No' }}</span>
                                        </label>
                                    </div>

                                    @if ($has_vehicle)
                                        <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-6" data-error="vehicle_booking_method">
                                            <div class="grid gap-4 {{ $vehicle_booking_method === 'brand_model' ? 'lg:grid-cols-5' : 'lg:grid-cols-4' }} sm:grid-cols-2">
                                                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                                    <p class="text-sm font-semibold text-slate-900">Classify Cargo by:</p>
                                                    <div class="mt-4 space-y-2">
                                                        <label class="flex items-center gap-3 rounded-full border px-4 py-3 text-sm text-slate-900 transition {{ $vehicle_booking_method === 'category' ? 'border-[#db2777] bg-[#db2777]/5' : 'border-slate-200 bg-white hover:border-[#db2777]/50' }}">
                                                            <input type="radio" wire:model.live="vehicle_booking_method" value="category" class="h-4 w-4 text-[#db2777] border-slate-300 focus:ring-[#db2777]" />
                                                            <span class="font-medium">Category</span>
                                                        </label>

                                                        <label class="flex items-center gap-3 rounded-full border px-4 py-3 text-sm text-slate-900 transition {{ $vehicle_booking_method === 'brand_model' ? 'border-[#db2777] bg-[#db2777]/5' : 'border-slate-200 bg-white hover:border-[#db2777]/50' }}">
                                                            <input type="radio" wire:model.live="vehicle_booking_method" value="brand_model" class="h-4 w-4 text-[#db2777] border-slate-300 focus:ring-[#db2777]" />
                                                            <span class="font-medium">Brand</span>
                                                        </label>
                                                    </div>
                                                    @error('vehicle_booking_method')<p class="mt-3 text-sm text-rose-600">{{ $message }}</p>@enderror
                                                </div>

                                                @if($vehicle_booking_method === 'category')
                                                    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                                        <label class="text-sm font-semibold text-slate-900">Category *</label>
                                                        <select wire:model.live="selected_vehicle_rate_id" class="mt-3 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20">
                                                            <option value="">Select category</option>
                                                            @foreach($vehicleRateCatalog as $rate)
                                                                <option value="{{ $rate->id }}">{{ $rate->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('selected_vehicle_rate_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                                    </div>
                                                @else
                                                    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                                        <label class="text-sm font-semibold text-slate-900">Brand *</label>
                                                        <select wire:model.live="selected_brand_id" class="mt-3 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20">
                                                            <option value="">Select brand</option>
                                                            @foreach($vehicleBrandCatalog as $brand)
                                                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('selected_brand_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                                    </div>

                                                    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                                        <label class="text-sm font-semibold text-slate-900">Model *</label>
                                                        <select wire:model.live="selected_model_id" class="mt-3 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20" @if($vehicleModelCatalog->isEmpty()) disabled @endif>
                                                            <option value="">Select model</option>
                                                            @foreach($vehicleModelCatalog as $model)
                                                                <option value="{{ $model->id }}">{{ $model->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('selected_model_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                                    </div>
                                                @endif

                                                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                                    <label class="text-sm font-semibold text-slate-900">Plate Number *</label>
                                                    <input type="text" wire:model.blur="vehicle_plate_number" class="mt-3 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20" placeholder="e.g., ABC 1234" />
                                                    @error('vehicle_plate_number')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                                </div>

                                                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                                    <p class="text-sm font-semibold text-slate-900">Cargo Rate</p>
                                                    <div class="mt-3 flex h-14 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-4 text-lg font-bold text-slate-900">
                                                        &#8369;{{ number_format($vehicle_price ?? 0, 2) }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-6">
                                                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                                    <span class="text-sm font-semibold text-slate-900">Driver name</span>
                                                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                                                        <div>
                                                            <label class="text-xs font-medium text-slate-600">First Name <span class="text-rose-500">*</span></label>
                                                            <input type="text" wire:model.blur="driver_first_name" class="mt-1 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20" placeholder="e.g., Juan" />
                                                            @error('driver_first_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                                        </div>
                                                        <div>
                                                            <label class="text-xs font-medium text-slate-600">Middle Name <span class="text-slate-400">(optional)</span></label>
                                                            <input type="text" wire:model.blur="driver_middle_name" class="mt-1 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20" placeholder="e.g., Dela" />
                                                        </div>
                                                        <div>
                                                            <label class="text-xs font-medium text-slate-600">Last Name <span class="text-rose-500">*</span></label>
                                                            <input type="text" wire:model.blur="driver_last_name" class="mt-1 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20" placeholder="e.g., Cruz" />
                                                            @error('driver_last_name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3 grid gap-4 sm:grid-cols-1">
                                                <label class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                                    <span class="text-sm font-semibold text-slate-900">Driver birthday</span>
                                                    <input type="date" wire:model.blur="driver_birthday" class="mt-3 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-slate-900 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20" />
                                                    @error('driver_birthday')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if ($showPassengerInfoModal)
                                <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
                                    <div class="relative w-full max-w-2xl overflow-hidden rounded-2xl bg-white p-6 shadow-2xl">
                                        <button type="button" wire:click.prevent="togglePassengerInfoModal" class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-slate-100">
                                            <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span class="sr-only">Close</span>
                                        </button>

                                        <h2 class="text-xl font-bold text-slate-900">Passenger limits and guidance</h2>
                                        <p class="mt-3 text-slate-600">You can book up to 8 travelers total. This includes both adults and minors combined. Any discounts are applied per traveler on the next step.</p>
                                        <ul class="mt-4 space-y-3 text-slate-700">
                                            <li class="flex gap-3">
                                                <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#db2777]/10 text-[#db2777] font-bold text-xs">1</span>
                                                <span>Adults are counted separately from minors, but both count toward the same 8-person total.</span>
                                            </li>
                                            <li class="flex gap-3">
                                                <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#db2777]/10 text-[#db2777] font-bold text-xs">2</span>
                                                <span>Minors aged 2 to 11 are still part of the booking capacity limit.</span>
                                            </li>
                                            <li class="flex gap-3">
                                                <span class="mt-1 inline-flex h-6 w-6 items-center justify-center rounded-full bg-[#db2777]/10 text-[#db2777] font-bold text-xs">3</span>
                                                <span>Use the buttons to update counts. The form prevents totals above 8.</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            @endif

                            @if ($showMinorAgeWarning && $mode !== 'airline')
                                <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
                                    <div class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white p-6 shadow-2xl">
                                        <button type="button" wire:click.prevent="closeMinorAgeWarning" class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-slate-100">
                                            <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            <span class="sr-only">Close</span>
                                        </button>

                                        <h2 class="text-xl font-bold text-slate-900">Minor age reminder</h2>
                                        <p class="mt-3 text-slate-600">23 months and under will be issued upon arrival at the port/airport.</p>
                                        <div class="mt-6 flex justify-end">
                                            <button type="button" wire:click.prevent="closeMinorAgeWarning" class="inline-flex rounded-full bg-[#db2777] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#be185d]">Close</button>
                                        </div>
                                    </div>
                                </div>
                            @endif


                    @endif

                    @if ($step === 2 && !$tour_id && !$prefilled_from_package)
                        <div class="space-y-4" wire:poll.60s>
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8" style="align-items: start;">
                                {{-- Left Column: Schedules and transport classes/accommodations --}}
                                <div class="lg:col-span-7 xl:col-span-8 space-y-6 min-w-0">
                                    <p class="text-black font-bold">Choose the schedule that works best for your trip.</p>
                                    @if($this->baggageRules)
                                        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                            <button type="button" wire:click.prevent="$toggle('showBaggageRules')" class="flex items-center justify-between w-full text-left">
                                                <div class="flex items-center gap-3">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#216417]" viewBox="0 0 24 24" fill="currentColor">
                                                        <path d="M8 21h8v1a1 1 0 01-1 1H9a1 1 0 01-1-1v-1zm3-2h2v1h-2v-1zM4 7a3 3 0 013-3h10a3 3 0 013 3v8a3 3 0 01-3 3H7a3 3 0 01-3-3V7zm3-1a1 1 0 00-1 1v8a1 1 0 001 1h10a1 1 0 001-1V7a1 1 0 00-1-1H7z"/>
                                                    </svg>
                                                    <div>
                                                        <p class="font-semibold text-slate-900">Baggage Rules</p>
                                                        <p class="text-sm text-slate-600">{{ \Illuminate\Support\Arr::get($this->baggageRules, 'name', 'View baggage allowances and fees') }}</p>
                                                    </div>
                                                </div>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 transition-transform {{ $showBaggageRules ? 'rotate-180' : '' }}" viewBox="0 0 24 24" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 01-1.06 0l-4.5-4.5a.75.75 0 011.06-1.06L12 14.44l3.97-3.97a.75.75 0 111.06 1.06l-4.5 4.5z" clip-rule="evenodd"/>
                                                </svg>
                                            </button>
                                            @if($showBaggageRules)
                                                <div class="mt-4 border-t border-slate-200 pt-4">
                                                    <p class="text-xs text-slate-500 mb-3">Last verified: {{ \Illuminate\Support\Arr::get($this->baggageRules, 'meta.last_verified', 'July 23, 2026') }}</p>
                                                    @if(\Illuminate\Support\Arr::get($this->baggageRules, 'carry_on'))
                                                        <div class="mb-4">
                                                            <h4 class="font-semibold text-slate-900 mb-2">Carry-on Baggage</h4>
                                                            <p class="text-sm text-slate-600 mb-2">{{ \Illuminate\Support\Arr::get($this->baggageRules, 'carry_on.description', '') }}</p>
                                                            <ul class="list-disc list-inside text-sm text-slate-600 space-y-1">
                                                                @if(\Illuminate\Support\Arr::get($this->baggageRules, 'carry_on.combined_weight_kg'))
                                                                    <li>Combined weight: {{ \Illuminate\Support\Arr::get($this->baggageRules, 'carry_on.combined_weight_kg') }}kg</li>
                                                                @endif
                                                                @if(\Illuminate\Support\Arr::get($this->baggageRules, 'carry_on.hand_carry_size_cm'))
                                                                    <li>Hand-carry size: {{ \Illuminate\Support\Arr::get($this->baggageRules, 'carry_on.hand_carry_size_cm') }}</li>
                                                                @endif
                                                                @if(\Illuminate\Support\Arr::get($this->baggageRules, 'carry_on.personal_item_size_cm'))
                                                                    <li>Personal item size: {{ \Illuminate\Support\Arr::get($this->baggageRules, 'carry_on.personal_item_size_cm') }}</li>
                                                                @endif
                                                                @if(\Illuminate\Support\Arr::get($this->baggageRules, 'carry_on.note'))
                                                                    <li>{{ \Illuminate\Support\Arr::get($this->baggageRules, 'carry_on.note') }}</li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    @endif
                                                    @if(\Illuminate\Support\Arr::get($this->baggageRules, 'checked_baggage'))
                                                        <div>
                                                            <h4 class="font-semibold text-slate-900 mb-2">Baggage Reminders</h4>
                                                            @if(\Illuminate\Support\Arr::get($this->baggageRules, 'checked_baggage.free_allowance_kg'))
                                                                <p class="text-sm text-slate-600 mb-2">Personal Baggage: {{ \Illuminate\Support\Arr::get($this->baggageRules, 'checked_baggage.free_allowance_kg') }}kg</p>
                                                            @endif
                                                            @if(\Illuminate\Support\Arr::get($this->baggageRules, 'checked_baggage.max_single_bag_weight_kg'))
                                                                <p class="text-sm text-slate-600 mb-2">Max single bag weight: {{ \Illuminate\Support\Arr::get($this->baggageRules, 'checked_baggage.max_single_bag_weight_kg') }}kg</p>
                                                            @endif
                                                            @if(\Illuminate\Support\Arr::get($this->baggageRules, 'checked_baggage.fare_bundles'))
                                                                <div class="overflow-x-auto">
                                                                    <table class="w-full text-sm text-slate-600">
                                                                        <thead class="text-slate-900 font-semibold">
                                                                            <tr>
                                                                                <th class="px-2 py-2 text-left">Fare Bundle</th>
                                                                                <th class="px-2 py-2 text-right">Free Checked</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @foreach(\Illuminate\Support\Arr::get($this->baggageRules, 'checked_baggage.fare_bundles', []) as $bundle)
                                                                                <tr class="border-t border-slate-200">
                                                                                    <td class="px-2 py-2">{{ \Illuminate\Support\Arr::get($bundle, 'name') }}</td>
                                                                                    <td class="px-2 py-2 text-right">{{ \Illuminate\Support\Arr::get($bundle, 'free_checked_kg') }}kg</td>
                                                                                </tr>
                                                                            @endforeach
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            @endif
                                                            @if(\Illuminate\Support\Arr::get($this->baggageRules, 'checked_baggage.prepaid_online'))
                                                                <p class="text-sm text-slate-600 mt-2">Prepaid online (domestic 20kg): {{ \Illuminate\Support\Arr::get($this->baggageRules, 'checked_baggage.prepaid_online.domestic_20kg_php', '') }}</p>
                                                            @endif
                                                            @if(\Illuminate\Support\Arr::get($this->baggageRules, 'checked_baggage.excess_rate_php_per_kg'))
                                                                <p class="text-sm text-slate-600 mt-2">Excess rate: &#8369;{{ \Illuminate\Support\Arr::get($this->baggageRules, 'checked_baggage.excess_rate_php_per_kg') }}/kg</p>
                                                            @endif
                                                            <p class="text-xs text-slate-500 mt-3">Rates subject to change - confirm at booking</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    @if($trip_type === 'round_trip')
                                        <x-schedule-carousel 
                                            title="Departure Trip"
                                            subtitle="Select your preferred departure trip vessel"
                                            :origin="$origin"
                                            :destination="$destination"
                                            :schedules="$availableSchedules"
                                            selectedId="{{ $selected_schedule_id }}"
                                            selectedAccommodationId="{{ $selected_schedule_accommodation_id }}"
                                            selectedClassId="{{ $selected_transport_class_id }}"
                                            selectMethod="selectSchedule"
                                            selectAccommodationMethod="selectScheduleAccommodation"
                                            selectClassMethod="selectTransportClass"
                                            :mode="$mode"
                                        />
                                        
                                        <div class="border-t border-slate-200 my-8"></div>
                                        
                                        <x-schedule-carousel 
                                            title="Returning Trip"
                                            subtitle="Select your preferred returning trip vessel"
                                            :origin="$destination"
                                            :destination="$origin"
                                            :schedules="$availableReturnSchedules"
                                            selectedId="{{ $selected_return_schedule_id }}"
                                            selectedAccommodationId="{{ $selected_return_schedule_accommodation_id }}"
                                            selectedClassId="{{ $selected_return_transport_class_id }}" 
                                            selectMethod="selectReturnSchedule"
                                            selectAccommodationMethod="selectReturnScheduleAccommodation"
                                            selectClassMethod="selectReturnTransportClass"
                                            :mode="$mode"
                                        />
                                    @else
                                        <x-schedule-carousel 
                                            title="Departure Trip"
                                            subtitle="Select your preferred departure trip vessel"
                                            :origin="$origin"
                                            :destination="$destination"
                                            :schedules="$availableSchedules"
                                            selectedId="{{ $selected_schedule_id }}"
                                            selectedAccommodationId="{{ $selected_schedule_accommodation_id }}"
                                            selectedClassId="{{ $selected_transport_class_id }}"
                                            selectMethod="selectSchedule"
                                            selectAccommodationMethod="selectScheduleAccommodation"
                                            selectClassMethod="selectTransportClass"
                                            :mode="$mode"
                                        />
                                    @endif

                                        @if($mode === 'airline')
                                            {{-- Extra Baggage section (bottom of schedule phase) --}}
                                            <div class="mt-8 border-t border-slate-200 pt-6">
                                                <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
                                                    <div class="flex flex-wrap items-center justify-between gap-4">
                                                        <div class="flex items-center gap-3">
                                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-[#216417]">
                                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                                </svg>
                                                            </div>
                                                            <div>
                                                                <p class="text-slate-900 font-bold text-lg">Prepaid Extra Baggage</p>
                                                                <p class="text-xs text-slate-500">Add prepaid check-in baggage per passenger for your flight</p>
                                                            </div>
                                                        </div>
                                                        <label class="relative inline-flex cursor-pointer items-center gap-3">
                                                            <input type="checkbox" wire:model.live="hasExtraBaggage" class="peer sr-only">
                                                            <span class="relative h-7 w-12 shrink-0 rounded-full bg-slate-200 transition peer-checked:bg-[#216417] peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#216417]/30 after:absolute after:left-0.5 after:top-0.5 after:h-6 after:w-6 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-5"></span>
                                                            <span class="text-sm font-bold text-slate-900">{{ $hasExtraBaggage ? 'Extra Baggage Added' : 'No Extra Baggage' }}</span>
                                                        </label>
                                                    </div>

                                                    @if($hasExtraBaggage)
                                                        @php
                                                            $baggageRates = $this->getAirlineExtraBaggageRates();
                                                            $currentAirlineKey = $selected_baggage_airline ?: $this->autoDetectBaggageAirline();
                                                            $selectedAirlineData = $baggageRates[$currentAirlineKey] ?? reset($baggageRates);
                                                            $passengersCount = max(1, count($passengers));
                                                        @endphp
                                                        <div class="mt-5 border-t border-slate-200 pt-5">
                                                            <div class="flex flex-wrap items-center justify-between gap-4">
                                                                <div>
                                                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Select Extra Baggage (kg)</label>
                                                                    <select
                                                                        wire:change="selectBaggageOption($event.target.options[$event.target.selectedIndex].dataset.weight, $event.target.value)"
                                                                        class="w-full sm:w-64 rounded-xl border border-slate-300 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-900 shadow-sm focus:border-[#216417] focus:outline-none focus:ring-2 focus:ring-[#216417]/20"
                                                                    >
                                                                        @foreach($selectedAirlineData['options'] as $opt)
                                                                            @php
                                                                                $isSelected = ($extra_baggage_weight === $opt['weight']);
                                                                            @endphp
                                                                            <option
                                                                                value="{{ $opt['price'] }}"
                                                                                data-weight="{{ $opt['weight'] }}"
                                                                                {{ $isSelected ? 'selected' : '' }}
                                                                            >
                                                                                {{ $opt['weight'] }} &mdash; &#8369;{{ number_format($opt['price']) }} per pax
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <div class="rounded-xl bg-emerald-50/70 border border-emerald-200 px-4 py-3 text-right">
                                                                    <div class="text-xs text-slate-600">
                                                                        Selected Allowance:
                                                                        <span class="rounded-full bg-[#216417] px-2.5 py-0.5 text-xs font-extrabold text-white">{{ $extra_baggage_weight ?: '20 kg' }}</span>
                                                                    </div>
                                                                    <div class="text-xs text-slate-500 mt-1">
                                                                        Rate: &#8369;{{ number_format($extra_baggage_price ?? 0) }} per passenger &times; {{ $passengersCount }} traveler{{ $passengersCount > 1 ? 's' : '' }}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="mt-4 rounded-xl bg-[#216417]/5 border border-[#216417]/20 p-4">
                                                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                                                    <div class="flex items-center gap-2">
                                                                        <svg class="h-5 w-5 text-[#216417]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                        </svg>
                                                                        <span class="text-xs font-semibold text-slate-700">Applied to all {{ $passengersCount }} passenger(s) on your flight.</span>
                                                                    </div>
                                                                    <div class="text-left sm:text-right border-t sm:border-t-0 border-emerald-200/60 pt-3 sm:pt-0">
                                                                        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Added Baggage Fee</div>
                                                                        <div class="text-xl font-extrabold text-[#216417] mt-0.5">
                                                                            +&#8369;{{ number_format($this->getExtraBaggageTotalPrice(), 2) }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Promo Ticket section --}}
                                        @php
                                            $activePromo = $this->getActivePromoTicket();
                                        @endphp

                                        @if($activePromo)
                                            @if($mode === 'airline')
                                                {{-- Airline: per-passenger promo info banner --}}
                                                @php
                                                    $slotsSelected = $this->getSelectedPromoPassengerCount();
                                                    $slotsRemaining = $this->getAvailablePromoSlotsRemaining();
                                                @endphp
                                                <div class="mt-6 border-t border-slate-200 pt-6">
                                                    <div class="rounded-2xl border-2 border-[#db2777] bg-gradient-to-r from-[#db2777]/5 to-[#db2777]/10 p-5 shadow-sm">
                                                        <div class="flex flex-wrap items-center justify-between gap-4">
                                                            <div>
                                                                <p class="font-bold text-lg text-slate-900 flex items-center gap-2">
                                                                    <span class="text-xl">&#x2728;</span> Promotional Fare Available!
                                                                </p>
                                                                <p class="mt-1 text-sm text-slate-600">
                                                                    Promo price: <span class="font-bold text-[#db2777]">&#8369;{{ number_format($activePromo->promo_price, 2) }}</span>
                                                                    &nbsp;&middot;&nbsp;
                                                                    <span class="font-semibold">{{ $activePromo->remaining_quantity }}</span> ticket(s) remaining
                                                                </p>
                                                                <p class="mt-2 text-xs text-slate-500">Select which passenger(s) below will use this promotional fare.</p>
                                                            </div>
                                                            @if($slotsSelected > 0)
                                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#db2777] px-4 py-1.5 text-sm font-semibold text-white shadow">
                                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                                    {{ $slotsSelected }} of {{ $activePromo->remaining_quantity + $slotsSelected }} selected
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                {{-- Ferry / other modes: keep the original booking-level toggle --}}
                                                <div class="mt-6 border-t border-slate-200 pt-6">
                                                    <div class="flex flex-wrap items-center justify-between gap-4 rounded-xl border-2 border-[#db2777] bg-[#db2777]/5 p-5 shadow-sm">
                                                        <div>
                                                            <p class="text-slate-900 font-bold text-lg">Promotional Ticket Available!</p>
                                                            <p class="mt-1 text-sm text-slate-600">
                                                                Promo price: <span class="font-bold text-[#db2777]">&#8369;{{ number_format($activePromo->promo_price, 2) }}</span>
                                                                &nbsp;|&nbsp; Remaining: {{ $activePromo->remaining_quantity }} of {{ $activePromo->quantity_available }}
                                                            </p>
                                                        </div>
                                                        <label class="relative inline-flex cursor-pointer items-center gap-3">
                                                            <input type="checkbox" wire:model.live="use_promo_ticket" class="peer sr-only">
                                                            <span class="relative h-7 w-12 shrink-0 rounded-full bg-slate-200 transition peer-checked:bg-[#db2777] peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#db2777]/30 after:absolute after:left-0.5 after:top-0.5 after:h-6 after:w-6 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-5"></span>
                                                            <span class="text-sm font-semibold text-slate-700">{{ $use_promo_ticket ? 'Use Promo Ticket' : 'Regular Ticket' }}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                </div>

                                {{-- Right Column: Seat Map / Ferry summary --}}
                                @php
                                    $selectedSchedule = collect($availableSchedules)->firstWhere('id', $selected_schedule_id);
                                    $selectedClass = $selectedSchedule && $selected_transport_class_id 
                                        ? collect($selectedSchedule['transport_classes'])->firstWhere(fn($c) => (int)($c['pivot_id'] ?? $c['id']) === (int)$selected_transport_class_id)
                                        : null;
                                @endphp
                                <div class="lg:col-span-5 xl:col-span-4 self-start lg:sticky lg:top-6 space-y-6 min-w-0">
                                    @if($mode === 'airline')
                                        {{-- Airline booking details little ticket --}}
                                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                                            <div class="flex flex-wrap items-start justify-between gap-4">
                                                <div>
                                                    <h3 class="text-lg font-bold text-slate-900">Airline booking details</h3>
                                                    <p class="mt-1 text-sm text-slate-600 font-medium">Review your selected flight route and class choice here.</p>
                                                </div>
                                                <span class="rounded-full bg-slate-100 border border-slate-200 px-4 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-700">Airline trip</span>
                                            </div>

                                            <div class="mt-6 space-y-4">
                                                @if($selectedSchedule)
                                                    <div class="rounded-xl border border-[#db2777]/20 bg-[#db2777]/5 p-4 shadow-sm">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <p class="text-slate-900 font-bold">Selected flight @if($trip_type === 'round_trip') (Departure) @endif</p>
                                                        </div>
                                                        <p class="text-sm text-[#db2777] font-semibold">{{ $selectedSchedule['service'] }} &middot; {{ $selectedSchedule['departure'] }} - {{ $selectedSchedule['arrival'] }}</p>
                                                        <p class="mt-1 text-sm text-slate-600">Duration: {{ $selectedSchedule['duration'] }}</p>
                                                    </div>

                                                    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                                        <p class="text-slate-900 font-bold">Travel Class @if($trip_type === 'round_trip') (Departure) @endif</p>
                                                        @if($selectedClass)
                                                            <p class="mt-2 text-sm text-slate-700 font-semibold">{{ $selectedClass['name'] }}</p>
                                                            <p class="mt-1 text-sm text-slate-900 font-extrabold">Total per person: &#8369;{{ number_format($selectedSchedule['price'] + $selectedClass['price'], 2) }}</p>
                                                            @if(!empty($selectedClass['description']))
                                                                <p class="mt-3 text-sm text-slate-500 italic">{{ $selectedClass['description'] }}</p>
                                                            @endif
                                                        @else
                                                            <p class="mt-2 text-sm text-slate-500 italic">Select a travel class on the left to continue.</p>
                                                        @endif
                                                    </div>

                                                    @if($trip_type === 'round_trip')
                                                        @php $selectedReturnSchedule = collect($availableReturnSchedules)->firstWhere('id', $selected_return_schedule_id); @endphp
                                                        @if($selectedReturnSchedule)
                                                            <div class="rounded-xl border border-[#db2777]/20 bg-[#db2777]/5 p-4 shadow-sm mt-4">
                                                                <div class="flex items-center justify-between mb-2">
                                                                    <p class="text-slate-900 font-bold">Selected flight (Returning)</p>
                                                                </div>
                                                                <p class="text-sm text-[#db2777] font-semibold">{{ $selectedReturnSchedule['service'] }} &middot; {{ $selectedReturnSchedule['departure'] }} - {{ $selectedReturnSchedule['arrival'] }}</p>
                                                                <p class="mt-1 text-sm text-slate-600">Duration: {{ $selectedReturnSchedule['duration'] }}</p>
                                                            </div>

                                                            @php $selectedReturnClass = collect($selectedReturnSchedule['transport_classes'] ?? [])->firstWhere(fn($c) => (int)($c['pivot_id'] ?? $c['id']) === (int)$selected_return_transport_class_id); @endphp

                                                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                                                <p class="text-slate-900 font-bold">Travel Class (Returning)</p>
                                                                @if($selectedReturnClass)
                                                                    <p class="mt-2 text-sm text-slate-700 font-semibold">{{ $selectedReturnClass['name'] }}</p>
                                                                    <p class="mt-1 text-sm text-slate-900 font-extrabold">Total per person: &#8369;{{ number_format($selectedReturnSchedule['price'] + $selectedReturnClass['price'], 2) }}</p>
                                                                    @if(!empty($selectedReturnClass['description']))
                                                                        <p class="mt-3 text-sm text-slate-500 italic">{{ $selectedReturnClass['description'] }}</p>
                                                                    @endif
                                                                @else
                                                                    <p class="mt-2 text-sm text-slate-500 italic">Select a travel class on the left to continue.</p>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm text-center">
                                                                <p class="text-slate-500 text-sm italic">Select a returning flight on the left to view details.</p>
                                                            </div>
                                                        @endif
                                                    @endif
                                                @else
                                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-center shadow-sm">
                                                        <p class="text-slate-500 text-sm italic">Select a flight schedule on the left to view details.</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>


                                    
@elseif($mode === 'ferry')
                                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm order-3 lg:order-1">
                                            <div class="flex flex-wrap items-start justify-between gap-4">
                                                <div>
                                                    <h3 class="text-lg font-bold text-slate-900">Ferry booking details</h3>
                                                    <p class="mt-1 text-sm text-slate-600 font-medium">Review your selected ferry route and accommodation choice here.</p>
                                                </div>
                                                <span class="rounded-full bg-slate-100 border border-slate-200 px-4 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-700">Ferry trip</span>
                                            </div>

                                            <div class="mt-6 space-y-4">
                                                @if($selectedSchedule)
                                                    <div class="rounded-xl border border-[#db2777]/20 bg-[#db2777]/5 p-4 shadow-sm">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <p class="text-slate-900 font-bold">Selected schedule @if($trip_type === 'round_trip') (Departure) @endif</p>
                                                        </div>
                                                        <p class="text-sm text-[#db2777] font-semibold">{{ $selectedSchedule['service'] }} &middot; {{ $selectedSchedule['departure'] }} - {{ $selectedSchedule['arrival'] }}</p>
                                                        <p class="mt-1 text-sm text-slate-600">Duration: {{ $selectedSchedule['duration'] }}</p>
                                                    </div>

                                                    @php $selectedAccommodation = $selectedSchedule && $selected_transport_class_id ? collect($selectedSchedule['transport_classes'] ?? [])->firstWhere(fn($c) => (int)($c['pivot_id'] ?? $c['id']) === (int)$selected_transport_class_id) : null; @endphp

                                                    <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                                        <p class="text-slate-900 font-bold">Travel Class @if($trip_type === 'round_trip') (Departure) @endif</p>
                                                        @if($selectedAccommodation)
                                                            <p class="mt-2 text-sm text-slate-700 font-semibold">{{ $selectedAccommodation['name'] }}</p>
                                                            <p class="mt-1 text-sm text-slate-900 font-extrabold">Total per person: &#8369;{{ number_format($selectedSchedule['price'] + $selectedAccommodation['price'], 2) }}</p>
                                                            @if(!empty($selectedAccommodation['description']))
                                                                <p class="mt-3 text-sm text-slate-500 italic">{{ $selectedAccommodation['description'] }}</p>
                                                            @endif
                                                        @else
                                                            <p class="mt-2 text-sm text-slate-500 italic">Select an accommodation on the left to continue.</p>
                                                        @endif
                                                    </div>
                                                    
                                                    @if($trip_type === 'round_trip')
                                                        @php $selectedReturnSchedule = collect($availableReturnSchedules)->firstWhere('id', $selected_return_schedule_id); @endphp
                                                        @if($selectedReturnSchedule)
                                                            <div class="rounded-xl border border-[#db2777]/20 bg-[#db2777]/5 p-4 shadow-sm mt-4">
                                                                <div class="flex items-center justify-between mb-2">
                                                                    <p class="text-slate-900 font-bold">Selected schedule (Returning)</p>
                                                                </div>
                                                                <p class="text-sm text-[#db2777] font-semibold">{{ $selectedReturnSchedule['service'] }} &middot; {{ $selectedReturnSchedule['departure'] }} - {{ $selectedReturnSchedule['arrival'] }}</p>
                                                                <p class="mt-1 text-sm text-slate-600">Duration: {{ $selectedReturnSchedule['duration'] }}</p>
                                                            </div>

                                                            @php $selectedReturnAccommodation = $selectedReturnSchedule && $selected_return_transport_class_id ? collect($selectedReturnSchedule['transport_classes'] ?? [])->firstWhere(fn($c) => (int)($c['pivot_id'] ?? $c['id']) === (int)$selected_return_transport_class_id) : null; @endphp

                                                            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                                                                <p class="text-slate-900 font-bold">Travel Class (Returning)</p>
                                                                @if($selectedReturnAccommodation)
                                                                    <p class="mt-2 text-sm text-slate-700 font-semibold">{{ $selectedReturnAccommodation['name'] }}</p>
                                                                    <p class="mt-1 text-sm text-slate-900 font-extrabold">Total per person: &#8369;{{ number_format($selectedReturnSchedule['price'] + $selectedReturnAccommodation['price'], 2) }}</p>
                                                                    @if(!empty($selectedReturnAccommodation['description']))
                                                                        <p class="mt-3 text-sm text-slate-500 italic">{{ $selectedReturnAccommodation['description'] }}</p>
                                                                    @endif
                                                                @else
                                                                    <p class="mt-2 text-sm text-slate-500 italic">Select an accommodation on the left to continue.</p>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm text-center">
                                                                <p class="text-slate-500 text-sm italic">Select a returning schedule on the left to view details.</p>
                                                            </div>
                                                        @endif
                                                    @endif
                                                @else
                                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-center shadow-sm">
                                                        <p class="text-slate-500 text-sm italic">Select a ferry schedule on the left to view details.</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-8 text-center shadow-sm">
                                            <h3 class="text-lg font-bold text-slate-900 mb-2">Pick a schedule to continue</h3>
                                            <p class="text-slate-600 font-medium">When you select a schedule, the next step will show the available class or accommodation options.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($step === 3)
                        <div class="space-y-4">
                            <div class="space-y-2">
                                <p class="text-black font-bold">Each traveler can have their own discount, if eligible. Name is required, discount is optional.</p>
                                @if($infants > 0)
                                    <p class="text-sm text-slate-600 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">✈️ Infants typically travel free and do not require discount information.</p>
                                @endif
                            </div>

                            @php
                                $typeLabels = ['adult' => 'Adult', 'child' => 'Child', 'minor' => 'Minor', 'infant' => 'Infant', 'driver' => 'Driver'];
                                $countByType = [];
                                $availableDiscounts = $discounts->reject(function ($discount) {
                                    return str_contains(strtolower($discount->name), 'infant');
                                });
                            @endphp

                            @foreach($passengers as $index => $passenger)
                                @if($passenger['type'] !== 'infant')
                                @php
                                    $countByType[$passenger['type']] = ($countByType[$passenger['type']] ?? 0) + 1;
                                @endphp
                                <div wire:key="passenger-{{ $index }}" class="flex flex-col lg:flex-row gap-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" data-error="passengers">
                                    <div class="flex-shrink-0 lg:w-32 lg:pt-8">
                                        <div class="rounded-full bg-[#db2777] px-4 py-2 text-center text-xs uppercase tracking-wider font-bold text-white shadow-sm inline-block w-full">
                                            @if($passenger['type'] === 'driver')
                                                {{ $typeLabels[$passenger['type']] }}
                                            @else
                                                {{ $typeLabels[$passenger['type']] }} {{ $countByType[$passenger['type']] }}
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="flex-grow grid gap-4 sm:grid-cols-1 md:grid-cols-2 lg:items-end">

                                    <label class="block min-w-0">
                                        <span class="text-slate-900 font-bold text-sm">Name</span>
                                        <div class="mt-3 grid gap-2 sm:grid-cols-3">
                                            <div>
                                                <input type="text" wire:model.blur="passengers.{{ $index }}.first_name" {{ $passenger['type'] === 'driver' ? 'readonly' : '' }} class="{{ $passenger['type'] === 'driver' ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : '' }} block w-full rounded-xl border border-slate-300 px-4 py-3 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20 transition-all" placeholder="First" />
                                                @error('passengers.' . $index . '.first_name')<p class="mt-2 text-xs text-rose-600">Required</p>@enderror
                                            </div>
                                            <div>
                                                <input type="text" wire:model.blur="passengers.{{ $index }}.middle_name" {{ $passenger['type'] === 'driver' ? 'readonly' : '' }} class="{{ $passenger['type'] === 'driver' ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : '' }} block w-full rounded-xl border border-slate-300 px-4 py-3 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20 transition-all" placeholder="Middle" />
                                            </div>
                                            <div>
                                                <input type="text" wire:model.blur="passengers.{{ $index }}.last_name" {{ $passenger['type'] === 'driver' ? 'readonly' : '' }} class="{{ $passenger['type'] === 'driver' ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : '' }} block w-full rounded-xl border border-slate-300 px-4 py-3 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20 transition-all" placeholder="Last" />
                                                @error('passengers.' . $index . '.last_name')<p class="mt-2 text-xs text-rose-600">Required</p>@enderror
                                            </div>
                                        </div>
                                    </label>

                                    <label class="block min-w-0">
                                        <span class="text-slate-900 font-bold text-sm">Date of birth</span>
                                        <input type="date" wire:model.blur="passengers.{{ $index }}.birthdate" {{ $passenger['type'] === 'driver' ? 'readonly' : '' }} class="{{ $passenger['type'] === 'driver' ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : '' }} mt-3 block w-full rounded-xl border border-slate-300 px-4 py-3 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20 transition-all" />
                                        @error('passengers.' . $index . '.birthdate')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                    </label>

                                    {{-- Airline per-passenger promo toggle --}}
                                    @if($mode === 'airline')
                                        @php
                                            $passengerActivePromo = $activePromo ?? $this->getActivePromoTicket();
                                            $passengerHasPromo    = ! empty($passenger['use_promo']);
                                            $discountClearedByPromo = ! empty($passenger['promo_cleared_discount']);
                                            $slotsLeft = $this->getAvailablePromoSlotsRemaining();
                                        @endphp
                                        @if($passengerActivePromo)
                                            <div class="col-span-full mt-1">
                                                @if($passengerHasPromo)
                                                    {{-- Active promo badge --}}
                                                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-[#db2777] bg-[#db2777]/5 px-4 py-3">
                                                        <div class="flex items-center gap-2">
                                                            <svg class="h-5 w-5 text-[#db2777] shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                            <div>
                                                                <p class="font-bold text-sm text-[#db2777]">Promotional Fare Applied</p>
                                                                <p class="text-xs text-slate-600">&#8369;{{ number_format($passengerActivePromo->promo_price, 2) }}/pax &middot; Discounts are not combinable with a promo fare.</p>
                                                                @if($discountClearedByPromo)
                                                                    <p class="mt-1 text-xs text-amber-600 font-medium">&#x26A0;&#xFE0F; Your discount selection was removed because promo fares cannot be combined with other discounts.</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <button type="button"
                                                            wire:click.prevent="togglePassengerPromo({{ $index }})"
                                                            class="inline-flex items-center gap-1 rounded-lg border border-[#db2777] px-3 py-1.5 text-xs font-semibold text-[#db2777] hover:bg-[#db2777]/10 transition-colors">
                                                            Remove Promo
                                                        </button>
                                                    </div>
                                                @elseif($slotsLeft > 0)
                                                    {{-- Available promo button --}}
                                                    <button type="button"
                                                        wire:click.prevent="togglePassengerPromo({{ $index }})"
                                                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-[#db2777]/40 px-4 py-2.5 text-sm font-semibold text-[#db2777] hover:border-[#db2777] hover:bg-[#db2777]/5 transition-all">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                                        Use Promo Fare <span class="text-xs font-normal text-slate-500">({{ $slotsLeft }} remaining)</span>
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                    @endif

                                    {{-- Discount select &mdash; hidden when promo is active for this passenger --}}
                                    @php
                                        $passengerHasPromoForDiscount = ($mode === 'airline') && ! empty($passenger['use_promo']);
                                        $isDriver = $passenger['type'] === 'driver';
                                    @endphp
                                    <label class="block min-w-0 {{ $passengerHasPromoForDiscount ? 'opacity-40 pointer-events-none select-none' : '' }}">
                                        <span class="text-slate-900 font-bold text-sm">Discount</span>
                                        @if($isDriver)
                                            <div class="mt-3 rounded-xl border border-[#00a859] bg-[#00a859]/10 px-4 py-3 text-sm text-[#216417] font-bold flex items-center gap-2 shadow-sm">
                                                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                                Driver &mdash; Free Ticket
                                            </div>
                                        @elseif($passengerHasPromoForDiscount)
                                            <p class="mt-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-400">No discount &mdash; promo fare applied</p>
                                        @else
                                            <select wire:model.number="passengers.{{ $index }}.discount_id" wire:change="$refresh" class="mt-3 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20 transition-all">
                                                <option value="">No discount</option>
                                                @foreach($availableDiscounts as $discount)
                                                    <option value="{{ $discount->id }}">{{ $discount->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('passengers.' . $index . '.discount_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                        @endif
                                    </label>

                                    @php
                                        $selectedDiscount = $discounts->firstWhere('id', $passenger['discount_id']);
                                        $discountKey = strtolower($selectedDiscount->name ?? '');
                                    @endphp

                                    @if($selectedDiscount && str_contains($discountKey, 'student'))
                                        <label class="block min-w-0">
                                            <span class="text-slate-900 font-bold text-sm">Upload school ID (Front)</span>
                                            <input type="file" wire:model="studentIdProofFronts.{{ $index }}" accept="image/*" class="mt-3 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20 transition-all" />
                                            @error('studentIdProofFronts.' . $index)<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                        </label>

                                        <label class="block min-w-0">
                                            <span class="text-slate-900 font-bold text-sm">Upload school ID (Back)</span>
                                            <input type="file" wire:model="studentIdProofBacks.{{ $index }}" accept="image/*" class="mt-3 block w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20 transition-all" />
                                            @error('studentIdProofBacks.' . $index)<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                        </label>

                                        <label class="block min-w-0">
                                            <span class="text-slate-900 font-bold text-sm">Student number</span>
                                            <input type="text" wire:model.blur="passengers.{{ $index }}.student_number" class="mt-3 block w-full rounded-xl border border-slate-300 px-4 py-3 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20 transition-all" placeholder="Student number" />
                                            @error('passengers.' . $index . '.student_number')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                        </label>
                                    @elseif($selectedDiscount && str_contains($discountKey, 'senior'))
                                        <label class="block min-w-0">
                                            <span class="text-slate-900 font-bold text-sm">OSCA number</span>
                                            <input type="text" wire:model.blur="passengers.{{ $index }}.senior_osca_number" class="mt-3 block w-full rounded-xl border border-slate-300 px-4 py-3 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20 transition-all" placeholder="OSCA number" />
                                            @error('passengers.' . $index . '.senior_osca_number')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                        </label>
                                    @elseif($selectedDiscount && str_contains($discountKey, 'pwd'))
                                        <label class="block min-w-0">
                                            <span class="text-slate-900 font-bold text-sm">PWD ID number</span>
                                            <input type="text" wire:model.blur="passengers.{{ $index }}.pwd_id_number" class="mt-3 block w-full rounded-xl border border-slate-300 px-4 py-3 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20 transition-all" placeholder="PWD ID number" />
                                            @error('passengers.' . $index . '.pwd_id_number')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                        </label>
                                    @endif


                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if ($step === 4)
                        <div class="space-y-6">
                            <div class="space-y-4">
                                <p class="text-black font-bold">Choose a place to stay in {{ $destination }} (optional).</p>
                                @php
                                    $currentDestination = $destination;
                                    $filteredHotels = $accommodationCatalog->filter(function ($acc) use ($currentDestination) {
                                        return $acc->destination === $currentDestination;
                                    });
                                @endphp

                                @if($filteredHotels->isEmpty())
                                    <p class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-slate-700 font-medium text-sm">No accommodations are available in {{ $destination }} right now. You can continue without one.</p>
                                @else
                                    <div class="grid gap-5 sm:grid-cols-2">
                                        @foreach($filteredHotels as $hotel)
                                            @php $isSelected = $selected_hotel_id === $hotel->id; @endphp
                                            <button
                                                type="button"
                                                wire:key="hotel-{{ $hotel->id }}"
                                                wire:click.prevent="$set('selected_hotel_id', {{ $isSelected ? 'null' : $hotel->id }})"
                                                class="text-left rounded-2xl border-2 overflow-hidden transition duration-200 {{ $isSelected ? 'border-[#db2777] shadow-md ring-2 ring-[#db2777]/20' : 'border-slate-200 hover:border-[#db2777]/50 hover:shadow-sm' }}"
                                            >
                                                <div class="relative h-48 w-full bg-slate-100">
                                                    @if($hotel->cover_image)
                                                        <img src="{{ $hotel->cover_image }}" alt="{{ $hotel->name }}" class="h-full w-full object-cover transition-transform duration-500 hover:scale-105" />
                                                    @else
                                                        <div class="flex h-full w-full items-center justify-center text-slate-400 text-sm font-medium">No photo</div>
                                                    @endif
                                                    @if($isSelected)
                                                        <span class="absolute top-4 right-4 rounded-full bg-[#db2777] text-white text-[10px] font-bold uppercase tracking-wider px-3 py-1 shadow-sm">Selected</span>
                                                    @endif
                                                </div>
                                                <div class="p-5">
                                                    <h3 class="font-bold text-slate-900 text-lg">{{ $hotel->name }}</h3>
                                                    @if($hotel->description)
                                                        <p class="mt-2 text-sm text-slate-600 line-clamp-2 leading-relaxed">{{ $hotel->description }}</p>
                                                    @endif
                                                    <p class="mt-4 text-xl font-extrabold text-[#db2777]">&#8369;{{ number_format($hotel->price, 2) }}</p>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if ($step === 5)
                        <div class="grid gap-6 lg:grid-cols-2">
                            <label class="block" data-error="client_name">
                                <span class="text-slate-900 font-bold text-sm">Your name</span>
                                <input type="text" wire:model.blur="client_name" class="mt-3 block w-full rounded-xl border border-slate-300 px-4 py-3 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20 transition-all" placeholder="Jane Doe" />
                                @error('client_name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </label>

                            <label class="block" data-error="client_email">
                                <span class="text-slate-900 font-bold text-sm">Email address</span>
                                <input type="email" wire:model.blur="client_email" class="mt-3 block w-full rounded-xl border border-slate-300 px-4 py-3 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20 transition-all" placeholder="you@example.com" />
                                @error('client_email')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </label>

                            <label class="block lg:col-span-2" data-error="client_phone">
                                <span class="text-slate-900 font-bold text-sm">Contact number <span class="text-rose-600">*</span></span>
                                <input required type="tel" inputmode="tel" wire:model.blur="client_phone" oninput="this.value = this.value.replace(/[^0-9+\s()-]/g, '')" onkeypress="if(event.key.length === 1 && !/[0-9+\s()-]/.test(event.key)) event.preventDefault();" class="mt-3 block w-full rounded-xl border border-slate-300 px-4 py-3 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20 transition-all" placeholder="e.g. +63 912 345 6789" />
                                @error('client_phone')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                            </label>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                            <h2 class="text-lg font-bold text-slate-900">Review</h2>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div class="space-y-3">
                                    <p class="text-slate-700 text-sm"><span class="font-bold text-slate-900">Route:</span> {{ $origin }} &rarr; {{ $destination }}</p>
                                    <p class="text-slate-700 text-sm"><span class="font-bold text-slate-900">Dates:</span> {{ $departure_date }}{{ $return_date ? ' &rarr; ' . $return_date : '' }}</p>
                                    <p class="text-slate-700 text-sm"><span class="font-bold text-slate-900">Passengers:</span> {{ $adults }} adult{{ $adults !== 1 ? 's' : '' }}@if($mode === 'airline'){{ $minors > 0 ? ', ' . $minors . ' minor' . ($minors !== 1 ? 's' : '') : '' }}@endif{{ $children > 0 ? ', ' . $children . ' child' . ($children !== 1 ? 'ren' : '') : '' }}@if($mode === 'airline'){{ $infants > 0 ? ', ' . $infants . ' infant' . ($infants !== 1 ? 's' : '') : '' }}@endif</p>
                                    @if ($selected_transport_class_id && isset($selectedClass))
                                        <p class="text-slate-700 text-sm"><span class="font-bold text-slate-900">Travel Class:</span> {{ $selectedClass['name'] }}</p>
                                    @endif
                                    @if ($has_vehicle)
                                        <p class="text-slate-700 text-sm"><span class="font-bold text-slate-900">Vehicle:</span> {{ $vehicle_type }} ({{ $vehicle_plate_number }}) &mdash; &#8369;{{ number_format($vehicle_price ?? 0, 2) }}</p>
                                    @endif
                                </div>

                                <div class="space-y-3">
                                    @php
                                        $selectedSchedule = collect($availableSchedules)->firstWhere('id', $selected_schedule_id);
                                        $selectedClass = $selectedSchedule && $selected_transport_class_id
                                            ? collect($selectedSchedule['transport_classes'] ?? [])->firstWhere(fn($c) => (int)($c['pivot_id'] ?? $c['id']) === (int)$selected_transport_class_id)
                                            : null;
                                        $discountedCount = collect($passengers)->filter(fn ($p) => !empty($p['discount_id']))->count();
                                        $promoCount = ($mode === 'airline') ? $this->getSelectedPromoPassengerCount() : 0;
                                    @endphp
                                    @if($promoCount > 0)
                                        <p class="text-slate-700 text-sm"><span class="font-bold text-slate-900">Promotional fare:</span> <span class="text-[#db2777] font-semibold">{{ $promoCount }} passenger(s)</span></p>
                                    @endif
                                    <p class="text-slate-700 text-sm"><span class="font-bold text-slate-900">Discounted travelers:</span> {{ $discountedCount }} of {{ count($passengers) }}</p>
                                    <p class="text-slate-700 text-sm"><span class="font-bold text-slate-900">Travel Class selected:</span> {{ $selectedClass ? $selectedClass['name'] : 'None' }}</p>
                                    <p class="text-slate-700 text-sm"><span class="font-bold text-slate-900">Estimated total:</span> <span class="font-extrabold text-[#db2777]">&#8369;{{ number_format($this->calculateTotalPrice(), 2) }}</span></p>
                                </div>

                            </div>

                            <div class="mt-6 space-y-3">
                                @php
                                    $summaryPromoTicket = ($mode === 'airline') ? $this->getActivePromoTicket() : null;
                                @endphp
                                @forelse($passengers as $passenger)
                                    @php
                                        $summaryIsPromo = $summaryPromoTicket && ! empty($passenger['use_promo']);
                                    @endphp
                                    <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-sm transition-shadow hover:shadow-md">
                                        <div class="flex items-center justify-between">
                                            <span class="text-slate-900 font-bold text-sm">{{ ucfirst($passenger['type']) }}{{ $passenger['name'] ? ' — ' . $passenger['name'] : '' }}</span>
                                            @if($summaryIsPromo)
                                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 bg-[#db2777]/10 text-[#db2777] rounded-full">
                                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                    Promotional Fare &mdash; &#8369;{{ number_format($summaryPromoTicket->promo_price, 2) }}
                                                </span>
                                            @else
                                                <span class="text-slate-500 text-xs font-semibold px-2 py-1 bg-slate-100 rounded-full">{{ optional($discounts->firstWhere('id', $passenger['discount_id']))->name ?? 'No discount' }}</span>
                                            @endif
                                        </div>

                                    </div>
                                @empty
                                    <p class="text-slate-500 italic">No passengers added yet.</p>
                                @endforelse

                                @if ($discountedCount > 0)
                                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
                                        <div class="flex items-start gap-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 text-amber-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div>
                                                <h3 class="text-sm font-bold text-amber-900">Important Reminder</h3>
                                                <p class="mt-1 text-sm text-amber-700">You have booked discounted tickets. Please make sure to bring the valid IDs (School ID, OSCA ID, or PWD ID) and present them at the port during boarding.</p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>


                            <div class="mt-6 space-y-3">
                                @if ($mode === 'ferry' && $has_vehicle)
                                    <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-sm flex justify-between items-center">
                                        <div>
                                            <p class="text-slate-900 font-bold text-sm">Vehicle: <span class="text-[#db2777]">{{ $vehicle_type }}</span></p>
                                            <p class="text-slate-500 text-xs">Plate: {{ $vehicle_plate_number }}</p>
                                        </div>
                                        <p class="text-slate-900 font-bold">&#8369;{{ number_format($vehicle_price ?? 0, 2) }}</p>
                                    </div>
                                @endif
                                @if ($selected_transport_class_id && isset($selectedClass))
                                    <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-sm flex justify-between items-center">
                                        <p class="text-slate-900 font-bold text-sm">Travel Class: <span class="text-[#db2777]">{{ $selectedClass['name'] }}</span></p>
                                        <p class="text-slate-900 font-bold">&#8369;{{ number_format($selectedClass['price'], 2) }}</p>
                                    </div>
                                @endif
                                @if ($selected_hotel_id)
                                    @php $selectedHotel = $accommodationCatalog->firstWhere('id', $selected_hotel_id); @endphp
                                    <div class="rounded-xl bg-white p-4 border border-slate-200 shadow-sm flex justify-between items-center">
                                        <p class="text-slate-900 font-bold text-sm">Hotel: <span class="text-[#db2777]">{{ $selectedHotel->name }}</span></p>
                                        <p class="text-slate-900 font-bold">&#8369;{{ number_format($selectedHotel->price, 2) }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                            <h2 class="text-lg font-bold text-slate-900">Selected schedule{{ $trip_type === 'round_trip' ? 's' : '' }}</h2>
                            @php
                                $departureSchedule = collect($availableSchedules)->firstWhere('id', $selected_schedule_id);
                                $returnSchedule = $trip_type === 'round_trip' ? collect($availableReturnSchedules)->firstWhere('id', $selected_return_schedule_id) : null;
                            @endphp
                            
                            {{-- Departure Ticket --}}
                            @if ($departureSchedule)
                                <div class="mt-4 rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                                    <div class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">Departure</div>
                                    
                                    {{-- Operator Logo and Route --}}
                                    @php
                                        $depOpLogo = $operator ? ($this->operatorLogos[$operator] ?? null) : null;
                                    @endphp
                                    
                                    <div class="mb-4 flex items-center gap-4 pb-4 border-b border-slate-100">
                                        @if($depOpLogo)
                                            <div class="w-16 h-12 flex-shrink-0">
                                                <img src="{{ $depOpLogo }}" alt="{{ $operator }}" class="w-full h-full object-contain">
                                            </div>
                                        @endif
                                        <div class="flex-1">
                                            <p class="text-slate-500 text-xs font-semibold">{{ $origin }} &rarr; {{ $destination }}</p>
                                            <p class="text-slate-900 font-bold text-sm mt-1">{{ $operator ?: 'Ferry Service' }}</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-[#db2777] font-extrabold text-lg">{{ $departureSchedule['service'] }}</p>
                                            <p class="text-slate-600 font-medium mt-1">{{ $departureSchedule['departure'] }} - {{ $departureSchedule['arrival'] }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-slate-900 font-bold text-lg">&#8369;{{ number_format($departureSchedule['price'], 2) }}</p>
                                            <p class="text-slate-500 text-sm">Duration: {{ $departureSchedule['duration'] }}</p>
                                        </div>
                                    </div>
                                    @if ($departureSchedule['vehicle_name'])
                                        <div class="mt-3 pt-3 border-t border-slate-100">
                                            <p class="text-slate-700 text-sm"><span class="font-bold text-slate-900">Vehicle:</span> {{ $departureSchedule['vehicle_name'] }}</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <p class="mt-4 text-slate-500 italic">No departure schedule selected yet.</p>
                            @endif

                            {{-- Return Ticket (if round trip) --}}
                            @if ($trip_type === 'round_trip')
                                @if ($returnSchedule)
                                    <div class="mt-4 rounded-xl bg-white p-5 border border-slate-200 shadow-sm">
                                        <div class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-500">Return</div>
                                        
                                        {{-- Operator Logo and Route (reversed for return) --}}
                                        @php
                                            $retOpLogo = $operator ? ($this->operatorLogos[$operator] ?? null) : null;
                                        @endphp
                                        
                                        <div class="mb-4 flex items-center gap-4 pb-4 border-b border-slate-100">
                                            @if($retOpLogo)
                                                <div class="w-16 h-12 flex-shrink-0">
                                                    <img src="{{ $retOpLogo }}" alt="{{ $operator }}" class="w-full h-full object-contain">
                                                </div>
                                            @endif
                                            <div class="flex-1">
                                                <p class="text-slate-500 text-xs font-semibold">{{ $destination }} &rarr; {{ $origin }}</p>
                                                <p class="text-slate-900 font-bold text-sm mt-1">{{ $operator ?: 'Ferry Service' }}</p>
                                            </div>
                                        </div>
                                        
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-[#db2777] font-extrabold text-lg">{{ $returnSchedule['service'] }}</p>
                                                <p class="text-slate-600 font-medium mt-1">{{ $returnSchedule['departure'] }} - {{ $returnSchedule['arrival'] }}</p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-slate-900 font-bold text-lg">&#8369;{{ number_format($returnSchedule['price'], 2) }}</p>
                                                <p class="text-slate-500 text-sm">Duration: {{ $returnSchedule['duration'] }}</p>
                                            </div>
                                        </div>
                                        @if ($returnSchedule['vehicle_name'])
                                            <div class="mt-3 pt-3 border-t border-slate-100">
                                                <p class="text-slate-700 text-sm"><span class="font-bold text-slate-900">Vehicle:</span> {{ $returnSchedule['vehicle_name'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <p class="mt-4 text-slate-500 italic">No return schedule selected yet.</p>
                                @endif
                            @endif
                        </div>

                        {{-- Price Breakdown --}}
                        @php
                            $breakdown = $this->getPriceBreakdown();
                        @endphp
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
                            <h2 class="text-lg font-bold text-slate-900">Price Breakdown</h2>
                            <div class="mt-4 space-y-3">
                                {{-- Tickets --}}
                                @if ($breakdown['departure_ticket'] > 0)
                                    <div class="flex justify-between items-center rounded-lg bg-white p-4 border border-slate-200">
                                        <span class="text-slate-700 font-medium">Departure Ticket & Class ({{ $adults }} adult{{ $adults !== 1 ? 's' : '' }}@if($mode === 'airline'){{ $minors > 0 ? ', ' . $minors . ' minor' . ($minors !== 1 ? 's' : '') : '' }}@endif{{ $children > 0 ? ', ' . $children . ' child' . ($children !== 1 ? 'ren' : '') : '' }}@if($mode === 'airline'){{ $infants > 0 ? ', ' . $infants . ' infant' . ($infants !== 1 ? 's' : '') : '' }}@endif)</span>
                                        <span class="text-slate-900 font-bold">&#8369;{{ number_format($breakdown['departure_ticket'], 2) }}</span>
                                    </div>
                                @endif

                                @if ($trip_type === 'round_trip' && $breakdown['return_ticket'] > 0)
                                    <div class="flex justify-between items-center rounded-lg bg-white p-4 border border-slate-200">
                                        <span class="text-slate-700 font-medium">Return Ticket & Class ({{ $adults }} adult{{ $adults !== 1 ? 's' : '' }}@if($mode === 'airline'){{ $minors > 0 ? ', ' . $minors . ' minor' . ($minors !== 1 ? 's' : '') : '' }}@endif{{ $children > 0 ? ', ' . $children . ' child' . ($children !== 1 ? 'ren' : '') : '' }}@if($mode === 'airline'){{ $infants > 0 ? ', ' . $infants . ' infant' . ($infants !== 1 ? 's' : '') : '' }}@endif)</span>
                                        <span class="text-slate-900 font-bold">&#8369;{{ number_format($breakdown['return_ticket'], 2) }}</span>
                                    </div>
                                @endif

                                {{-- Accommodation --}}
                                @if ($breakdown['accommodation'] > 0)
                                    <div class="flex justify-between items-center rounded-lg bg-white p-4 border border-slate-200">
                                        <span class="text-slate-700 font-medium">Schedule Accommodation</span>
                                        <span class="text-slate-900 font-bold">&#8369;{{ number_format($breakdown['accommodation'], 2) }}</span>
                                    </div>
                                @endif

                                {{-- Transport Class --}}
                                @if ($breakdown['transport_class'] > 0)
                                    <div class="flex justify-between items-center rounded-lg bg-white p-4 border border-slate-200">
                                        <span class="text-slate-700 font-medium">Transport Class</span>
                                        <span class="text-slate-900 font-bold">&#8369;{{ number_format($breakdown['transport_class'], 2) }}</span>
                                    </div>
                                @endif

                                {{-- Vehicle --}}
                                @if ($breakdown['vehicle'] > 0)
                                    <div class="flex justify-between items-center rounded-lg bg-white p-4 border border-slate-200">
                                        <span class="text-slate-700 font-medium">Vehicle</span>
                                        <span class="text-slate-900 font-bold">&#8369;{{ number_format($breakdown['vehicle'], 2) }}</span>
                                    </div>
                                @endif

                                {{-- Hotel --}}
                                @if ($breakdown['hotel'] > 0)
                                    <div class="flex justify-between items-center rounded-lg bg-white p-4 border border-slate-200">
                                        <span class="text-slate-700 font-medium">Hotel</span>
                                        <span class="text-slate-900 font-bold">&#8369;{{ number_format($breakdown['hotel'], 2) }}</span>
                                    </div>
                                @endif

                                {{-- Extra Baggage --}}
                                @if (isset($breakdown['extra_baggage']) && $breakdown['extra_baggage'] > 0)
                                    <div class="flex justify-between items-center rounded-lg bg-white p-4 border border-slate-200">
                                        <span class="text-slate-700 font-medium">Prepaid Extra Baggage ({{ $extra_baggage_weight }} &times; {{ $adults + $children }} pax)</span>
                                        <span class="text-slate-900 font-bold">&#8369;{{ number_format($breakdown['extra_baggage'], 2) }}</span>
                                    </div>
                                @endif

                                {{-- Fees --}}
                                @if ($breakdown['fee_per_traveler'] > 0)
                                    <div class="flex justify-between items-center rounded-lg bg-white p-4 border border-slate-200">
                                        <span class="text-slate-700 font-medium">Web Admin Fee ({{ $adults + $children }} traveler{{ $adults + $children !== 1 ? 's' : '' }})</span>
                                        <span class="text-slate-900 font-bold">&#8369;{{ number_format($breakdown['fee_per_traveler'], 2) }}</span>
                                    </div>
                                @endif

                                @if ($breakdown['fee_per_accommodation'] > 0)
                                    <div class="flex justify-between items-center rounded-lg bg-white p-4 border border-slate-200">
                                        <span class="text-slate-700 font-medium">Hotel Service Fee</span>
                                        <span class="text-slate-900 font-bold">&#8369;{{ number_format($breakdown['fee_per_accommodation'], 2) }}</span>
                                    </div>
                                @endif

                                @if (isset($breakdown['transaction_fee']) && $breakdown['transaction_fee'] > 0)
                                    <div class="flex justify-between items-center rounded-lg bg-white p-4 border border-slate-200">
                                        <span class="text-slate-700 font-medium">Transaction Fee</span>
                                        <span class="text-slate-900 font-bold">&#8369;{{ number_format($breakdown['transaction_fee'], 2) }}</span>
                                    </div>
                                @endif

                                {{-- Grand Total --}}
                                <div class="mt-4 pt-4 border-t-2 border-slate-300 rounded-lg bg-gradient-to-r from-[#db2777]/5 to-[#216417]/5 p-4 border border-slate-300">
                                    <div class="flex justify-between items-center">
                                        <span class="text-slate-900 font-bold text-lg">Grand Total</span>
                                        <span class="text-[#db2777] font-extrabold text-2xl">&#8369;{{ number_format($breakdown['total'], 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            @php
                                $operatorLabel = !empty($operator) ? $operator : 'the selected operator';
                            @endphp
                            <div class="flex flex-col gap-3">
                                <button type="button" wire:click.prevent="$set('showTermsModal', true)" class="text-left w-full text-[#db2777] hover:text-[#be185d] hover:underline focus:outline-none">
                                    <h3 class="text-lg font-bold">Amiga Gracia Terms and Condition</h3>
                                </button>
                                <p class="text-sm text-slate-600">Please review the full terms in the modal below. The acceptance checkbox will only become available after you reach the end of the document.</p>

                                <button type="button" wire:click.prevent="$set('showPrivacyModal', true)" class="text-left w-full text-[#db2777] hover:text-[#be185d] hover:underline focus:outline-none">
                                    <h3 class="text-lg font-bold">Amiga Gracia Travel Services Data Privacy</h3>
                                </button>
                                <p class="text-sm text-slate-600">Please review the full privacy policy in the modal below. The acceptance checkbox will only become available after you reach the end of the document.</p>
                            </div>
                        </div>

                    @endif

                        @if ($step === 2)
                            @error('selected_transport_class_id')
                                <div class="mt-4 p-4 rounded-xl bg-rose-50 border border-rose-200">
                                    <p class="text-sm font-bold text-rose-600 flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ $message }}
                                    </p>
                                </div>
                            @enderror
                            @error('selected_return_transport_class_id')
                                <div class="mt-4 p-4 rounded-xl bg-rose-50 border border-rose-200">
                                    <p class="text-sm font-bold text-rose-600 flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ $message }}
                                    </p>
                                </div>
                            @enderror
                        @endif

                        <div class="flex flex-col gap-3 sm:flex-row sm:justify-between mt-8 pt-6 border-t border-slate-200">
                            @if ($step > 1)
                                <button type="button" wire:click.prevent="previousStep" class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl border border-slate-300 bg-white px-8 py-3.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:border-slate-400">
                                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                    Back
                                </button>
                            @else
                                <div class="hidden sm:block"></div>
                            @endif

                            @php
                                $currentMaxStep = $maxStep ?? ($this->maxStep ?? 5);
                            @endphp
                            @if ($step < $currentMaxStep)
                                <button type="button" wire:click.prevent="nextStep" class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl bg-[#db2777] px-8 py-3.5 text-sm font-bold text-white shadow-md transition-all hover:bg-[#db2777]/90 hover:shadow-lg">
                                    Next
                                    <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </button>
                            @else
                                <button type="submit" wire:loading.attr="disabled" wire:target="submit,confirmTermsAndContinue,confirmPrivacyAndContinue" class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl bg-[#db2777] px-8 py-3.5 text-sm font-bold text-white shadow-md transition-all hover:bg-[#db2777]/90 hover:shadow-lg ring-4 ring-[#db2777]/20 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <div wire:loading wire:target="submit,confirmTermsAndContinue,confirmPrivacyAndContinue" class="mr-2">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                    <svg wire:loading.remove wire:target="submit,confirmTermsAndContinue,confirmPrivacyAndContinue" class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span wire:loading.remove wire:target="submit,confirmTermsAndContinue,confirmPrivacyAndContinue">Complete Booking</span>
                                    <span wire:loading wire:target="submit,confirmTermsAndContinue,confirmPrivacyAndContinue">Processing...</span>
                                </button>
                            @endif
                        </div>
                    </form>
                    
                    <script>
                        // Auto-scroll to first error on validation failure
                        // Use both Livewire event and MutationObserver for maximum reliability
                        const scrollToFirstError = () => {
                            setTimeout(() => {
                                const errorMessage = document.querySelector('.text-rose-600');
                                if (errorMessage) {
                                    const errorContainer = errorMessage.closest('[data-error]') || errorMessage.closest('label') || errorMessage.parentElement;
                                    if (errorContainer) {
                                        errorContainer.scrollIntoView({ 
                                            behavior: 'smooth', 
                                            block: 'center' 
                                        });
                                    }
                                }
                            }, 150);
                        };

                        // Listen for Livewire validation-error event
                        if (typeof Livewire !== 'undefined') {
                            Livewire.on('validation-error', scrollToFirstError);
                        }

                        // Also observe DOM changes for reliability
                        const observer = new MutationObserver((mutations) => {
                            mutations.forEach((mutation) => {
                                if (mutation.addedNodes.length) {
                                    mutation.addedNodes.forEach((node) => {
                                        if (node.textContent && node.textContent.includes('field is required')) {
                                            scrollToFirstError();
                                        }
                                    });
                                }
                            });
                        });

                        observer.observe(document.body, {
                            childList: true,
                            subtree: true,
                            characterData: true
                        });
                    </script>
                </div>
            </div>
        </div>

    <!-- Operator Confirmation Modal -->
    @if ($showOperatorConfirmation)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
                <div class="bg-gradient-to-r from-pink-500 to-[#db2777] px-6 py-6">
                    <h2 class="text-2xl font-bold text-white">Confirm Your Selection</h2>
                    <p class="text-pink-100 text-sm mt-1">You've selected a specific booking option</p>
                </div>
                
                <div class="px-6 py-6">
                    <div class="space-y-4 mb-6">
                        <div class="rounded-lg bg-slate-50 border border-slate-200 p-4">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Travel Mode</div>
                            <div class="flex items-center gap-2">
                                @if($mode === 'ferry')
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l6-3 6 3 6-3v13l-6 3-6-3-6 3V7z"/></svg>
                                @elseif($mode === 'airline')
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                @endif
                                <span class="font-semibold text-slate-900 capitalize">{{ $mode }}</span>
                            </div>
                        </div>

                        <div class="rounded-lg bg-slate-50 border border-slate-200 p-4">
                            <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">Operator</div>
                            <div class="flex items-center gap-3">
                                @php
                                    $opLogo = $operator ? ($this->operatorLogos[$operator] ?? null) : null;
                                @endphp
                                @if($opLogo)
                                    <div class="w-8 h-8 shrink-0 bg-white rounded flex items-center justify-center overflow-hidden border border-slate-200">
                                        <img src="{{ $opLogo }}" alt="{{ $operator }}" class="w-full h-full object-contain">
                                    </div>
                                @endif
                                <span class="font-semibold text-slate-900">{{ $operator }}</span>
                            </div>
                        </div>
                    </div>

                    <p class="text-sm text-slate-600 mb-6">
                        These choices cannot be changed after confirming. If you'd like to book with a different operator or travel mode, you can go back and select again.
                    </p>
                </div>

                <div class="flex gap-3 px-6 py-4 bg-slate-50 border-t border-slate-200">
                    <button type="button" wire:click="changeSelection" class="flex-1 px-4 py-2.5 rounded-lg border border-slate-300 text-slate-700 font-semibold hover:bg-slate-100 transition">
                        Cancel
                    </button>
                    <button type="button" wire:click="confirmOperatorSelection" class="flex-1 px-4 py-2.5 rounded-lg bg-[#db2777] text-white font-semibold hover:bg-pink-700 transition">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Terms and Conditions Modal -->
    @if ($showTermsModal)
        @php
            $operatorLabel = !empty($operator) ? $operator : 'the selected operator';
        @endphp
        <div x-data="{ accepted: @entangle('hasAcceptedTerms'), isSubmitting: @entangle('isSubmittingBooking'), scrolledToBottom: false }" x-init="initBookingModal($el); $nextTick(() => { const el = $refs.content; if (el) { this.scrolledToBottom = el.scrollTop + el.clientHeight >= el.scrollHeight - 150; } })" class="fixed inset-x-0 top-20 bottom-0 z-[100] flex items-center justify-center px-4 pb-4 pt-6 bg-slate-900/60 backdrop-blur-sm">
            <div class="w-full max-w-2xl max-h-[calc(100vh-5rem)] overflow-hidden bg-white rounded-2xl shadow-2xl flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h2 class="text-xl font-bold text-slate-900">Amiga Gracia Terms and Agreement</h2>
                </div>
                
                <div x-ref="content" x-on:scroll.passive="scrolledToBottom = scrolledToBottom || $event.target.scrollTop + $event.target.clientHeight >= $event.target.scrollHeight - 150" class="flex-1 overflow-y-auto px-6 py-4">
                    <p class="text-sm text-slate-700 mb-6">
                        Please go through these Terms and Agreement carefully. Your acceptance is required before continuing with your booking.
                    </p>
                    
                    @if($mode === 'airline')
                    <div class="space-y-6 text-sm text-slate-700">
                        <p class="font-semibold text-slate-900">AMIGA GRACIA TRAVEL SERVICES<br>Airline Ticket Booking Guidelines</p>
                        <p>Thank you for choosing Amiga Gracia Travel Services. To ensure a smooth and hassle-free booking experience, please read and understand the following guidelines before confirming your airline reservation.</p>
                        
                        <!-- Passenger Information -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">1. Passenger Information</h3>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>The passenger's full name must exactly match the name on the passport or valid government-issued ID.</li>
                                <li>Amiga Gracia Travel Services will not be responsible for any costs resulting from incorrect or incomplete passenger information provided by the client.</li>
                                <li>Date of birth, nationality, passport details, and contact information must be accurate and complete.</li>
                            </ul>
                        </div>
                        
                        <!-- Fare Availability -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">2. Fare Availability</h3>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Airline fares are dynamic and may change without prior notice until the ticket is issued.</li>
                                <li>Quoted fares are subject to seat availability and airline confirmation.</li>
                                <li>A reservation is not guaranteed until full payment has been received and the ticket has been issued.</li>
                            </ul>
                        </div>

                        <!-- Payment Policy -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">3. Payment Policy</h3>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Full payment is required before ticket issuance unless otherwise agreed in writing.</li>
                                <li>Payment confirmation does not automatically guarantee the booking until verified by our office.</li>
                                <li>Tickets will only be issued after payment has been successfully received and confirmed.</li>
                            </ul>
                        </div>

                        <!-- Ticket Issuance -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">4. Ticket Issuance</h3>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Once payment is confirmed, the electronic ticket (E-ticket) and itinerary receipt will be sent to the passenger via the registered email address or preferred messaging platform.</li>
                                <li>Passengers are advised to review their itinerary immediately and report any discrepancies within 24 hours of receipt.</li>
                            </ul>
                        </div>

                        <!-- Changes and Corrections -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">5. Changes and Corrections</h3>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Name changes or corrections are subject to the airline's policies and may not be permitted.</li>
                                <li>Flight date, time, or route changes are subject to fare conditions, airline approval, seat availability, and applicable penalties.</li>
                                <li>Any additional fare difference and airline-imposed fees shall be borne by the passenger.</li>
                            </ul>
                        </div>

                        <!-- Cancellation and Refunds -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">6. Cancellation and Refunds</h3>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Cancellation and refund requests are subject to the airline's fare rules and conditions.</li>
                                <li>Promotional or discounted tickets may be non-refundable, non-reroutable, or non-rebookable.</li>
                                <li>Processing time for approved refunds depends solely on the airline and may take several weeks or months.</li>
                                <li>Service fees charged by Amiga Gracia Travel Services are non-refundable unless otherwise stated.</li>
                            </ul>
                        </div>

                        <!-- Travel Documents -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">7. Travel Documents</h3>
                            <p class="mb-2">Passengers are responsible for ensuring they possess all required travel documents, including:</p>
                            <ul class="list-disc pl-5 space-y-1 mb-2">
                                <li>Valid passport (with the required validity for the destination)</li>
                                <li>Appropriate visa(s), if applicable</li>
                                <li>Government-issued identification</li>
                                <li>Health, vaccination, or other entry requirements imposed by the destination country</li>
                            </ul>
                            <p>Amiga Gracia Travel Services is not liable for denied boarding or entry due to incomplete, expired, or invalid travel documents.</p>
                        </div>

                        <!-- Baggage Policy -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">8. Baggage Policy</h3>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Baggage allowances vary by airline, route, and fare class.</li>
                                <li>Additional baggage purchased after ticket issuance may be subject to different rates.</li>
                                <li>Passengers are encouraged to verify baggage allowances before travel.</li>
                            </ul>
                        </div>

                        <!-- Check-in Requirements -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">9. Check-in Requirements</h3>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Domestic flights: Arrive at the airport at least 2 hours before departure.</li>
                                <li>International flights: Arrive at least 3 hours before departure.</li>
                                <li>Passengers should complete online check-in whenever available.</li>
                            </ul>
                        </div>

                        <!-- Flight Schedule Changes -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">10. Flight Schedule Changes</h3>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Airlines may change flight schedules without prior notice.</li>
                                <li>Amiga Gracia Travel Services will notify passengers of any airline advisories received; however, passengers are also encouraged to monitor their flight status directly with the airline before departure.</li>
                            </ul>
                        </div>

                        <!-- No-Show Policy -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">11. No-Show Policy</h3>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Failure to check in or board the scheduled flight may result in cancellation of the remaining itinerary, depending on the airline's policy.</li>
                                <li>No-show penalties are determined solely by the airline.</li>
                            </ul>
                        </div>

                        <!-- Special Requests -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">12. Special Requests</h3>
                            <p class="mb-2">Requests such as:</p>
                            <ul class="list-disc pl-5 space-y-1 mb-2">
                                <li>Special meals</li>
                                <li>Wheelchair assistance</li>
                                <li>Bassinet requests</li>
                                <li>Unaccompanied minor services</li>
                                <li>Seat preferences</li>
                                <li>Medical assistance</li>
                            </ul>
                            <p>are subject to airline approval and availability and cannot be guaranteed.</p>
                        </div>

                        <!-- Force Majeure -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">13. Force Majeure</h3>
                            <p>Amiga Gracia Travel Services shall not be held liable for flight disruptions, cancellations, delays, missed connections, or additional expenses resulting from events beyond our control, including but not limited to adverse weather conditions, natural disasters, government regulations, airport closures, labor strikes, pandemics, security concerns, or airline operational decisions.</p>
                        </div>

                        <!-- Client Responsibility -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">14. Client Responsibility</h3>
                            <p class="mb-2">By confirming your booking, you acknowledge that:</p>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>All information provided is accurate.</li>
                                <li>You have reviewed and accepted the airline's fare rules and conditions.</li>
                                <li>You understand the applicable cancellation, refund, and rebooking policies.</li>
                                <li>You agree to comply with all airline, airport, immigration, customs, and health regulations.</li>
                            </ul>
                        </div>

                        <!-- Client Acknowledgment -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">Client Acknowledgment</h3>
                            <p>I certify that I have read, understood, and agreed to the Airline Ticket Booking Guidelines of Amiga Gracia Travel Services. I acknowledge that airline fares, schedules, baggage allowances, and booking conditions are governed by the respective airline's policies and that I accept the applicable terms and conditions.</p>
                        </div>
                    </div>
                    @else
                    <div class="space-y-6 text-sm text-slate-700">
                        <!-- Boarding Requirements -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">Boarding Requirements</h3>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>One printed copy of the eTicket Itinerary Receipt.</li>
                                <li>Presentation of each passenger's valid ID.</li>
                                <li>Passengers must arrive at the terminal 3&ndash;4 hours before departure. Boarding gates close 1 hour before departure.</li>
                                <li>The operating ferry carrier reserves the right to refuse boarding if a passenger cannot present the required documents upon request.</li>
                            </ul>
                        </div>
                        
                        <!-- eTicket Itinerary Receipt -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">eTicket Itinerary Receipt</h3>
                            <p>The eTicket Itinerary Receipt is non-transferable. It is valid only until the date and time of departure printed on the ticket. Unused or expired eTickets are non-refundable and cannot be revalidated, subject to the Return Policy below.</p>
                        </div>
                        
                        <!-- Government-Mandated Discounts -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">Government-Mandated Discounts</h3>
                            
                            <div class="mt-4">
                                <h4 class="text-sm font-bold text-slate-900 mb-1">Senior Citizens</h4>
                                <p class="mb-2">Senior Citizen discounts apply to passengers aged 60 or above once a valid ID number has been entered. The 20% Senior Citizen discount applies to the base rate only.</p>
                                <p class="mb-2">If a promotional rate is available, the lowest applicable eligible fare may be used instead of the Senior Citizen discount. VAT treatment is subject to applicable law and carrier policy.</p>
                                <p class="mb-2">Passengers must present a valid Senior Citizen ID issued by the Office for Senior Citizens Affairs (OSCA), or another valid Philippine government-issued ID showing their date of birth, during inspection and boarding. Failure to present the required ID may result in forfeiture of the discount, revalidation requirements, applicable surcharges, and fare differences.</p>
                                <p class="mb-2">Senior Citizen discounts are applicable to Filipino nationals only.</p>
                                <p>Senior citizens are encouraged to travel with a legal-aged companion and bring a medical certificate confirming fitness to travel. They may be subject to assessment by the vessel doctor or nurse on the departure date. Boarding remains subject to the operating carrier's safety assessment.</p>
                            </div>
                            
                            <div class="mt-4">
                                <h4 class="text-sm font-bold text-slate-900 mb-1">Infants</h4>
                                <p class="mb-2">Infants below 2 years old and below 1 meter in height may be allowed to board. A medical certificate and documentation proving the infant's relationship to the accompanying passenger may be required.</p>
                                <p class="mb-2">A fixed rate of &#8369;500.00 applies per infant regardless of destination or accommodation, subject to carrier policy.</p>
                                <p class="mb-2">A separate ticket may be issued for each infant, and the infant may share the parent's or guardian's bunk or room.</p>
                                <p class="mb-2">No more than two infants are allowed per adult passenger. Additional infants may be charged the applicable promotional fare or 75% off the base rate, plus auxiliary charges.</p>
                                <p>Infants must be accompanied by an adult parent or guardian.</p>
                            </div>
                            
                            <div class="mt-4">
                                <h4 class="text-sm font-bold text-slate-900 mb-1">Pregnant Passengers</h4>
                                <p class="mb-2">Pregnant passengers who are 24 weeks or more into their pregnancy may not be allowed to board.</p>
                                <p>Pregnant passengers may be required to present a medical certificate confirming that gestation is below 24 weeks and are encouraged to travel with a legal-aged companion. The vessel doctor or nurse may assess fitness to travel on the departure date. Final boarding approval remains at the discretion of the operating carrier's medical and safety personnel.</p>
                            </div>
                            
                            <div class="mt-4">
                                <h4 class="text-sm font-bold text-slate-900 mb-1">Unaccompanied Minor Passengers</h4>
                                <p class="mb-2">An unaccompanied minor is a passenger aged 11 to 17 who travels alone.</p>
                                <p class="mb-2">Children must be accompanied by a parent or legal guardian at the port and endorsed to the boarding officers. The parent or guardian may be required to sign a waiver.</p>
                                <p>Unaccompanied minors must be collected by their declared representative at the destination port. If no representative is present, the passenger may be held for release and endorsed to the Department of Social Welfare and Development (DSWD), as applicable.</p>
                            </div>
                            
                            <div class="mt-4">
                                <h4 class="text-sm font-bold text-slate-900 mb-1">Other Discounts: PWDs, Students, and Medal of Valor Awardees</h4>
                                <p class="mb-2">Persons with Disabilities (PWDs), students, and Medal of Valor awardees may need to contact Amiga Gracia Travel Services office to request and process their applicable special discounts.</p>
                                <p>These discounts apply to the base rate only and generally do not apply to discounted or promotional fares.</p>
                            </div>
                        </div>
                        
                        <!-- Return, Refund, and Revalidation Policy -->
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">Return, Refund, and Revalidation Policy</h3>
                            <p class="mb-2">Customers may send refund requests to the official Amiga Gracia Travel Services support email using the registered transaction email address. The support email should be configurable via the application settings.</p>
                            <p class="mb-2">Only original eTickets that have not been boarded, remain within ticket validity, and have not undergone rebooking may be eligible for processing.</p>
                            <p class="mb-2">For wallet and credit-card paid transactions, refund requests may be accommodated by email from Monday to Friday, regular business days only, between 8:00 AM and 4:00 PM. Requests must be sent at least two regular days before ticket expiry.</p>
                            <p class="mb-2">For cash and ATM-paid bookings, refunds must be processed through Amiga Gracia Travel Services corporate ticketing outlets.</p>
                            <p class="mb-2">For direct refund requests at a corporate ticketing outlet, the account holder or passenger must submit the complete itinerary and present a valid government-issued ID.</p>
                            <p class="mb-2">If an account holder or passenger authorizes a representative, the representative must present:</p>
                            <ul class="list-disc pl-5 space-y-1 mb-2">
                                <li>An original signed authorization letter.</li>
                                <li>The actual valid government-issued ID of the account holder or passenger.</li>
                                <li>The representative's own valid government-issued ID.</li>
                            </ul>
                            <p class="mb-2">Refunds are subject to the following surcharge:</p>
                            <ul class="list-disc pl-5 space-y-1 mb-2">
                                <li>Before vessel departure:15% Surcharge plus the applicable Web Admin Fee and Transaction Fee per ticket.</li>
                            </ul>
                            <p class="mb-2">No partial refunds are available for tickets purchased under room rates. Refunds are released only after surrendering all tickets issued for the relevant room.</p>
                            <p class="mb-2">Unused and unscanned eTickets may be revalidated during the ticket-validity period. Revalidation means changing ticket details other than the passenger name or age, and the trip origin or destination. Revalidation is processed only through corporate ticketing outlets. Passengers must present the eTicket Itinerary Receipt and a valid ID.</p>
                            <p class="mb-2">Revalidation is subject to the following surcharge:</p>
                            <ul class="list-disc pl-5 space-y-1 mb-2">
                                <li>Before vessel departure: 15% Surcharge + &#8369;150.00 revalidation fee + fare difference.</li>
                            </ul>
                            <p class="mb-2">Refund and revalidation surcharges may be waived if a trip is affected by typhoon, force majeure, technical problems, emergency or extended dry-docking, preventive maintenance, or carrier-initiated trip changes.</p>
                            <p class="mb-2">The Web Admin Fee and Transaction Fee is non-refundable.</p>
                            <p>Ticket validity ends on the date and time of departure printed on the ticket.</p>
                        </div>
                        
                        <!-- Final Notice -->
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                            <p class="text-sm text-slate-700 font-medium">These terms may be updated by Amiga Gracia Travel Services. Please review the current version before each booking.</p>
                        </div>
                    </div>
                    @endif
                </div>
                
                <div class="flex flex-col gap-3 px-6 py-4 border-t border-slate-200">
                    <label class="flex items-start gap-3">
                        <input type="checkbox" x-model="accepted" :disabled="!scrolledToBottom" id="acceptTerms" class="mt-0.5 h-4 w-4 text-[#db2777] border-slate-300 focus:ring-[#db2777] disabled:cursor-not-allowed disabled:opacity-50">
                        <span class="text-sm text-slate-700">I have read and agree to the Amiga Gracia Terms and Agreement.</span>
                    </label>
                    <p class="text-xs text-slate-500" x-show="!scrolledToBottom">Scroll to the end of the document to enable acceptance.</p>
                    <p class="text-xs text-emerald-600" x-show="scrolledToBottom">You have reached the end of the document. You may now accept.</p>
                    @if ($showTermsAgreementWarning && ! $hasAcceptedTerms)
                        <p class="text-sm font-medium text-rose-600">You need to read and agree to continue.</p>
                    @endif
                    @error('hasAcceptedTerms')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                    
                    <div class="flex flex-col-reverse sm:flex-row gap-3 sm:justify-end">
                        <button type="button" wire:click.prevent="cancelTermsModal" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:border-slate-400">
                            Cancel
                        </button>
                        <button type="button" wire:click.prevent="confirmTermsAndContinue" wire:loading.attr="disabled" wire:target="confirmTermsAndContinue" :disabled="!accepted || !scrolledToBottom" class="inline-flex items-center justify-center rounded-xl bg-[#db2777] px-6 py-2.5 text-sm font-bold text-white shadow-md transition-all hover:bg-[#db2777]/90 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                            <div wire:loading wire:target="confirmTermsAndContinue" class="mr-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <span wire:loading.remove wire:target="confirmTermsAndContinue">Done & Continue</span>
                            <span wire:loading wire:target="confirmTermsAndContinue">Processing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showPrivacyModal)
        <div x-data="{ accepted: @entangle('hasAcceptedPrivacy'), isSubmitting: @entangle('isSubmittingBooking'), scrolledToBottom: false }" x-init="initBookingModal($el); $nextTick(() => { const el = $refs.content; if (el) { this.scrolledToBottom = el.scrollTop + el.clientHeight >= el.scrollHeight - 150; } })" class="fixed inset-x-0 top-20 bottom-0 z-[100] flex items-center justify-center px-4 pb-4 pt-6 bg-slate-900/60 backdrop-blur-sm">
            <div class="w-full max-w-2xl max-h-[calc(100vh-5rem)] overflow-hidden bg-white rounded-2xl shadow-2xl flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h2 class="text-xl font-bold text-slate-900">Amiga Gracia Travel Services Data Privacy Policy</h2>
                </div>

                <div x-ref="content" x-on:scroll.passive="scrolledToBottom = scrolledToBottom || $event.target.scrollTop + $event.target.clientHeight >= $event.target.scrollHeight - 150" class="flex-1 overflow-y-auto px-6 py-4">
                    <p class="text-sm text-slate-700 mb-6">
                        Please review how Amiga Gracia Travel Services collects, stores, and protects your personal data before continuing with your booking.
                    </p>

                    <div class="space-y-6 text-sm text-slate-700">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">Personal Data Collection</h3>
                            <p class="mb-2">We collect only the personal information necessary to process your booking, issue tickets, and contact you about your travel reservation.</p>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">Use of Personal Data</h3>
                            <p class="mb-2">Your information is used to confirm your booking, communicate updates, send receipts, and comply with transportation partner requirements.</p>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">Data Security</h3>
                            <p class="mb-2">We take reasonable technical and organizational measures to safeguard your personal data. Access is restricted to authorized personnel only.</p>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">Retention</h3>
                            <p class="mb-2">We retain your booking details for as long as necessary to fulfill our services and comply with legal obligations.</p>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 mb-2">Your Rights</h3>
                            <p class="mb-2">You have the right to access, correct, or request deletion of your personal data in accordance with applicable privacy laws.</p>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 px-6 py-4 border-t border-slate-200">
                    <label class="flex items-start gap-3">
                        <input type="checkbox" x-model="accepted" :disabled="!scrolledToBottom" id="acceptPrivacy" class="mt-0.5 h-4 w-4 text-[#db2777] border-slate-300 focus:ring-[#db2777] disabled:cursor-not-allowed disabled:opacity-50">
                        <span class="text-sm text-slate-700">I have read and agree to the Amiga Gracia Travel Agency Data Privacy Policy.</span>
                    </label>
                    <p class="text-xs text-slate-500" x-show="!scrolledToBottom">Scroll to the end of the document to enable acceptance.</p>
                    <p class="text-xs text-emerald-600" x-show="scrolledToBottom">You have reached the end of the document. You may now accept.</p>
                    @if ($showPrivacyAgreementWarning && ! $hasAcceptedPrivacy)
                        <p class="text-sm font-medium text-rose-600">You need to read and agree to continue.</p>
                    @endif
                    @error('hasAcceptedPrivacy')
                        <p class="text-sm text-rose-600">{{ $message }}</p>
                    @enderror

                    <div class="flex flex-col-reverse sm:flex-row gap-3 sm:justify-end">
                        <button type="button" wire:click.prevent="cancelPrivacyModal" class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:border-slate-400">
                            Cancel
                        </button>
                        <button type="button" wire:click.prevent="confirmPrivacyAndContinue" wire:loading.attr="disabled" wire:target="confirmPrivacyAndContinue" :disabled="!accepted || !scrolledToBottom" class="inline-flex items-center justify-center rounded-xl bg-[#db2777] px-6 py-2.5 text-sm font-bold text-white shadow-md transition-all hover:bg-[#db2777]/90 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                            <div wire:loading wire:target="confirmPrivacyAndContinue" class="mr-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <span wire:loading.remove wire:target="confirmPrivacyAndContinue">Done & Continue</span>
                            <span wire:loading wire:target="confirmPrivacyAndContinue">Processing...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($showPresentIdWarning)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
            <div class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl ring-1 ring-slate-200">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-900">Prepare valid ID for boarding</h2>
                        <p class="mt-3 text-sm leading-6 text-slate-600">Discounted passengers must present a valid ID during boarding. Please have your school ID, OSCA ID, or PWD ID ready.</p>
                    </div>
                    <button type="button" wire:click.prevent="closePresentIdWarning" class="rounded-full bg-slate-100 p-2 text-slate-600 hover:bg-slate-200">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="mt-5 grid gap-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-semibold text-slate-900">What to bring</p>
                        <ul class="mt-3 space-y-2 text-sm text-slate-700">
                            <li>&bull; School ID for Student discounts</li>
                            <li>&bull; OSCA ID for Senior Citizen discounts</li>
                            <li>&bull; PWD ID for PWD discounts</li>
                        </ul>
                    </div>
                    <button type="button" wire:click.prevent="closePresentIdWarning" class="inline-flex w-full items-center justify-center rounded-xl bg-[#db2777] px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#be185d]">
                        Got it, I will present my ID
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($step === 1 && $showDataPrivacyWarning)
        <div x-data="{ show: !localStorage.getItem('amiga_privacy_accepted') }" x-show="show" x-cloak class="fixed inset-0 z-[999] flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm overflow-y-auto">
            <div class="relative w-full max-w-xl max-h-[90vh] flex flex-col rounded-3xl bg-white p-6 sm:p-8 shadow-2xl ring-1 ring-slate-200 text-left overflow-hidden">
                <div class="flex items-center gap-4 pb-2">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Data privacy reminder</h2>
                        <p class="text-xs font-semibold text-slate-500">Please review before proceeding to the booking form</p>
                    </div>
                </div>

                <div class="mt-4 space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-relaxed text-slate-700 overflow-y-auto max-h-[50vh]">
                    <p class="font-semibold text-slate-900">Notice on Data Collection & Privacy Rights</p>
                    <p>
                        In compliance with the <strong>Data Privacy Act of 2012 (R.A. 10173)</strong>, Amiga Gracia Travel Service is committed to protecting your personal information.
                    </p>
                    <p>
                        By proceeding, you consent to the collection, processing, and storage of your personal details (including full names, contact info, birthdates, and identification documents) strictly for ticket reservations, passenger manifest compliance, customer verification, and transport partner requirements.
                    </p>
                    <p class="text-xs text-slate-600 italic">
                        Clicking <strong>Continue</strong> allows you to proceed to the booking form. If you choose <strong>Cancel / Disagree</strong>, you will be redirected to the home page.
                    </p>
                </div>

                <div class="mt-6 flex flex-col-reverse sm:flex-row items-center justify-end gap-3 shrink-0 pt-2">
                    <button 
                        type="button" 
                        @click="window.location.href='/'"
                        wire:click.prevent="declineDataPrivacyWarning"
                        class="w-full sm:w-auto rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 focus:outline-none"
                    >
                        Cancel / Disagree
                    </button>
                    <button 
                        type="button" 
                        @click="localStorage.setItem('amiga_privacy_accepted', '1'); show = false"
                        wire:click.prevent="acceptDataPrivacyWarning"
                        class="w-full sm:w-auto rounded-xl bg-[#216417] px-6 py-3 text-sm font-bold text-white shadow-md transition hover:bg-[#194d12] focus:outline-none cursor-pointer"
                    >
                        Continue
                    </button>
                </div>
            </div>
        </div>
    @endif

    <script>
        function initBookingModal(modal) {
            const focusableElements = modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            if (!focusableElements.length) {
                return;
            }

            const firstFocusable = focusableElements[0];
            const lastFocusable = focusableElements[focusableElements.length - 1];

            const trapFocus = (event) => {
                if (event.key !== 'Tab') {
                    return;
                }

                if (event.shiftKey) {
                    if (document.activeElement === firstFocusable) {
                        event.preventDefault();
                        lastFocusable.focus();
                    }
                } else {
                    if (document.activeElement === lastFocusable) {
                        event.preventDefault();
                        firstFocusable.focus();
                    }
                }
            };

            modal.addEventListener('keydown', trapFocus);
            firstFocusable.focus();
        }

        document.addEventListener('livewire:initialized', () => {
            Livewire.on('validation-error', () => {
                setTimeout(() => {
                    const firstError = document.querySelector('.text-rose-600');
                    if (firstError) {
                        // Find the closest parent section or container to scroll to
                        const container = firstError.closest('.rounded-2xl') || firstError.closest('label') || firstError;
                        container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        // Add a brief highlight animation
                        const input = container.querySelector('input, select, button');
                        if (input) {
                            input.classList.add('ring-2', 'ring-rose-500', 'ring-offset-2');
                            setTimeout(() => {
                                input.classList.remove('ring-2', 'ring-rose-500', 'ring-offset-2');
                            }, 1500);
                        }
                    }
                }, 100);
            });
        });
    </script>
</div>

