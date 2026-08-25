@extends('layouts.app')

@section('content')
<div class="bg-transparent min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-16 relative ws-sbtn-container amiga-animate-on-scroll amiga-transition">
            @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'hero' })" class="ws-sbtn absolute top-0 right-0"></button> @endif
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700 bg-emerald-100 px-3 py-1 rounded-full">{{ data_get($pageContent, 'badge', 'Services') }}</span>
            <h1 class="mt-4 text-4xl sm:text-5xl font-black text-slate-900 tracking-tight">{{ data_get($pageContent, 'title', 'Our Travel Services') }}</h1>
            <p class="mt-4 text-lg text-black font-semibold max-w-2xl mx-auto">
                {{ data_get($pageContent, 'description', 'Explore a full range of reliable travel, transit ticketing, and customizable packages designed to make your journey completely hassle-free.') }}
            </p>
        </div>

        @php
            $serviceCta = $pageContent['service_cta'] ?? [
                'badge' => 'New Booking System',
                'title' => 'Book Ferry Tickets Directly Online',
                'description' => 'Quickly check available schedules, fares, and cabins for 2GO and Starlite. Complete your passenger credentials and print tickets instantly.',
                'button_text' => 'Start Direct Booking',
            ];
            
            // New Travel & Booking Services Cards
            $travelServiceCards = [
                [
                    'title' => '2GO Booking',
                    'description' => 'Book premier overnight ship accommodation and fast cargo transits with 2GO. Ideal for family retreats, business logistics, and leisure trips.',
                    'note' => 'Available Online',
                    'image' => 'images/2GO-Logo.png',
                    'button_text' => 'Book Now',
                    'link' => '/book/new?operator=' . urlencode('2GO') . '&trip_type=one_way&mode=ferry',
                    'color' => 'text-pink-600',
                ],
                [
                    'title' => 'Starlite Ferries Booking',
                    'description' => 'Affordable regional transits between Batangas, Calapan, and Roxas. We manage standard ferry bookings and roll-on/roll-off (RoRo) cargo slots.',
                    'note' => 'Available Online',
                    'image' => 'images/Starlite_Logo.png',
                    'button_text' => 'Book Now',
                    'link' => '/book/new?operator=' . urlencode('Starlite') . '&trip_type=one_way&mode=ferry',
                    'color' => 'text-emerald-700',
                ],
                [
                    'title' => 'Cebu Pacific',
                    'description' => 'Domestic and international flights powered by leading carriers including AirAsia, Cebu Pacific, and Philippine Airline (PAL). Hassle-free check-ins and seat bookings.',
                    'note' => 'PAL, CebuPac, AirAsia',
                    'image' => 'images/CebuPecific-Logo.png',
                    'button_text' => 'Book Now',
                    'link' => '/book/new?operator=' . urlencode('Cebu Pacific') . '&trip_type=one_way&mode=airline',
                    'color' => 'text-blue-600',
                ],
                [
                    'title' => 'Philippine Airlines',
                    'description' => 'Philippine Airlines flights with premium support and flexible fare options.',
                    'note' => 'PAL & International',
                    'image' => 'images/Pal-Logo.jfif',
                    'button_text' => 'Book Now',
                    'link' => '/book/new?operator=' . urlencode('Philippine Airlines') . '&trip_type=one_way&mode=airline',
                    'color' => 'text-purple-600',
                ],
                [
                    'title' => 'AirAsia',
                    'description' => 'Find low-cost airline tickets and convenient domestic connections.',
                    'note' => 'Low Fare Flights',
                    'image' => 'images/AirAsia-Logo.png',
                    'button_text' => 'Book Now',
                    'link' => '/book/new?operator=' . urlencode('AirAsia') . '&trip_type=one_way&mode=airline',
                    'color' => 'text-orange-600',
                ],
                [
                    'title' => 'Custom Travel Arrangements',
                    'description' => 'Tailored travel packages for corporate retreats, family reunions, and large groups. We handle flight connections, hotel accommodation blocks, and group transport.',
                    'note' => 'Tailored For Groups',
                    'image' => 'images/amiga-logo-transparent.png',
                    'button_text' => 'Learn more',
                    'link' => '/contact-us',
                    'color' => 'text-teal-700',
                ],
            ];
            
            $serviceCards = $pageContent['service_cards'] ?? [
                [
                    'icon' => 'M13 5l7 7-7 7M5 5l7 7-7 7',
                    'title' => '2GO Onboard Training',
                    'description' => 'Comprehensive onboarding and orientation programs for individuals joining 2GO operations, covering safety, customer service, and onboard protocols.',
                    'note' => 'For New Hires & Trainees',
                    'link' => url('/contact-us'),
                    'color' => 'text-pink-600',
                ],
                [
                    'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
                    'title' => 'Educational Tour',
                    'description' => 'Educational tours for students and academic groups, featuring visits to travel facilities, ports, and cultural sites for immersive learning experiences.',
                    'note' => 'For Schools & Groups',
                    'link' => url('/contact-us'),
                    'color' => 'text-emerald-700',
                ],
                [
                    'icon' => 'M12 14l9-5-9-5-9 5 9 5z',
                    'title' => 'Stay and Learn',
                    'description' => 'Combined accommodation and learning packages, perfect for workshops, seminars, and training sessions with comfortable stays included.',
                    'note' => 'Workshops & Seminars',
                    'link' => url('/contact-us'),
                    'color' => 'text-blue-600',
                ],
                [
                    'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4',
                    'title' => 'Marine Related Trainings',
                    'description' => 'Specialized training programs for maritime professionals, including safety, navigation, and vessel operations in partnership with marine institutions.',
                    'note' => 'For Mariners & Seafarers',
                    'link' => url('/contact-us'),
                    'color' => 'text-purple-600',
                ],
                [
                    'icon' => 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8',
                    'title' => 'Transport',
                    'description' => 'Reliable transport solutions including ferry, airline, and land transfers for individuals, groups, and corporate travel needs.',
                    'note' => 'Multi-Modal Transport',
                    'link' => url('/contact-us'),
                    'color' => 'text-orange-600',
                ],
                [
                    'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
                    'title' => 'Visa & Passport Assistance',
                    'description' => 'Complete assistance with visa applications and passport processing, helping you prepare required documents and navigate application procedures.',
                    'note' => 'Document Processing',
                    'link' => url('/contact-us'),
                    'color' => 'text-teal-700',
                ],
            ];
        @endphp
        <div class="bg-gradient-to-br from-[#216417] to-[#14400e] text-white rounded-[2rem] p-8 sm:p-12 shadow-xl mb-12 flex flex-col md:flex-row items-center justify-between gap-8 relative ws-sbtn-container amiga-animate-on-scroll amiga-transition">
            @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'service_cta' })" class="ws-sbtn absolute top-2 right-2"></button> @endif
            <div class="max-w-xl text-center md:text-left">
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-300 bg-white/10 px-3 py-1 rounded-full">{{ data_get($serviceCta, 'badge') }}</span>
                <h2 class="mt-3 text-2xl sm:text-3xl font-black">{{ data_get($serviceCta, 'title') }}</h2>
                <p class="mt-2 text-emerald-100/90 text-sm sm:text-base">
                    {{ data_get($serviceCta, 'description') }}
                </p>
            </div>
            <div class="flex-shrink-0">
                <a href="{{ url('/book/new') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-white text-emerald-900 font-bold rounded-full shadow-lg hover:bg-emerald-50 transition cursor-pointer">
                    {{ data_get($serviceCta, 'button_text') }}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- Travel & Booking Services Section -->
        <div class="mb-16 relative ws-sbtn-container amiga-animate-on-scroll amiga-transition">
            @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'travel_service_cards' })" class="ws-sbtn absolute -top-4 -right-4"></button> @endif
            <div class="text-center mb-10">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900">{{ data_get($pageContent, 'travel_services_title', 'Travel & Booking Services') }}</h2>
                <p class="mt-3 text-black font-semibold max-w-2xl mx-auto">{{ data_get($pageContent, 'travel_services_desc', 'Choose from our ferry, airline, tour, and custom travel arrangements.') }}</p>
            </div>
            
