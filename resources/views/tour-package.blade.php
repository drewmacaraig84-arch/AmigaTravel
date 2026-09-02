@extends('layouts.app')

@section('content')
<div class="bg-transparent min-h-screen py-12 px-4 sm:px-6 lg:px-8" x-data="{ activeTab: 'domestic' }">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-16 relative ws-sbtn-container">
            @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'hero' })" class="ws-sbtn absolute top-0 right-0"></button> @endif
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700 bg-emerald-100 px-3 py-1 rounded-full">{{ data_get($pageContent, 'badge', 'Tour Packages') }}</span>
            <h1 class="mt-4 text-4xl sm:text-5xl font-black text-slate-900 tracking-tight">{{ data_get($pageContent, 'title', 'Explore Our Packages') }}</h1>
            <p class="mt-4 text-lg text-black font-semibold max-w-2xl mx-auto">
                {{ data_get($pageContent, 'description', 'Discover carefully curated domestic and international travel packages tailored to your budget and adventure style.') }}
            </p>

        @php
            $tours = \App\Models\Tour::where('is_active', true)->ordered()->get();
            $formatTour = function($tour) {
                return [
                    'id' => $tour->id,
                    'image' => $tour->image,
                    'alt' => $tour->tour_name,
                    'label' => $tour->promo,
                    'title' => $tour->tour_name,
                    'subtitle' => $tour->duration . ' · ' . $tour->destination,
                    'description' => $tour->highlights ?: $tour->remarks,
                    'price' => '₱' . number_format($tour->price_per_pax, 0),
                    'button_text' => 'View Details',
                    'button_link' => route('tours.show', $tour->id),
                ];
            };
            
            $tourPackages = [
                'domestic' => $tours->where('is_international', false)->map($formatTour)->toArray(),
                'international' => $tours->where('is_international', true)->map($formatTour)->toArray(),
            ];
            $supportedDestinations = $pageContent['supported_destinations'] ?? [
                [
                    'title' => 'Southeast Asia',
                    'destinations' => [
                        'Thailand (Bangkok)',
                        'Vietnam (Hanoi/HCMC)',
                        'Singapore',
                        'Indonesia (Bali)',
                    ],
                ],
                [
                    'title' => 'East Asia',
                    'destinations' => [
                        'South Korea (Seoul)',
                        'Japan (Tokyo/Osaka)',
                        'Taiwan (Taipei)',
                        'China (Shanghai)',
                        'Hong Kong',
                    ],
                ],
                [
                    'title' => 'Philippine Beaches',
                    'destinations' => [
                        'Puerto Galera',
                        'Boracay Island',
                        'El Nido, Palawan',
                        'Siargao Island',
                    ],
                ],
                [
                    'title' => 'Philippine Cities',
                    'destinations' => [
                        'Cebu City',
                        'Bohol (Tagbilaran)',
                        'Manila Metro',
                        'Davao City',
                    ],
                ],
            ];
        @endphp

            <!-- Interactive Tab Buttons -->
            <div class="mt-10 inline-flex p-1 bg-slate-200/80 rounded-2xl relative ws-sbtn-container">
                @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'tabs' })" class="ws-sbtn absolute -top-2 -right-2 z-10"></button> @endif
                <button @click="activeTab = 'domestic'" 
                        :class="activeTab === 'domestic' ? 'bg-white text-slate-900 shadow-sm' : 'text-black font-semibold hover:text-slate-900'"
                        class="px-6 py-2.5 rounded-xl font-bold text-sm transition cursor-pointer">
                    {{ data_get($pageContent, 'tab_domestic_label', 'Domestic Packages') }}
                </button>
                <button @click="activeTab = 'international'" 
                        :class="activeTab === 'international' ? 'bg-white text-slate-900 shadow-sm' : 'text-black font-semibold hover:text-slate-900'"
                        class="px-6 py-2.5 rounded-xl font-bold text-sm transition cursor-pointer">
                    {{ data_get($pageContent, 'tab_international_label', 'International Packages') }}
                </button>
            </div>
        </div>

        <div class="relative ws-sbtn-container w-full">
            @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'tour_packages' })" class="ws-sbtn absolute -top-8 -right-4 z-10"></button> @endif
            <!-- Domestic Packages Tab -->
            <div id="domestic-packages" x-show="activeTab === 'domestic'" x-transition class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 w-full">
            {{-- Server-side fallback packages (will be replaced by client-side JS when available) --}}
            @foreach(data_get($tourPackages, 'domestic', []) as $package)
                <a href="{{ data_get($package, 'id') ? route('tours.show', data_get($package, 'id')) : data_get($package, 'button_link', '#') }}" 
                   class="group relative rounded-[2rem] overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 ring-1 ring-slate-900/10 aspect-[4/5] min-h-[460px] bg-slate-900 flex flex-col justify-end block cursor-pointer">
                    
                    <!-- Full Image Background -->
                    <img src="{{ data_get($package, 'image') }}" alt="{{ data_get($package, 'alt') }}" class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-700 ease-out">
                    
                    <!-- Top Badges -->
                    <div class="absolute top-4 left-4 z-10 flex flex-wrap gap-2">
                        @if(data_get($package, 'label'))
                            <span class="text-[11px] font-extrabold text-white uppercase tracking-wider bg-black/60 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/20 shadow-md">
                                {{ data_get($package, 'label') }}
                            </span>
                        @endif
                        @if(data_get($package, 'subtitle'))
                            <span class="text-[11px] font-bold text-white bg-emerald-700/80 backdrop-blur-md px-3 py-1.5 rounded-full shadow-md border border-white/10">
                                {{ data_get($package, 'subtitle') }}
                            </span>
                        @endif
                    </div>

                    <!-- Default resting bottom bar (Fades out when hovered) -->
                    <div class="absolute inset-x-0 bottom-0 p-5 bg-gradient-to-t from-slate-950/85 via-slate-950/40 to-transparent flex items-end justify-between transition-opacity duration-300 group-hover:opacity-0 pointer-events-none">
                        <div>
                            <h3 class="font-black text-white text-lg drop-shadow-md leading-snug">{{ data_get($package, 'title') }}</h3>
                            <span class="text-xs font-bold text-emerald-400 drop-shadow">Starting from {{ data_get($package, 'price') }}</span>
                        </div>
                        <span class="p-2.5 rounded-full bg-white/20 backdrop-blur-md text-white border border-white/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </span>
                    </div>

                    <!-- Hover Glassmorphic Overlay with Details, Price & Action Button -->
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-900/85 to-slate-900/40 backdrop-blur-md p-6 sm:p-7 flex flex-col justify-end text-white opacity-0 group-hover:opacity-100 transition-all duration-400 ease-out translate-y-3 group-hover:translate-y-0">
                        <div>
                            <span class="text-xs font-bold text-emerald-400 uppercase tracking-widest block mb-1">
                                {{ data_get($package, 'subtitle') }}
                            </span>
                            <h3 class="font-black text-xl sm:text-2xl text-white tracking-tight leading-tight mb-2.5 drop-shadow">
                                {{ data_get($package, 'title') }}
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-200 line-clamp-4 leading-relaxed font-light mb-4">
                                {{ data_get($package, 'description') }}
                            </p>
                        </div>

                        <div class="pt-4 border-t border-white/15 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-300 font-medium uppercase tracking-wider block">Starting from</span>
                                <span class="font-black text-emerald-400 text-xl tracking-tight">{{ data_get($package, 'price') }}<span class="text-xs font-normal text-slate-300">/pax</span></span>
                            </div>
                            <span class="px-5 py-2.5 bg-[#ee018d] text-white text-xs font-black rounded-full hover:bg-pink-600 shadow-lg shadow-pink-600/30 transition-all flex items-center gap-1.5 group-hover:shadow-pink-500/50">
                                View Details
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- International Packages Tab -->
        <div id="international-packages" x-show="activeTab === 'international'" x-transition class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" style="display:none;">
            {{-- Server-side fallback packages (will be replaced by client-side JS when available) --}}
            @foreach(data_get($tourPackages, 'international', []) as $package)
                <a href="{{ data_get($package, 'id') ? route('tours.show', data_get($package, 'id')) : data_get($package, 'button_link', '#') }}" 
                   class="group relative rounded-[2rem] overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 ring-1 ring-slate-900/10 aspect-[4/5] min-h-[460px] bg-slate-900 flex flex-col justify-end block cursor-pointer">
                    
                    <!-- Full Image Background -->
                    <img src="{{ data_get($package, 'image') }}" alt="{{ data_get($package, 'alt') }}" class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-700 ease-out">
                    
                    <!-- Top Badges -->
                    <div class="absolute top-4 left-4 z-10 flex flex-wrap gap-2">
                        @if(data_get($package, 'label'))
                            <span class="text-[11px] font-extrabold text-white uppercase tracking-wider bg-black/60 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/20 shadow-md">
                                {{ data_get($package, 'label') }}
                            </span>
                        @endif
                        @if(data_get($package, 'subtitle'))
                            <span class="text-[11px] font-bold text-white bg-pink-700/80 backdrop-blur-md px-3 py-1.5 rounded-full shadow-md border border-white/10">
                                {{ data_get($package, 'subtitle') }}
                            </span>
                        @endif
                    </div>

                    <!-- Default resting bottom bar (Fades out when hovered) -->
                    <div class="absolute inset-x-0 bottom-0 p-5 bg-gradient-to-t from-slate-950/85 via-slate-950/40 to-transparent flex items-end justify-between transition-opacity duration-300 group-hover:opacity-0 pointer-events-none">
                        <div>
                            <h3 class="font-black text-white text-lg drop-shadow-md leading-snug">{{ data_get($package, 'title') }}</h3>
                            <span class="text-xs font-bold text-emerald-400 drop-shadow">Starting from {{ data_get($package, 'price') }}</span>
                        </div>
                        <span class="p-2.5 rounded-full bg-white/20 backdrop-blur-md text-white border border-white/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </span>
                    </div>

                    <!-- Hover Glassmorphic Overlay with Details, Price & Action Button -->
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-900/85 to-slate-900/40 backdrop-blur-md p-6 sm:p-7 flex flex-col justify-end text-white opacity-0 group-hover:opacity-100 transition-all duration-400 ease-out translate-y-3 group-hover:translate-y-0">
                        <div>
                            <span class="text-xs font-bold text-pink-400 uppercase tracking-widest block mb-1">
                                {{ data_get($package, 'subtitle') }}
                            </span>
                            <h3 class="font-black text-xl sm:text-2xl text-white tracking-tight leading-tight mb-2.5 drop-shadow">
                                {{ data_get($package, 'title') }}
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-200 line-clamp-4 leading-relaxed font-light mb-4">
                                {{ data_get($package, 'description') }}
                            </p>
                        </div>

                        <div class="pt-4 border-t border-white/15 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-300 font-medium uppercase tracking-wider block">Starting from</span>
                                <span class="font-black text-emerald-400 text-xl tracking-tight">{{ data_get($package, 'price') }}<span class="text-xs font-normal text-slate-300">/pax</span></span>
                            </div>
                            <span class="px-5 py-2.5 bg-[#ee018d] text-white text-xs font-black rounded-full hover:bg-pink-600 shadow-lg shadow-pink-600/30 transition-all flex items-center gap-1.5 group-hover:shadow-pink-500/50">
                                View Details
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        </div>

        <!-- Custom Package CTA -->
        <div class="mt-20 bg-gradient-to-br from-[#216417] to-[#14400e] rounded-[2rem] p-8 sm:p-12 text-center text-white shadow-xl flex flex-col items-center justify-center relative ws-sbtn-container">
            @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'cta' })" class="ws-sbtn absolute top-2 right-2"></button> @endif
            <div class="h-16 w-16 bg-emerald-500/20 rounded-full flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black mb-4">{{ data_get($pageContent, 'cta_title', 'Start Your Journey') }}</h2>
            <p class="text-emerald-100 max-w-xl mx-auto mb-8">
                {{ data_get($pageContent, 'cta_desc', 'Ready to explore? Book your tour package now and create unforgettable memories.') }}
            </p>
            <a href="{{ url('/contact-us') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-emerald-900 font-bold rounded-full shadow-lg hover:bg-emerald-50 transition cursor-pointer">
                {{ data_get($pageContent, 'cta_button', 'Book Now') }}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>

        <!-- Supported Destinations Section -->
        <div class="mt-20 text-center relative ws-sbtn-container">
            @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'supported_destinations' })" class="ws-sbtn absolute -top-4 -right-4 z-10"></button> @endif
            <h2 class="text-3xl font-black text-slate-900 mb-4">{{ data_get($pageContent, 'destinations_title', 'Popular Destinations') }}</h2>
            <p class="text-lg text-slate-600 max-w-2xl mx-auto mb-12">{{ data_get($pageContent, 'destinations_desc', 'See the world with our tailored services to these amazing destinations.') }}</p>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($supportedDestinations as $group)
                    <div class="bg-white/50 p-6 rounded-2xl shadow-sm border border-slate-100">
                        <h4 class="font-bold text-[#216417] text-sm uppercase tracking-wide mb-3">{{ data_get($group, 'title') }}</h4>
                        <ul class="text-sm text-slate-500 space-y-2">
                            @foreach(data_get($group, 'destinations', []) as $destination)
                                <li>{{ $destination }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const domContainer = document.getElementById('domestic-packages');
    const intlContainer = document.getElementById('international-packages');

    function cardHtml(pkg) {
        const image = pkg.image || 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=80';
        const label = pkg.promo || pkg.tag || '';
        const title = pkg.tour_name || pkg.name || '';
        const subtitle = pkg.duration || pkg.detail || '';
        const desc = pkg.highlights || pkg.inclusions || pkg.desc || '';
        const price = pkg.price_per_pax || pkg.price || '';
            // Build a book link that pre-fills the booking form via query params
            const params = new URLSearchParams();
            if (pkg.id) params.set('tour_id', pkg.id);
            if (pkg.destinations) params.set('destination', pkg.destinations);
            if (pkg.departure) params.set('origin', pkg.departure);

            // Determine mode: prefer explicit mode_of_transportation, else airline
            if (pkg.mode_of_transportation) {
                const explicitMode = pkg.mode_of_transportation.toString().trim().toLowerCase();
                if (explicitMode.includes('airline')) {
                    params.set('mode', 'airline');
                } else if (explicitMode.includes('ferry')) {
                    params.set('mode', 'ferry');
                }
            } else if (pkg.airline && pkg.airline.toString().trim() !== '' && pkg.airline.toString().toLowerCase() !== 'n/a') {
                params.set('mode', 'airline');
            }

            let durationDays = null;
            if (pkg.duration_days) {
                durationDays = pkg.duration_days;
            } else if (pkg.duration) {
                const m = pkg.duration.toString().match(/(\d+)\s*[dD]/);
                if (m && m[1]) {
                    durationDays = m[1];
                } else {
                    const m2 = pkg.duration.toString().match(/(\d+)\s*day/i);
                    if (m2 && m2[1]) {
                        durationDays = m2[1];
                    }
                }
            }

            if (pkg.trip_type) {
                const tripType = pkg.trip_type.toString().trim().toLowerCase();
                if (tripType.includes('round')) {
                    params.set('trip_type', 'round_trip');
                } else if (tripType.includes('one')) {
                    params.set('trip_type', 'one_way');
                }
            }

            // Use parsed dates from the API when available; otherwise try to parse raw available_dates
            if (pkg.available_dates_parsed && Array.isArray(pkg.available_dates_parsed) && pkg.available_dates_parsed.length > 0) {
                // pass the list as a comma-separated param and pick the first as default
                params.set('available_dates', pkg.available_dates_parsed.join(','));
                params.set('departure_date', pkg.available_dates_parsed[0]);
            } else if (pkg.available_dates) {
                const raw = pkg.available_dates.toString();
                if (!/not\s*specified/i.test(raw)) {
                    const candidates = raw.split(/[,;|\/]+/).map(s => s.trim()).filter(Boolean);
                    let picked = '';
                    const pickedList = [];
                    for (const c of candidates) {
                        if (/^\d{4}-\d{2}-\d{2}$/.test(c)) { pickedList.push(c); if (!picked) picked = c; continue; }
                        const d = new Date(c);
                        if (!isNaN(d.getTime())) {
                            const y = d.getFullYear();
                            const m = String(d.getMonth() + 1).padStart(2, '0');
                            const day = String(d.getDate()).padStart(2, '0');
                            const iso = `${y}-${m}-${day}`;
                            pickedList.push(iso);
                            if (!picked) picked = iso;
                        }
                    }
                    if (pickedList.length) {
                        params.set('available_dates', pickedList.join(','));
                        params.set('departure_date', pickedList[0]);
                    } else if (picked) {
                        params.set('departure_date', picked);
                    }
                }
            }

            // Send duration_days if provided
            if (durationDays) {
                params.set('duration_days', durationDays);
            }

            // Default to round-trip when a duration implies a multi-day package
            const computedDurationDays = params.get('duration_days');
            if (!params.has('trip_type') && computedDurationDays && parseInt(computedDurationDays, 10) > 1) {
                params.set('trip_type', 'round_trip');
            }

            // Automatically compute return date for fixed-duration round-trip packages
            const departureDate = params.get('departure_date');
            const tripType = params.get('trip_type');
            const returnDurationDays = params.get('duration_days');
            if (departureDate && returnDurationDays && tripType === 'round_trip') {
                const d = new Date(departureDate);
                const days = parseInt(returnDurationDays, 10);
                if (!isNaN(days) && days > 1 && !isNaN(d.getTime())) {
                    d.setDate(d.getDate() + days - 1);
                    const returnIso = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                    params.set('return_date', returnIso);
                }
            }

            const link = pkg.id ? `/tours/${pkg.id}` : '/book/new';
            const formattedPrice = price ? (price.toString().startsWith('₱') ? price : '₱' + (isNaN(Number(price)) ? price : Number(price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }))) : '—';

        return `
            <a href="${link}" class="group relative rounded-[2rem] overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-500 ring-1 ring-slate-900/10 aspect-[4/5] min-h-[460px] bg-slate-900 flex flex-col justify-end block cursor-pointer">
                <!-- Full Image Background -->
                <img src="${image}" alt="${title}" class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-110 transition-transform duration-700 ease-out">
                
                <!-- Top Badges -->
                <div class="absolute top-4 left-4 z-10 flex flex-wrap gap-2">
                    ${label ? `<span class="text-[11px] font-extrabold text-white uppercase tracking-wider bg-black/60 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/20 shadow-md">${label}</span>` : ''}
                    ${subtitle ? `<span class="text-[11px] font-bold text-white bg-emerald-700/80 backdrop-blur-md px-3 py-1.5 rounded-full shadow-md border border-white/10">${subtitle}</span>` : ''}
                </div>

                <!-- Default resting bottom bar (Fades out when hovered) -->
                <div class="absolute inset-x-0 bottom-0 p-5 bg-gradient-to-t from-slate-950/85 via-slate-950/40 to-transparent flex items-end justify-between transition-opacity duration-300 group-hover:opacity-0 pointer-events-none">
                    <div>
                        <h3 class="font-black text-white text-lg drop-shadow-md leading-snug">${title}</h3>
                        <span class="text-xs font-bold text-emerald-400 drop-shadow">Starting from ${formattedPrice}</span>
                    </div>
                    <span class="p-2.5 rounded-full bg-white/20 backdrop-blur-md text-white border border-white/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                </div>

                <!-- Hover Glassmorphic Overlay with Details, Price & Action Button -->
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/95 via-slate-900/85 to-slate-900/40 backdrop-blur-md p-6 sm:p-7 flex flex-col justify-end text-white opacity-0 group-hover:opacity-100 transition-all duration-400 ease-out translate-y-3 group-hover:translate-y-0">
                    <div>
                        ${subtitle ? `<span class="text-xs font-bold text-emerald-400 uppercase tracking-widest block mb-1">${subtitle}</span>` : ''}
                        <h3 class="font-black text-xl sm:text-2xl text-white tracking-tight leading-tight mb-2.5 drop-shadow">
                            ${title}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-200 line-clamp-4 leading-relaxed font-light mb-4">
                            ${desc}
                        </p>
                    </div>

                    <div class="pt-4 border-t border-white/15 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-slate-300 font-medium uppercase tracking-wider block">Starting from</span>
                            <span class="font-black text-emerald-400 text-xl tracking-tight">${formattedPrice}<span class="text-xs font-normal text-slate-300">/pax</span></span>
                        </div>
                        <span class="px-5 py-2.5 bg-[#ee018d] text-white text-xs font-black rounded-full hover:bg-pink-600 shadow-lg shadow-pink-600/30 transition-all flex items-center gap-1.5 group-hover:shadow-pink-500/50">
                            View Details
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </span>
                    </div>
                </div>
            </a>
        `;
    }

    fetch('/api/tours')
        .then(r => r.ok ? r.json() : Promise.reject(r))
        .then(list => {
            const domestic = [];
            const international = [];
            list.forEach(pkg => {
                const country = (pkg.country || '').toString().toLowerCase();
                if (country.includes('philipp')) domestic.push(pkg);
                else international.push(pkg);
            });

            if (domContainer) {
                domContainer.innerHTML = domestic.map(cardHtml).join('');
            }
            if (intlContainer) {
                intlContainer.innerHTML = international.map(cardHtml).join('');
            }
        })
        .catch(err => {
            console.error('Could not load tours:', err);
        });
});
</script>
@endpush
@endsection
