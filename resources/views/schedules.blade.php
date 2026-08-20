@extends('layouts.app')

@section('content')
{{-- Hero Section --}}
@php
    $origins = $routes->pluck('origin')->unique()->sort()->values();
    $destinations = $routes->pluck('destination')->unique()->sort()->values();
@endphp
<div x-data="{
    activeFilter: 'all',
    selectedOrigin: '',
    selectedDestination: '',
    swapRoute() {
        let tmp = this.selectedOrigin;
        this.selectedOrigin = this.selectedDestination;
        this.selectedDestination = tmp;
    },
    matchesSearch(origin, destination) {
        if (this.selectedOrigin && origin !== this.selectedOrigin) return false;
        if (this.selectedDestination && destination !== this.selectedDestination) return false;
        return true;
    },
    matchesMode(mode) {
        return this.activeFilter === 'all' || this.activeFilter === mode;
    }
}">
<div class="relative bg-[#216417] overflow-hidden">
    @if(session()->has('booking_draft'))
        <div class="w-full bg-pink-50/95 border-b border-pink-200 px-4 sm:px-6 lg:px-8 py-3.5 text-slate-900 shadow-sm relative z-20">
            <div class="max-w-7xl mx-auto flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-pink-700">You have a pending booking in progress.</p>
                    <p class="mt-0.5 text-xs text-slate-600">Return to complete your booking or cancel the draft to start a new one.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ url('/book/new') }}" class="inline-flex items-center justify-center rounded-full bg-pink-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-pink-700">Return to booking</a>
                    <form method="POST" action="{{ route('booking.draft.cancel') }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-full border border-pink-600 px-4 py-2 text-xs font-semibold text-pink-700 transition hover:bg-pink-100">Cancel draft</button>
                    </form>
                </div>
            </div>
        </div>
    @endif
    <div class="absolute inset-0 bg-no-repeat bg-cover bg-center pointer-events-none" style="background-image: url('{{ asset('images/world-map.svg') }}'); opacity: 0.1;"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-16 sm:pt-10 sm:pb-20">
        @include('partials.global-skeleton')
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-8">
            <div class="flex-1">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/10 backdrop-blur-sm px-4 py-1.5 mb-4">
                    <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium text-emerald-100">{{ data_get($pageContent ?? [], 'schedules_badge', 'Real-time schedules') }}</span>
                </div>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight">
                    {{ data_get($pageContent ?? [], 'schedules_title', 'Schedule and Routes') }}
                </h1>
                <p class="mt-2 text-xl font-medium text-emerald-100">
                    for {{ \Carbon\Carbon::parse($startDate)->format('F j') }} - {{ \Carbon\Carbon::parse($endDate)->format('F j, Y') }}
                </p>
                <p class="mt-3 text-base sm:text-lg text-emerald-100/80 max-w-xl pr-4">
                    {{ data_get($pageContent ?? [], 'schedules_description', 'Browse available ferry and airline routes with live pricing, departure times, and accommodation options.') }}
                </p>
            </div>

            {{-- Origin / Destination Search Box (Horizontal) --}}
            <div class="w-full relative z-10 max-w-md shrink-0">
                <div class="flex flex-col sm:flex-row items-center bg-white/10 backdrop-blur-md sm:rounded-full rounded-2xl border border-white/20 sm:h-14 shadow-lg shadow-black/10 relative">
                    {{-- Origin --}}
                    <div class="w-full sm:flex-1 h-14 sm:h-full relative group border-b sm:border-b-0 sm:border-r border-white/20">
                        <label class="absolute top-1.5 left-4 text-[10px] font-semibold uppercase tracking-wider text-emerald-100/70">Origin</label>
                        <select x-model="selectedOrigin" class="w-full h-full pt-4 pb-1 px-4 text-sm font-bold text-white bg-transparent border-0 focus:ring-0 focus:outline-none appearance-none cursor-pointer [&>option]:text-slate-800">
                            <option value="">All Origins</option>
                            @foreach($origins as $origin)
                                <option value="{{ $origin }}">{{ $origin }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                        </div>
                    </div>

                    {{-- Swap Button --}}
                    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-20 flex justify-center items-center">
                        <button @click="swapRoute()" type="button" class="group flex items-center justify-center w-8 h-8 rounded-full bg-[#216417] border border-white/30 text-white hover:bg-[#ee018d] hover:border-[#ee018d] hover:shadow-[0_0_15px_rgba(238,1,141,0.5)] transition-all duration-300 shadow-md" title="Swap origin and destination">
                            <svg class="w-4 h-4 transition-transform duration-300 group-hover:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        </button>
                    </div>

                    {{-- Destination --}}
                    <div class="w-full sm:flex-1 h-14 sm:h-full relative group">
                        <label class="absolute top-1.5 left-4 text-[10px] font-semibold uppercase tracking-wider text-emerald-100/70">Destination</label>
                        <select x-model="selectedDestination" class="w-full h-full pt-4 pb-1 px-4 text-sm font-bold text-white bg-transparent border-0 focus:ring-0 focus:outline-none appearance-none cursor-pointer [&>option]:text-slate-800">
                            <option value="">Where Are You Headed?</option>
                            @foreach($destinations as $dest)
                                <option value="{{ $dest }}">{{ $dest }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 9l4-4 4 4m0 6l-4 4-4-4"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stats --}}
        <div class="mt-10 grid grid-cols-2 sm:grid-cols-4 gap-4">
            @php
                $totalRoutes = $routes->count();
                $totalSchedules = $routes->sum(fn($r) => $r->schedules->count());
                $ferryRoutes = $routes->where('mode', 'ferry')->count() + $routes->whereNull('mode')->count();
                $airlineRoutes = $routes->where('mode', 'airline')->count();
            @endphp
            <div class="rounded-xl bg-white/10 backdrop-blur-sm border border-white/10 px-4 py-3">
                <p class="text-2xl font-bold text-white">{{ $totalRoutes }}</p>
                <p class="text-xs text-emerald-200/70 font-medium mt-0.5">Active Routes</p>
            </div>
            <div class="rounded-xl bg-white/10 backdrop-blur-sm border border-white/10 px-4 py-3">
                <p class="text-2xl font-bold text-white">{{ $totalSchedules }}</p>
                <p class="text-xs text-emerald-200/70 font-medium mt-0.5">Daily Departures</p>
            </div>
            <div class="rounded-xl bg-white/10 backdrop-blur-sm border border-white/10 px-4 py-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l6-3 6 3 6-3v13l-6 3-6-3-6 3V7z"/></svg>
                    <p class="text-2xl font-bold text-white">{{ $ferryRoutes }}</p>
                </div>
                <p class="text-xs text-emerald-200/70 font-medium mt-0.5">Ferry Routes</p>
            </div>
            <div class="rounded-xl bg-white/10 backdrop-blur-sm border border-white/10 px-4 py-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    <p class="text-2xl font-bold text-white">{{ $airlineRoutes }}</p>
                </div>
                <p class="text-xs text-emerald-200/70 font-medium mt-0.5">Airline Routes</p>
            </div>
        </div>
    </div>
</div>

{{-- Search + Filter --}}
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 relative z-10">
    {{-- Date Search Bar --}}
    <form action="{{ route('schedules') }}" method="GET" class="bg-white/80 backdrop-blur-md rounded-2xl shadow-lg shadow-slate-200/50 border border-slate-100 overflow-hidden mb-4 p-4 flex flex-col sm:flex-row gap-4 items-end">
        <div class="flex-1 w-full">
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Start Date</label>
            <input type="date" name="start_date" value="{{ $startDate }}" class="w-full rounded-xl border-slate-200 text-sm focus:ring-[#216417] focus:border-[#216417]">
        </div>
        <div class="flex-1 w-full">
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">End Date</label>
            <input type="date" name="end_date" value="{{ $endDate }}" class="w-full rounded-xl border-slate-200 text-sm focus:ring-[#216417] focus:border-[#216417]">
        </div>
        <button type="submit" class="w-full sm:w-auto px-6 py-2 bg-[#216417] hover:bg-[#1a5212] text-white font-semibold rounded-xl text-sm transition">
            Apply Dates
        </button>
    </form>

    {{-- Mode Filter Tabs --}}
    <div class="bg-white/70 backdrop-blur-md rounded-2xl shadow-lg shadow-slate-200/50 border border-slate-100 p-2 flex flex-wrap gap-2">
        <button @click="activeFilter = 'all'" :class="activeFilter === 'all' ? 'bg-[#216417] text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'" class="flex-1 sm:flex-none rounded-xl px-5 py-2.5 text-sm font-semibold transition-all duration-200 min-w-[100px]">
            All Routes
        </button>
        @if($ferryRoutes > 0)
        <button @click="activeFilter = 'ferry'" :class="activeFilter === 'ferry' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'" class="flex-1 sm:flex-none rounded-xl px-5 py-2.5 text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-2 min-w-[100px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l6-3 6 3 6-3v13l-6 3-6-3-6 3V7z"/></svg>
            Ferry
        </button>
        @endif
        @if($airlineRoutes > 0)
        <button @click="activeFilter = 'airline'" :class="activeFilter === 'airline' ? 'bg-amber-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100'" class="flex-1 sm:flex-none rounded-xl px-5 py-2.5 text-sm font-semibold transition-all duration-200 flex items-center justify-center gap-2 min-w-[100px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
            Airline
        </button>
        @endif

        {{-- Route Cards --}}
        <div class="w-full mt-4 space-y-6">
            @forelse($routes as $route)
                @php
                    $routeMode = $route->mode ?? 'ferry';
                    $isFerry = $routeMode !== 'airline';
                    $modeColor = $isFerry ? 'blue' : 'amber';
                    $modeIcon = $isFerry ? 'ferry' : 'airline';
                @endphp
                <div
                    x-show="matchesMode('{{ $routeMode }}') && matchesSearch('{{ $route->origin }}', '{{ $route->destination }}')"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="rounded-2xl border border-slate-200 bg-white/75 backdrop-blur-md overflow-hidden hover:shadow-lg transition-shadow duration-300"
                >
                    {{-- Route Header --}}
                    <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-slate-50/70 to-white/70">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center gap-4">
                                {{-- Mode Badge --}}
                                <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center {{ $isFerry ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600' }}">
                                    @if($isFerry)
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l6-3 6 3 6-3v13l-6 3-6-3-6 3V7z"/></svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                    @endif
                                </div>

                                <div>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                        <h2 class="text-xl font-bold text-slate-900 break-words">{{ $route->origin }}</h2>
                                        <div class="flex items-center gap-1 text-slate-400 shrink-0">
                                            <div class="w-1.5 h-1.5 rounded-full bg-slate-300 hidden sm:block"></div>
                                            <div class="w-4 sm:w-8 h-px bg-slate-300"></div>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                            <div class="w-4 sm:w-8 h-px bg-slate-300"></div>
                                            <div class="w-1.5 h-1.5 rounded-full bg-slate-300 hidden sm:block"></div>
                                        </div>
                                        <h2 class="text-xl font-bold text-slate-900 break-words">{{ $route->destination }}</h2>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $isFerry ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ ucfirst($routeMode) }}
                                        </span>
                                        <span class="text-sm text-slate-500">{{ $route->vehicle?->operator ?? $route->operator ?? '' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <p class="text-xs text-slate-500 font-medium">{{ $route->schedules->count() }} {{ Str::plural('departure', $route->schedules->count()) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Schedule Cards Grid --}}
                    <div class="p-4 sm:p-6">
                        @if($route->schedules->count() > 0)
                            <div x-data="{
                                activeSlide: 0,
                                totalSlides: {{ $route->schedules->count() }},
                                itemsPerPage: 3,
                                get pages() { return Math.ceil(this.totalSlides / this.itemsPerPage); },
                                init() {
                                    this.updateItemsPerPage();
                                    window.addEventListener('resize', () => this.updateItemsPerPage());
                                    
                                    $refs.slider.addEventListener('scroll', () => {
                                        let page = Math.round($refs.slider.scrollLeft / $refs.slider.clientWidth);
                                        this.activeSlide = page;
                                    });
                                },
                                updateItemsPerPage() {
                                    this.itemsPerPage = window.innerWidth >= 1280 ? 3 : (window.innerWidth >= 768 ? 2 : 1);
                                },
                                goToPage(page) {
                                    $refs.slider.scrollTo({ left: page * $refs.slider.clientWidth, behavior: 'smooth' });
                                }
                            }" class="relative w-full group">
                                
                                <style>
                                    .hide-scroll::-webkit-scrollbar { display: none; }
                                </style>
                                
                                {{-- Prev Button --}}
                                <button x-show="pages > 1" @click="goToPage(Math.max(0, activeSlide - 1))" class="absolute -left-4 top-1/2 -translate-y-1/2 z-10 w-10 h-10 bg-white rounded-full shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] border border-slate-100 flex items-center justify-center text-slate-600 hover:text-[#216417] hover:border-[#216417] transition-all opacity-0 group-hover:opacity-100 disabled:opacity-0 disabled:cursor-not-allowed" :disabled="activeSlide === 0">
                                    <svg class="w-5 h-5 pr-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                
                                {{-- Next Button --}}
                                <button x-show="pages > 1" @click="goToPage(Math.min(pages - 1, activeSlide + 1))" class="absolute -right-4 top-1/2 -translate-y-1/2 z-10 w-10 h-10 bg-white rounded-full shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] border border-slate-100 flex items-center justify-center text-slate-600 hover:text-[#216417] hover:border-[#216417] transition-all opacity-0 group-hover:opacity-100 disabled:opacity-0 disabled:cursor-not-allowed" :disabled="activeSlide === pages - 1">
                                    <svg class="w-5 h-5 pl-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </button>

                                {{-- Slider --}}
                                <div x-ref="slider" class="flex overflow-x-auto snap-x snap-mandatory gap-4 pb-4 hide-scroll" style="scrollbar-width: none;">
                                    @foreach($route->schedules as $schedule)
                                        @php 
                                            $departureIso = \Carbon\Carbon::parse($schedule->departure_time)->toIso8601String();
                                            $schIsPast = \Carbon\Carbon::parse($schedule->departure_time)->isPast(); 
                                        @endphp
                                        <div class="snap-start shrink-0 w-full md:w-[calc(50%-0.5rem)] xl:w-[calc(33.333%-0.67rem)]"
                                             x-data="{ isPast: {{ $schIsPast ? 'true' : 'false' }}, depTime: new Date('{{ $departureIso }}') }"
                                             x-init="if (!isPast) { setInterval(() => { if (new Date() >= depTime) { isPast = true; } }, 1000) }">
                                            <div class="h-full group relative rounded-xl border bg-white/80 backdrop-blur-sm p-4 transition-all duration-200"
                                                 :class="isPast ? 'border-slate-200 opacity-60' : 'border-slate-200 hover:border-[#216417]/30 hover:shadow-md'">
                                                {{-- Departed ribbon --}}
                                                <div x-show="isPast" style="display: {{ $schIsPast ? 'block' : 'none' }};" class="absolute top-0 right-0 z-10">
                                                    <div class="bg-slate-500 text-white text-[9px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-bl-xl rounded-tr-xl shadow-sm">
                                                        Departed
                                                    </div>
                                                </div>
                                                {{-- Service Name & Time --}}
                                                <div class="flex items-start justify-between mb-3">
                                                    <div class="flex items-center gap-3">
                                                        @php
                                                            $opName = $route->vehicle?->operator ?? $route->operator ?? '';
                                                            $opLogo = null;
                                                            if (stripos($opName, '2GO') !== false) $opLogo = '2GO-Logo.png';
                                                            elseif (stripos($opName, 'Starlite') !== false) $opLogo = 'Starlite_Logo.png';
                                                            elseif (stripos($opName, 'Cebu') !== false) $opLogo = 'CebuPecific-Logo.png';
                                                            elseif (stripos($opName, 'Pal') !== false || stripos($opName, 'Philippine') !== false) $opLogo = 'Pal-Logo.jfif';
                                                            elseif (stripos($opName, 'AirAsia') !== false) $opLogo = 'AirAsia-Logo.png';
                                                        @endphp
                                                        @if($opLogo)
                                                            <div class="w-10 h-10 shrink-0 bg-slate-50 rounded border border-slate-100 flex items-center justify-center p-1 overflow-hidden">
                                                                <img src="{{ asset('images/' . $opLogo) }}" alt="{{ $opName }}" class="w-full h-full object-contain">
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <h3 class="font-bold text-slate-900 text-sm leading-tight">{{ $schedule->service_name }}</h3>
                                                            @if($opName)
                                                                <p class="text-xs text-slate-500 mt-0.5">{{ $opName }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    {{-- Availability Badge --}}
                                                    @php
                                                        $totalTickets = $schedule->scheduleAccommodations->sum('tickets_available');
                                                    @endphp
                                                    @if($totalTickets <= 0)
                                                        <div class="ml-auto shrink-0">
                                                            <span class="inline-flex items-center rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-semibold text-red-600 border border-red-100">
                                                                Sold out
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Time Bar --}}
                                                <div class="flex items-center gap-3 py-3 border-t border-b border-slate-100">
                                                    <div class="text-center">
                                                        <p class="text-sm sm:text-base font-bold text-slate-900 leading-tight">{{ $schedule->formatted_departure }}</p>
                                                        <p class="text-[10px] uppercase tracking-wider text-slate-400 mt-1">Depart</p>
                                                    </div>
                                                    <div class="flex-1 flex flex-col items-center">
                                                        <p class="text-[10px] font-medium text-slate-400 mb-1">{{ $schedule->duration_label }}</p>
                                                        <div class="relative w-full h-px bg-slate-200">
                                                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-[#216417]"></div>
                                                            <div class="absolute right-0 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-[#216417]"></div>
                                                            <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
                                                                @if($isFerry)
                                                                    <svg class="w-3.5 h-3.5 text-[#216417]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l6-3 6 3 6-3v13l-6 3-6-3-6 3V7z"/></svg>
                                                                @else
                                                                    <svg class="w-3.5 h-3.5 text-[#216417]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="text-center">
                                                        <p class="text-sm sm:text-base font-bold text-slate-900 leading-tight">{{ $schedule->formatted_arrival }}</p>
                                                        <p class="text-[10px] uppercase tracking-wider text-slate-400 mt-1">Arrive</p>
                                                    </div>
                                                </div>

                                                {{-- Details --}}
                                                <div class="mt-3 space-y-2">
                                                    {{-- Departure Date --}}
                                                    <div class="flex items-center gap-2">
                                                        <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                        <p class="text-xs font-semibold text-slate-700">
                                                            {{ \Carbon\Carbon::parse($schedule->departure_time)->format('l, F j, Y') }}
                                                        </p>
                                                    </div>

                                                    {{-- Accommodation / Classes --}}
                                                    <div class="flex items-start gap-2">
                                                        <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                        <p class="text-xs text-slate-600 leading-relaxed">{{ $schedule->accommodation_label }}</p>
                                                    </div>

                                                    {{-- Availability --}}
                                                    @if($schedule->availability_label && $schedule->availability_label !== 'Available')
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-3.5 h-3.5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                            <p class="text-xs font-medium text-amber-600">{{ $schedule->availability_label }}</p>
                                                        </div>
                                                    @else
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            <p class="text-xs font-medium text-emerald-600">Available</p>
                                                        </div>
                                                    @endif
                                                </div>

                                                {{-- Book Now Button --}}
                                                <div class="mt-4 pt-4 border-t border-slate-100">
                                                    <template x-if="isPast">
                                                        <div class="w-full inline-flex justify-center items-center gap-1.5 rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-500 cursor-not-allowed">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                            Schedule Departed
                                                        </div>
                                                    </template>
                                                    <template x-if="!isPast">
                                                        <a href="{{ url('/book/new?trip_type=one_way&mode=' . urlencode($isFerry ? 'ferry' : 'airline') . '&operator=' . urlencode($opName) . '&origin=' . urlencode($route->origin) . '&destination=' . urlencode($route->destination) . '&departure_date=' . urlencode(\Carbon\Carbon::parse($schedule->departure_time)->format('Y-m-d'))) }}" class="w-full inline-flex justify-center items-center gap-1.5 rounded-lg bg-[#216417] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#1a5212]">
                                                            Book Now
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                                        </a>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                {{-- Pagination Dots --}}
                                <div x-show="pages > 1" class="flex justify-center items-center gap-2 mt-4 pb-2">
                                    <template x-for="page in pages" :key="page">
                                        <button @click="goToPage(page - 1)" 
                                            :class="activeSlide === (page - 1) ? 'w-8 bg-[#216417]' : 'w-2 bg-slate-300 hover:bg-[#216417]/50'" 
                                            class="h-2 rounded-full transition-all duration-300"
                                            :aria-label="'Go to slide page ' + page">
                                        </button>
                                    </template>
                                </div>
                            </div>


                        @else
                            <div class="text-center py-8 text-slate-400">
                                <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-sm font-medium">No active schedules for this route</p>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-16 text-center">
                    <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="text-lg font-bold text-slate-600 mb-2">No Schedules Available</h3>
                    <p class="text-sm text-slate-500 max-w-sm mx-auto">No active schedules at the moment. Please check back later or contact us for the latest route information.</p>
                    <a href="{{ url('/contact-us') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-[#216417] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#1a5212]">
                        Contact Us
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Bottom CTA --}}
@if($routes->count() > 0)
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 mb-4">
    <div class="rounded-2xl bg-gradient-to-r from-[#216417] to-[#1a5212] p-8 sm:p-10 text-center relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <svg class="absolute -right-10 -top-10 w-48 h-48 text-white" viewBox="0 0 200 200" fill="currentColor"><circle cx="100" cy="100" r="100"/></svg>
            <svg class="absolute -left-10 -bottom-10 w-36 h-36 text-white" viewBox="0 0 200 200" fill="currentColor"><circle cx="100" cy="100" r="100"/></svg>
        </div>
        <div class="relative z-10">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white">Ready to Travel?</h2>
            <p class="mt-2 text-emerald-100/80 max-w-lg mx-auto">Book your ferry or airline ticket in just a few easy steps. Best rates guaranteed.</p>
            <div class="mt-6 flex flex-wrap gap-3 justify-center">
                <a href="{{ url('/book/new') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-8 py-3.5 text-sm font-bold text-[#216417] shadow-lg transition hover:bg-emerald-50 hover:shadow-xl">
                    Start Booking
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="{{ url('/contact-us') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/30 px-8 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10">
                    Need Help?
                </a>
            </div>
        </div>
    </div>
</div>
@endif
</div>
@endsection
