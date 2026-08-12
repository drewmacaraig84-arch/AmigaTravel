@props([
    'title' => 'Select a schedule',
    'subtitle' => null,
    'origin' => null,
    'destination' => null,
    'schedules' => [],
    'selectedId' => null,
    'selectedAccommodationId' => null,
    'selectedClassId' => null,
    'selectMethod' => 'selectSchedule',
    'selectAccommodationMethod' => 'selectScheduleAccommodation',
    'selectClassMethod' => 'selectTransportClass',
    'mode' => 'ferry',
])

@php
    // Pre-chunk schedules server-side so each carousel "page" is exactly 100% wide — fixes width math and guarantees uniform card sizing
    $schedulesCollection = collect($schedules)->values();
    $mobileChunks = $schedulesCollection->chunk(2);     // 2 per page on < sm
    $desktopChunks = $schedulesCollection->chunk(3);    // 3 per page on sm+
    $totalMobilePages = $mobileChunks->count();
    $totalDesktopPages = $desktopChunks->count();
@endphp

<div class="space-y-4">
    @if($subtitle)
        <p class="text-slate-500 text-sm mb-1">{{ $subtitle }}</p>
    @endif
    <h3 class="text-2xl font-bold text-[#5c1c85] mb-3">{{ $title }}</h3>
    @if($origin && $destination)
        <div class="bg-[#e0efff] text-[#5c1c85] font-semibold py-3 px-6 rounded-xl inline-flex items-center space-x-6 mb-4">
            <span>From {{ $origin }}</span>
            <span class="w-px h-5 bg-[#5c1c85]/30"></span>
            <span>To {{ $destination }}</span>
        </div>
    @endif

    @if(count($schedules) === 0)
        <p class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-slate-600 text-sm">No schedules are available for this route on the selected date.</p>
    @else
        <!-- Alpine Carousel (server-chunked pages): 2 cards/page mobile, 3 cards/page desktop — each page = 100% width -->
        <div
            x-data="{
                isDesktop: window.innerWidth >= 640,
                currentSlide: 0,
                get totalPages() { return this.isDesktop ? {{ $totalDesktopPages }} : {{ $totalMobilePages }}; },
                get hasMultiplePages() { return this.totalPages > 1; },
                init() {
                    const update = () => {
                        const wasDesktop = this.isDesktop;
                        this.isDesktop = window.innerWidth >= 640;
                        if (wasDesktop !== this.isDesktop) { this.currentSlide = 0; }
                        if (this.currentSlide >= this.totalPages) { this.currentSlide = Math.max(0, this.totalPages - 1); }
                    };
                    window.addEventListener('resize', update);
                }
            }"
            class="relative group"
        >
            <!-- Navigation Arrows -->
            <button
                x-show="hasMultiplePages && currentSlide > 0"
                @click="currentSlide = Math.max(0, currentSlide - 1)"
                class="absolute -left-3 sm:-left-4 top-1/2 -translate-y-1/2 z-20 w-8 h-8 sm:w-10 sm:h-10 bg-white rounded-full shadow-[0_4px_20px_-4px_rgba(0,0,0,0.15)] border border-slate-100 flex items-center justify-center text-slate-600 hover:text-[#216417] hover:border-[#216417] transition-all sm:opacity-0 sm:group-hover:opacity-100"
            >
                <svg class="w-4 h-4 sm:w-5 sm:h-5 pr-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <button
                x-show="hasMultiplePages && currentSlide < totalPages - 1"
                @click="currentSlide = Math.min(totalPages - 1, currentSlide + 1)"
                class="absolute -right-3 sm:-right-4 top-1/2 -translate-y-1/2 z-20 w-8 h-8 sm:w-10 sm:h-10 bg-white rounded-full shadow-[0_4px_20px_-4px_rgba(0,0,0,0.15)] border border-slate-100 flex items-center justify-center text-slate-600 hover:text-[#216417] hover:border-[#216417] transition-all sm:opacity-0 sm:group-hover:opacity-100"
            >
                <svg class="w-4 h-4 sm:w-5 sm:h-5 pl-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>

            <!-- Slides Container (Mobile: 2 per page) -->
            <div class="overflow-hidden py-2 -mx-2 px-2 sm:hidden">
                <div class="flex transition-transform duration-300 ease-out" :style="'transform: translateX(-' + (currentSlide * 100) + '%)'">
                    @foreach($mobileChunks as $pageIdx => $pageSchedules)
                        <div class="w-full flex-shrink-0 flex">
                            @foreach($pageSchedules as $schedule)
                                <div class="w-1/2 flex-shrink-0 px-2">
                                    @include('components._schedule-card', [
                                        'schedule' => $schedule,
                                        'selectedId' => $selectedId,
                                        'selectMethod' => $selectMethod,
                                    ])
                                </div>
                            @endforeach
                            {{-- Pad partial last page with empty spacer so cards keep same width as other pages --}}
                            @for($i = $pageSchedules->count(); $i < 2; $i++)
                                <div class="w-1/2 flex-shrink-0 px-2 invisible">
                                    <div class="rounded-xl border p-3 flex flex-col min-h-[168px] sm:min-h-[190px]"></div>
                                </div>
                            @endfor
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Slides Container (Desktop: 3 per page) -->
            <div class="overflow-hidden py-2 -mx-2 px-2 hidden sm:block">
                <div class="flex transition-transform duration-300 ease-out" :style="'transform: translateX(-' + (currentSlide * 100) + '%)'">
                    @foreach($desktopChunks as $pageIdx => $pageSchedules)
                        <div class="w-full flex-shrink-0 flex">
                            @foreach($pageSchedules as $schedule)
                                <div class="w-1/3 flex-shrink-0 px-2">
                                    @include('components._schedule-card', [
                                        'schedule' => $schedule,
                                        'selectedId' => $selectedId,
                                        'selectMethod' => $selectMethod,
                                    ])
                                </div>
                            @endforeach
                            {{-- Pad partial last page --}}
                            @for($i = $pageSchedules->count(); $i < 3; $i++)
                                <div class="w-1/3 flex-shrink-0 px-2 invisible">
                                    <div class="rounded-2xl border p-4 flex flex-col min-h-[168px] sm:min-h-[190px]"></div>
                                </div>
                            @endfor
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Indicator Dots -->
            <div
                x-show="hasMultiplePages"
                x-transition.opacity.duration.200ms
                class="flex justify-center items-center space-x-2 mt-4 min-h-[12px]"
            >
                <template x-for="i in totalPages" :key="(isDesktop ? 'd':'m') + '-' + i">
                    <button
                        type="button"
                        @click="currentSlide = i - 1"
                        class="rounded-full transition-all duration-200"
                        :class="currentSlide === i - 1
                            ? 'w-6 h-2 bg-[#db2777]'
                            : 'w-2 h-2 bg-slate-300 hover:bg-slate-400'"
                        :aria-label="'Go to page ' + i"
                    ></button>
                </template>
            </div>
        </div>
    @endif

    @error($selectedId) <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror

    @if($selectedId)
        @php
            $selectedSchedule = collect($schedules)->firstWhere('id', $selectedId);
        @endphp

        {{-- Ferry: Show accommodations (legacy fallback) --}}
        @if($mode === 'ferry' && $selectedSchedule && !empty($selectedSchedule['accommodations']) && empty($selectedSchedule['transport_classes']))
            <div class="mt-5 sm:mt-4 border-t border-slate-200 pt-5 sm:pt-4">
                <p class="text-slate-900 font-bold mb-4 sm:mb-3 text-sm">Select accommodation for this trip:</p>
                <div class="grid gap-3 grid-cols-2 sm:gap-4 lg:grid-cols-3">
                    @foreach($selectedSchedule['accommodations'] as $accommodation)
                        @php
                            $schedulePrice = $selectedSchedule['price'] ?? 0;
                            $accommodationPrice = $accommodation['price'] ?? 0;
                            $totalPrice = $schedulePrice + $accommodationPrice;
                        @endphp
                        <button type="button" wire:click.prevent="{{ $selectAccommodationMethod }}({{ $accommodation['id'] }})" class="rounded-xl border-2 p-3 sm:p-4 text-left transition duration-200 {{ (int)$selectedAccommodationId === (int)$accommodation['id'] ? 'border-[#db2777] bg-[#db2777]/5 shadow-sm' : 'border-slate-200 bg-white hover:border-[#db2777]/50 hover:shadow-sm' }}">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h4 class="font-bold text-slate-900 text-xs sm:text-sm">{{ $accommodation['name'] }}</h4>
                                <div class="flex items-center gap-1.5">
                                    @if(isset($accommodation['tickets_available']))
                                        <span class="text-[10px] font-extrabold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full border border-emerald-200">
                                            {{ $accommodation['tickets_available'] }} {{ \Illuminate\Support\Str::plural('ticket', $accommodation['tickets_available']) }} left
                                        </span>
                                    @endif
                                    @if($accommodation['has_bed'])
                                        <span class="text-[9px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full border border-slate-200">With Bed</span>
                                    @endif
                                </div>
                            </div>
                            <p class="mt-2 text-lg font-extrabold text-[#db2777]">&#8369;{{ number_format($totalPrice, 2) }}</p>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Show transport classes (for Airlines and modern Ferries) --}}
        @if($selectedSchedule && !empty($selectedSchedule['transport_classes']))
            <div class="mt-5 sm:mt-4 border-t border-slate-200 pt-5 sm:pt-4">
                <p class="text-slate-900 font-bold mb-4 sm:mb-3 text-sm">Select travel class for this trip:</p>
                <div class="grid gap-3 grid-cols-2 sm:gap-4 lg:grid-cols-3">
                    @foreach($selectedSchedule['transport_classes'] as $class)
                        @php
                            $schedulePrice = $selectedSchedule['price'] ?? 0;
                            $classPrice = $class['price'] ?? 0;
                            $totalPrice = $schedulePrice + $classPrice;
                            $uniqueId = $class['pivot_id'] ?? $class['id'];
                        @endphp
                        <button type="button" 
                            @if(!empty($class['is_promo'])) 
                                x-data=""
                                x-on:click.prevent="$dispatch('open-promo-modal', { method: '{{ $selectClassMethod }}', id: {{ $uniqueId }} })"
                            @else
                                wire:click.prevent="{{ $selectClassMethod }}({{ $uniqueId }})" 
                            @endif
                            class="relative rounded-xl border-2 p-3 sm:p-4 text-left transition duration-200 overflow-hidden {{ (int)$selectedClassId === (int)$uniqueId ? (!empty($class['is_promo']) ? 'border-amber-400 bg-amber-50 shadow-sm' : 'border-[#db2777] bg-[#db2777]/5 shadow-sm') : (!empty($class['is_promo']) ? 'border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 hover:border-amber-400 hover:shadow-sm' : 'border-slate-200 bg-white hover:border-[#db2777]/50 hover:shadow-sm') }}">
                            
                            @if(!empty($class['is_promo']))
                                <div class="absolute top-0 right-0 bg-amber-400 text-white text-[9px] font-extrabold px-2 py-0.5 rounded-bl-lg uppercase tracking-wider shadow-sm">
                                    PROMO
                                </div>
                            @endif

                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h4 class="font-bold {{ !empty($class['is_promo']) ? 'text-amber-900' : 'text-slate-900' }} text-xs sm:text-sm pr-10">
                                    {{ $class['name'] }}
                                    @if(!empty($class['rate_code']))
                                        <span class="text-xs font-semibold {{ !empty($class['is_promo']) ? 'text-amber-700' : 'text-slate-500' }} ml-1">({{ $class['rate_code'] }})</span>
                                    @endif
                                </h4>
                                <div class="flex items-center gap-1.5 flex-wrap justify-start w-full mt-1">
                                    @if(isset($class['tickets_available']))
                                        <span class="text-[10px] font-extrabold {{ (int)$selectedClassId === (int)$uniqueId ? (!empty($class['is_promo']) ? 'bg-amber-500 text-white' : 'bg-[#db2777] text-white') : 'bg-emerald-100 text-emerald-800 border border-emerald-200' }} px-2.5 py-0.5 rounded-full">
                                            {{ $class['tickets_available'] }} {{ \Illuminate\Support\Str::plural('seat', $class['tickets_available']) }} left
                                        </span>
                                    @endif
                                    @if(!empty($class['is_promo']))
                                        <span class="text-[9px] font-bold uppercase tracking-wider bg-rose-100 text-rose-700 px-2 py-0.5 rounded-full border border-rose-200">Non-refundable</span>
                                    @endif
                                </div>
                            </div>
                            
                            @if(!empty($class['is_promo']) && !empty($class['promo_duration_start']) && !empty($class['promo_duration_end']))
                                <p class="mt-1.5 text-[10px] text-amber-800 font-semibold flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Until {{ \Carbon\Carbon::parse($class['promo_duration_end'])->format('M d, Y h:i A') }}
                                </p>
                            @endif
                            
                            <p class="mt-2 text-lg font-extrabold {{ !empty($class['is_promo']) ? 'text-amber-600' : 'text-[#db2777]' }}">&#8369;{{ number_format($totalPrice, 2) }}</p>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
    <!-- Promotional Ticket Modal -->
    <div 
        x-data="{ show: false, method: '', id: null }"
        @open-promo-modal.window="show = true; method = $event.detail.method; id = $event.detail.id"
        x-show="show"
        style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto overflow-x-hidden bg-slate-900/60 backdrop-blur-sm"
    >
        <div 
            x-show="show" 
            @click.outside="show = false"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="relative w-full max-w-sm p-6 bg-white shadow-2xl rounded-2xl mx-4 border border-slate-100"
        >
            <div class="flex items-center justify-center w-14 h-14 mx-auto mb-4 bg-amber-100 rounded-full">
                <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            
            <h3 class="text-xl font-bold text-center text-slate-900 mb-2">Promotional Ticket</h3>
            
            <p class="text-sm text-center text-slate-600 mb-6">
                This is a promotional ticket and is <strong class="text-slate-800">STRICTLY non-refundable</strong>. It cannot be cancelled or rebooked. Do you wish to proceed?
            </p>

            <div class="flex gap-3 justify-center w-full">
                <button type="button" @click="show = false" class="flex-1 px-5 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Cancel</button>
                <button type="button" @click="$wire.call(method, id); show = false" class="flex-1 px-5 py-2.5 text-sm font-semibold text-white bg-amber-500 rounded-xl hover:bg-amber-600 shadow-sm transition">Proceed</button>
            </div>
        </div>
    </div>
</div>