@php
                $defaultServiceImage = 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80';
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-6 lg:gap-8">
                @foreach($travelServiceCards as $card)
                @php
                    $rawCardImage = data_get($card, 'image');
                    if (is_array($rawCardImage)) {
                        $rawCardImage = array_values(array_filter($rawCardImage))[0] ?? null;
                    }
                    $cardImage = $rawCardImage
                        ? (str_starts_with($rawCardImage, 'http://') || str_starts_with($rawCardImage, 'https://')
                            ? $rawCardImage
                            : (str_starts_with($rawCardImage, 'images/')
                                ? asset($rawCardImage)
                                : (storage_asset_path($rawCardImage) ?: $defaultServiceImage)))
                        : $defaultServiceImage;
                    $cardLink = data_get($card, 'link', url('/book/new'));
                    $buttonText = data_get($card, 'button_text', 'Learn more');
                @endphp
                <a href="{{ $cardLink }}" class="group overflow-hidden rounded-xl sm:rounded-[2rem] bg-white/85 backdrop-blur-md border border-slate-200 shadow-sm hover:shadow-xl transition duration-200 flex flex-col">
                    <div class="h-20 sm:h-36 w-full bg-white/80 flex items-center justify-center p-2 sm:p-8 border-b border-slate-100">
                        <img src="{{ $cardImage }}" alt="{{ data_get($card, 'title') }}" class="max-h-full max-w-full object-contain transition duration-200 group-hover:scale-105" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80';" />
                    </div>
                    <div class="p-2.5 sm:p-6 flex flex-col flex-grow">
                        <span class="inline-flex items-center gap-1 text-[8px] sm:text-[10px] font-semibold text-[#ee018d] uppercase tracking-wider mb-1 sm:mb-3 leading-tight truncate">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm-1 15v-4H7l5-7v4h4l-5 7z" />
                            </svg>
                            <span class="truncate">{{ data_get($card, 'note', 'Travel Service') }}</span>
                        </span>
                        <h3 class="text-xs sm:text-lg font-bold text-slate-900 mb-1 sm:mb-2 leading-tight truncate">{{ data_get($card, 'title') }}</h3>
                        <p class="text-[9px] sm:text-sm text-slate-600 mb-2 sm:mb-4 flex-grow line-clamp-2 sm:line-clamp-none leading-tight">{{ data_get($card, 'description') }}</p>
                        <button class="w-full bg-[#ee018d] text-white text-[10px] sm:text-sm font-bold py-1.5 px-2 sm:py-3 sm:px-6 rounded-full hover:bg-pink-700 transition-colors leading-tight">
                            {{ $buttonText }}
                        </button>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

        <!-- Services Grid -->
        <div x-data="{
                selectedService: null,
                openModal(service) { this.selectedService = service; },
                closeModal() { this.selectedService = null; }
            }"
            class="relative ws-sbtn-container mt-16 amiga-animate-on-scroll amiga-transition"
        >
            @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'service_cards' })" class="ws-sbtn absolute -top-4 -right-4 z-10"></button> @endif
            <div class="text-center mb-10">
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900">{{ data_get($pageContent, 'specialized_services_title', 'Specialized Services') }}</h2>
                <p class="mt-3 text-black font-semibold max-w-2xl mx-auto">{{ data_get($pageContent, 'specialized_services_desc', 'Explore our additional programs and assistance offerings.') }}</p>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-6 lg:gap-8">
                @foreach($serviceCards as $card)
                @php
                    $rawCardImage = data_get($card, 'image');
                    if (is_array($rawCardImage)) {
                        $rawCardImage = array_values(array_filter($rawCardImage))[0] ?? null;
                    }

                    if ($rawCardImage) {
                        if (str_starts_with($rawCardImage, 'http://') || str_starts_with($rawCardImage, 'https://')) {
                            $cardImage = $rawCardImage;
                        } elseif (str_starts_with($rawCardImage, 'images/')) {
                            $cardImage = asset($rawCardImage);
                        } else {
                            $cardImage = storage_asset_path($rawCardImage) ?: $defaultServiceImage;
                        }
                    } else {
                        $cardImage = $defaultServiceImage;
                    }

                    $modalData = json_encode(array_merge($card, ['image' => $cardImage]));
                @endphp
                <div class="group overflow-hidden rounded-xl sm:rounded-[2rem] bg-white/85 backdrop-blur-md border border-slate-200 shadow-sm hover:shadow-xl transition duration-200 flex flex-col">
                    <img src="{{ $cardImage }}" alt="{{ data_get($card, 'title') }}" class="w-full aspect-video object-cover transition duration-200 group-hover:scale-105" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80';" />
                    <div class="p-2.5 sm:p-6 flex flex-col flex-grow">
                        <span class="inline-flex items-center gap-1 text-[8px] sm:text-[10px] font-semibold text-[#216417] uppercase tracking-wider mb-1 sm:mb-3 leading-tight truncate">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm-1 15v-4H7l5-7v4h4l-5 7z" />
                            </svg>
                            <span class="truncate">{{ data_get($card, 'note', 'Service') }}</span>
                        </span>
                        <h3 class="text-xs sm:text-lg font-bold text-slate-900 mb-1 sm:mb-2 leading-tight truncate">{{ data_get($card, 'title') }}</h3>
                        <p class="text-[9px] sm:text-sm text-slate-600 mb-2 sm:mb-4 flex-grow line-clamp-2 sm:line-clamp-none leading-tight">{{ data_get($card, 'description') }}</p>
                        <button type="button" @click.prevent="openModal({{ $modalData }})" class="w-full bg-[#216417] text-white text-[10px] sm:text-sm font-bold py-1.5 px-2 sm:py-3 sm:px-6 rounded-full hover:bg-emerald-800 transition-colors leading-tight">
                            {{ data_get($card, 'button_text', 'Learn more') }}
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

            <div x-show="selectedService" x-cloak x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/70 flex items-center justify-center p-4">
                <div class="relative max-w-3xl w-full rounded-[2rem] bg-white shadow-2xl overflow-hidden">
                    <button type="button" @click="closeModal()" class="absolute right-4 top-4 rounded-full bg-white/90 p-2 text-slate-700 hover:bg-white">
                        <span class="sr-only">Close</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.414L11.414 10l4.95 4.95a1 1 0 01-1.414 1.414L10 11.414l-4.95 4.95a1 1 0 01-1.414-1.414L8.586 10 3.636 5.05a1 1 0 011.414-1.414L10 8.586z" clip-rule="evenodd"/></svg>
                    </button>
                    <img x-bind:src="selectedService.image" x-bind:alt="selectedService.title" class="w-full max-h-80 object-cover" />
                    <div class="p-8">
                        <h2 class="text-2xl font-bold text-slate-900 mb-3" x-text="selectedService.title"></h2>
                        <p class="text-sm text-slate-500 mb-4" x-text="selectedService.note"></p>
                        <p class="text-sm text-slate-600 leading-relaxed" x-text="selectedService.description"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
