<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#008000">
        <link rel="manifest" href="/manifest.json">

        <title>{{ config('app.name', 'Amiga Gracia Travel Service') }}</title>

        {{-- ═══════════════════════════════════════════════════════
             FAVICON SUITE
             Google uses these to show the site logo next to search
             listings. Requirements: square PNG, multiples of 48px.
             app-icon-original.png is already 512×512 and square.
        ═══════════════════════════════════════════════════════ --}}
        <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('images/app-icon-original.png') }}">
        <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/app-icon-original.png') }}">
        <link rel="icon" type="image/png" sizes="96x96"  href="{{ asset('images/app-icon-original.png') }}">
        <link rel="icon" type="image/png" sizes="48x48"  href="{{ asset('images/app-icon-original.png') }}">
        <link rel="icon" type="image/x-icon"             href="/favicon.ico">
        <link rel="apple-touch-icon" sizes="180x180"     href="{{ asset('images/app-icon-original.png') }}">
        <link rel="shortcut icon"                        href="{{ asset('images/app-icon-original.png') }}">

        {{-- ═══════════════════════════════════════════════════════
             JSON-LD STRUCTURED DATA
             Implements:
               • Organization  – logo, name, contact, sameAs (social)
               • LocalBusiness – address, geo, opening hours, GBP link
               • WebSite       – sitelinks search box eligibility
             These are the primary signals Google reads to display
             the logo thumbnail beside the website search listing.
        ═══════════════════════════════════════════════════════ --}}
        <script type="application/ld+json">
        @verbatim
        {
          "@context": "https://schema.org",
          "@graph": [
            {
              "@type": ["Organization", "LocalBusiness", "TravelAgency"],
              "@id": "https://www.amigagracia.com/#organization",
              "name": "Amiga Gracia Travel Services",
              "alternateName": "Amiga Gracia",
              "url": "https://www.amigagracia.com",
              "logo": {
                "@type": "ImageObject",
                "url": "https://www.amigagracia.com/images/app-icon-original.png",
                "width": 512,
                "height": 512,
                "caption": "Amiga Gracia Travel Services Logo"
              },
              "image": "https://www.amigagracia.com/images/app-icon-original.png",
              "description": "Kay Amiga, Hassle Free Ka! Book ferry tickets (2GO, Starlite), airline tickets (Cebu Pacific, PAL, AirAsia), hotel reservations, and tour packages in Calapan City, Oriental Mindoro.",
              "telephone": "+63-930-928-4278",
              "email": "agtsreservation@amigagracia.com",
              "address": {
                "@type": "PostalAddress",
                "streetAddress": "Roxas Drive, Libis",
                "addressLocality": "Calapan City",
                "addressRegion": "Oriental Mindoro",
                "postalCode": "5200",
                "addressCountry": "PH"
              },
              "geo": {
                "@type": "GeoCoordinates",
                "latitude": 13.4116,
                "longitude": 121.1803
              },
              "openingHoursSpecification": [
                {
                  "@type": "OpeningHoursSpecification",
                  "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],
                  "opens": "08:00",
                  "closes": "18:00"
                }
              ],
              "priceRange": "₱₱",
              "currenciesAccepted": "PHP",
              "paymentAccepted": "Cash, GCash, Bank Transfer",
              "areaServed": {
                "@type": "City",
                "name": "Calapan City"
              },
              "sameAs": [
                "https://www.facebook.com/profile.php?id=100072122019511",
                "https://www.tiktok.com/@amigagracia",
                "https://www.amigagracia.com"
              ],
              "hasMap": "https://www.google.com/maps/search/Amiga+Gracia+Travel+Services+Calapan"
            },
            {
              "@type": "WebSite",
              "@id": "https://www.amigagracia.com/#website",
              "url": "https://www.amigagracia.com",
              "name": "Amiga Gracia Travel Services",
              "description": "Book ferry, airline tickets, tours and hotel reservations in Calapan, Oriental Mindoro.",
              "publisher": {
                "@id": "https://www.amigagracia.com/#organization"
              },
              "potentialAction": {
                "@type": "SearchAction",
                "target": {
                  "@type": "EntryPoint",
                  "urlTemplate": "https://www.amigagracia.com/schedules?q={search_term_string}"
                },
                "query-input": "required name=search_term_string"
              }
            }
          ]
        }
        @endverbatim
        </script>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        {{-- Flatpickr (global) --}}
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr" defer></script>
        <style>
            /* Prevent Alpine.js elements with x-cloak from flashing before init */
            [x-cloak] { display: none !important; }

            .flatpickr-calendar { font-family: inherit; border-radius: 1rem; border: 1px solid #e2e8f0; box-shadow: 0 10px 30px -5px rgba(0,0,0,.15); overflow: hidden; }
            .flatpickr-calendar.arrowTop:before,.flatpickr-calendar.arrowTop:after { display:none; }
            .flatpickr-months { background:#3b82f6; border-radius:1rem 1rem 0 0; padding:4px 0; }
            .flatpickr-month,.flatpickr-current-month .cur-month,.flatpickr-current-month .cur-year { color:#fff; fill:#fff; font-weight:600; }
            .flatpickr-prev-month,.flatpickr-next-month { color:#fff; fill:#fff; padding:8px 14px; }
            .flatpickr-prev-month:hover,.flatpickr-next-month:hover { background:rgba(255,255,255,.2); border-radius:8px; }
            .flatpickr-weekdays { background:#eff6ff; }
            span.flatpickr-weekday { color:#3b82f6; font-weight:700; font-size:.68rem; text-transform:uppercase; }
            .flatpickr-day { border-radius:.6rem; font-size:.85rem; color:#1e293b; height:34px; line-height:34px; }
            .flatpickr-day:hover { background:#dbeafe; border-color:transparent; }
            .flatpickr-day.today { border-color:#3b82f6; color:#3b82f6; font-weight:700; }
            .flatpickr-day.today:hover { background:#dbeafe; }
            .flatpickr-day.selected,.flatpickr-day.selected:hover { background:#3b82f6; border-color:#3b82f6; color:#fff; font-weight:700; }
            .flatpickr-day.prevMonthDay,.flatpickr-day.nextMonthDay { color:#cbd5e1; }
            .flatpickr-day.flatpickr-disabled { color:#e2e8f0; }
            .flatpickr-innerContainer { padding:6px 8px 8px; }
        </style>

        @php
            $bgMap = [
                '' => 'bg-1.jpg',
                '/' => 'bg-1.jpg',
                'home' => 'bg-1.jpg',
                'about' => 'bg-2.jpg',
                'schedules' => 'bg-3.jpg',
                'services' => 'bg-4.jpg',
                'tour-package' => 'bg-5.jpg',
                'contact-us' => 'bg-6.jpg',
                'faqs' => 'bg-7.jpg',
                'download' => 'bg-8.jpg',
            ];

            $currentPath = trim(request()->path(), '/');
            $bgImage = 'bg-1.jpg';

            if (isset($bgMap[$currentPath])) {
                $bgImage = $bgMap[$currentPath];
            } elseif (request()->is('about*')) {
                $bgImage = 'bg-2.jpg';
            } elseif (request()->is('book*')) {
                $bgImage = null;
            } elseif (request()->is('schedules*')) {
                $bgImage = 'bg-3.jpg';
            } elseif (request()->is('services*')) {
                $bgImage = 'bg-4.jpg';
            } elseif (request()->is('tour*') || request()->is('tours*')) {
                $bgImage = 'bg-5.jpg';
            } elseif (request()->is('contact*')) {
                $bgImage = 'bg-6.jpg';
            } elseif (request()->is('faq*')) {
                $bgImage = 'bg-7.jpg';
            } elseif (request()->is('download*')) {
                $bgImage = 'bg-8.jpg';
            } elseif (request()->is('login*') || request()->is('register*')) {
                $bgImage = 'bg-2.jpg';
            }
        @endphp
        @if($bgImage)
        <style>
            /* Dynamic page background image with opacity */
            body::before {
                content: "";
                position: fixed;
                inset: 0;
                background-image: url('{{ asset('images/amiga-backgrounds/' . $bgImage) }}');
                background-size: cover;
                background-position: center center;
                opacity: 0.35;
                z-index: 0;
                pointer-events: none;
                filter: saturate(0.95) brightness(0.95);
            }
        </style>
        @endif
        <style>
            /* Ensure content sits above the background */
            body > *:not(header) { position: relative; z-index: 10; }
            header { position: sticky; top: 0; z-index: 50; }

            /* Hide background for Filament admin pages */
            body.fi-layout::before,
            .fi-layout::before {
                display: none !important;
            }
        </style>
    </head>
    <body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col">
        @php
            $isAuthPage = request()->is('login*') || request()->is('register*') || request()->is('admin/login*') || request()->is('admin/register*');
        @endphp

        @if(! $isAuthPage)
        @if(request()->is('/'))
        <header x-data="{ scrolled: false }"
                @scroll.window="scrolled = (window.pageYOffset > 50)"
                :class="scrolled ? 'shadow-lg' : ''"
                class="bg-[#008000] text-white sticky top-0 z-50 relative transition-all duration-300 ease-in-out">
            <div class="relative z-10 max-w-full mx-auto px-3 sm:px-4 lg:px-5">
                <div :class="scrolled ? 'h-20' : 'h-28 sm:h-32'" 
                     class="h-28 sm:h-32 flex items-center justify-between transition-all duration-300 ease-in-out">
                    <div class="flex items-center gap-6 lg:gap-10">
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ url('/') }}" class="flex items-center gap-2">
                                  <img src="{{ asset('images/amiga_logo_white_outline.png') }}" 
                                      alt="{{ data_get($headerData, 'company_name', 'Amiga Gracia') }}" 
                                      :class="scrolled ? 'h-16' : 'h-24 sm:h-28'"
                                      class="h-24 sm:h-28 w-auto object-contain transition-all duration-300 ease-in-out">
                            </a>
                        </div>
                        <nav :class="scrolled ? 'translate-y-0' : '-translate-y-4 sm:-translate-y-5'" 
                             class="hidden md:flex items-center space-x-6 lg:space-x-7 font-medium transition-transform duration-300 ease-in-out">
        @else
        <header class="bg-[#008000] text-white sticky top-0 z-50 shadow-md relative">
            <div class="relative z-10 max-w-full mx-auto px-3 sm:px-4 lg:px-5">
                <div class="flex items-center justify-between h-20">
                    <div class="flex items-center gap-6 lg:gap-10">
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ url('/') }}" class="flex items-center gap-2">
                                <img src="{{ asset('images/amiga_logo_white_outline.png') }}" 
                                     alt="{{ data_get($headerData, 'company_name', 'Amiga Gracia') }}" 
                                     class="h-16 w-auto object-contain">
                            </a>
                        </div>
                        <nav class="hidden md:flex items-center space-x-6 lg:space-x-7 font-medium">
        @endif
                        <a href="{{ url('/') }}" class="py-1 text-white transition-all duration-200 {{ request()->is('/') ? 'border-b-2 border-white font-semibold' : 'border-b-2 border-transparent hover:border-white/70' }}">Home</a>
                        <a href="{{ url('/about') }}" class="py-1 text-white transition-all duration-200 {{ request()->is('about') ? 'border-b-2 border-white font-semibold' : 'border-b-2 border-transparent hover:border-white/70' }}">About</a>
                        <a href="{{ url('/schedules') }}" class="py-1 text-white transition-all duration-200 {{ request()->is('schedules') ? 'border-b-2 border-white font-semibold' : 'border-b-2 border-transparent hover:border-white/70' }}">Schedules</a>
                        <div x-data="{ open: false }" class="relative" @mouseenter="open = true" @mouseleave="open = false">
                            <button class="py-1 text-white transition-all duration-200 flex items-center gap-1 {{ request()->is('services') || request()->is('tour-package') ? 'border-b-2 border-white font-semibold' : 'border-b-2 border-transparent hover:border-white/70' }}">
                                Discover
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute left-1/2 -translate-x-1/2 mt-2 w-48 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden" style="display: none;">
                                <div class="py-1">
                                    <a href="{{ url('/services') }}" class="block px-4 py-2.5 text-sm font-medium {{ request()->is('services') ? 'bg-slate-100 text-slate-900' : 'text-slate-800 hover:bg-slate-50 hover:text-slate-900' }}">Services</a>
                                    <a href="{{ url('/tour-package') }}" class="block px-4 py-2.5 text-sm font-medium {{ request()->is('tour-package') ? 'bg-slate-100 text-slate-900' : 'text-slate-800 hover:bg-slate-50 hover:text-slate-900' }}">Tour Packages</a>
                                </div>
                            </div>
                        </div>
                        <div x-data="{ open: false }" class="relative" @mouseenter="open = true" @mouseleave="open = false">
                            <button class="py-1 text-white transition-all duration-200 flex items-center gap-1 {{ request()->is('contact-us') || request()->is('faqs') ? 'border-b-2 border-white font-semibold' : 'border-b-2 border-transparent hover:border-white/70' }}">
                                Get Help
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute left-1/2 -translate-x-1/2 mt-2 w-48 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none overflow-hidden" style="display: none;">
                                <div class="py-1">
                                    <a href="{{ url('/contact-us') }}" class="block px-4 py-2.5 text-sm font-medium {{ request()->is('contact-us') ? 'bg-slate-100 text-slate-900' : 'text-slate-800 hover:bg-slate-50 hover:text-slate-900' }}">Contact Us</a>
                                    <a href="{{ url('/faqs') }}" class="block px-4 py-2.5 text-sm font-medium {{ request()->is('faqs') ? 'bg-slate-100 text-slate-900' : 'text-slate-800 hover:bg-slate-50 hover:text-slate-900' }}">FAQs</a>
                                </div>
                            </div>
                        </div>
                    </nav>
                </div>

                    <div class="flex items-center gap-6 lg:gap-8 ml-auto">
                        <div :class="scrolled ? 'translate-y-0' : '{{ request()->is('/') ? '-translate-y-4 sm:-translate-y-5' : 'translate-y-0' }}'" 
                             class="hidden md:flex items-center gap-6 lg:gap-7 font-medium transition-transform duration-300 ease-in-out">
                            <div x-data="{ open: false }" @click.away="open = false" class="relative">
                                <button @click="open = !open" class="py-1 text-white transition-all duration-200 {{ request()->is('book/status') ? 'border-b-2 border-white font-semibold' : 'border-b-2 border-transparent hover:border-white/70' }}">
                                    My Booking
                                </button>
                                <div x-show="open" style="display: none;" class="absolute right-0 mt-2 w-[340px] rounded-xl bg-white p-6 shadow-2xl ring-1 ring-slate-200 z-50 text-left" x-transition>
                                    <h3 class="text-[15px] font-semibold text-slate-800 mb-4">Enter your Via Booking Reference number</h3>
                                    <form action="{{ url('/book/status') }}" method="GET" class="space-y-4">
                                        <div>
                                            <label class="sr-only">Transaction number</label>
                                            <input type="text" name="transaction_number" placeholder="e.g. AGT-20260805-1234" class="block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-[#ee018d] focus:outline-none focus:ring-1 focus:ring-[#ee018d] text-slate-900 placeholder-slate-400 shadow-sm">
                                        </div>
                                        <div>
                                            <label class="sr-only">Email</label>
                                            <input type="email" name="email" placeholder="Enter your email" class="block w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-[#ee018d] focus:outline-none focus:ring-1 focus:ring-[#ee018d] text-slate-900 placeholder-slate-400 shadow-sm">
                                        </div>
                                        <button type="submit" class="w-max ml-auto block rounded-lg bg-[#14b8a6] px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0f9686] shadow-sm" style="background-color: #20b28e;">
                                            Check Status
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <a href="{{ url('/download') }}" class="py-1 text-white transition-all duration-200 {{ request()->is('download') ? 'border-b-2 border-white font-semibold' : 'border-b-2 border-transparent hover:border-white/70' }}">Download App</a>
                        </div>
                        <div class="hidden xl:flex items-center gap-6 text-sm text-white/90">
                            @if(!empty($headerData['phone']))
                                <a href="tel:{{ $headerData['phone'] }}" class="hover:text-[#ee018d]">{{ $headerData['phone'] }}</a>
                            @endif
                            @if(!empty($headerData['email']))
                                <a href="mailto:{{ $headerData['email'] }}" class="hover:text-[#ee018d]">{{ $headerData['email'] }}</a>
                            @endif
                        </div>
                        <button id="mobile-menu-button" aria-expanded="false" aria-label="Toggle navigation" class="inline-flex items-center justify-center rounded-lg border border-white/20 p-2 text-white hover:bg-white/10 md:hidden focus:outline-none focus:ring-2 focus:ring-white/50">
                            <svg id="menu-open-icon" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg id="menu-close-icon" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <div id="mobile-menu" class="md:hidden hidden bg-[#006600] border-t border-white/10 relative z-10">
                <div class="max-w-full mx-auto px-4 py-4 space-y-3">
                    <a href="{{ url('/') }}" class="block rounded-xl px-4 py-3 {{ request()->is('/') ? 'bg-white/15 text-white' : 'text-white hover:bg-white/15 hover:text-white' }}">Home</a>
                    <a href="{{ url('/about') }}" class="block rounded-xl px-4 py-3 {{ request()->is('about') ? 'bg-white/15 text-white' : 'text-white hover:bg-white/15 hover:text-white' }}">About</a>
                    <a href="{{ url('/schedules') }}" class="block rounded-xl px-4 py-3 {{ request()->is('schedules') ? 'bg-white/15 text-white' : 'text-white hover:bg-white/15 hover:text-white' }}">Schedules</a>
                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="w-full flex justify-between items-center rounded-xl px-4 py-3 font-medium {{ request()->is('services') || request()->is('tour-package') ? 'bg-white/15 text-white' : 'text-white hover:bg-white/15 hover:text-white' }}">
                            Discover
                            <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" style="display: none;" class="pl-4 pr-2 py-2 space-y-2 border-l border-white/20 ml-2 mt-1 bg-white rounded-xl shadow-sm">
                            <a href="{{ url('/services') }}" class="block rounded-lg px-4 py-2 text-sm font-medium {{ request()->is('services') ? 'bg-slate-100 text-slate-900' : 'text-slate-800 hover:bg-slate-50 hover:text-slate-900' }}">Services</a>
                            <a href="{{ url('/tour-package') }}" class="block rounded-lg px-4 py-2 text-sm font-medium {{ request()->is('tour-package') ? 'bg-slate-100 text-slate-900' : 'text-slate-800 hover:bg-slate-50 hover:text-slate-900' }}">Tour Package</a>
                        </div>
                    </div>
                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="w-full flex justify-between items-center rounded-xl px-4 py-3 font-medium {{ request()->is('contact-us') || request()->is('faqs') ? 'bg-white/15 text-white' : 'text-white hover:bg-white/15 hover:text-white' }}">
                            Get Help
                            <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" style="display: none;" class="pl-4 pr-2 py-2 space-y-2 border-l border-white/20 ml-2 mt-1 bg-white rounded-xl shadow-sm">
                            <a href="{{ url('/contact-us') }}" class="block rounded-lg px-4 py-2 text-sm font-medium {{ request()->is('contact-us') ? 'bg-slate-100 text-slate-900' : 'text-slate-800 hover:bg-slate-50 hover:text-slate-900' }}">Contact Us</a>
                            <a href="{{ url('/faqs') }}" class="block rounded-lg px-4 py-2 text-sm font-medium {{ request()->is('faqs') ? 'bg-slate-100 text-slate-900' : 'text-slate-800 hover:bg-slate-50 hover:text-slate-900' }}">FAQs</a>
                        </div>
                    </div>
                    <div x-data="{ open: false }">
                        <button @click="open = !open" class="w-full flex justify-between items-center rounded-xl px-4 py-3 font-medium {{ request()->is('book/status') ? 'bg-white/15 text-white' : 'text-white hover:bg-white/15 hover:text-white' }}">
                            My Booking
                            <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" style="display: none;" class="pl-4 pr-2 py-3 space-y-3 border-l border-white/20 ml-2 mt-1 bg-white rounded-xl shadow-sm text-left">
                            <h3 class="text-sm font-semibold text-slate-800 px-2">Enter your Via Booking Reference number</h3>
                            <form action="{{ url('/book/status') }}" method="GET" class="space-y-3 px-2">
                                <div>
                                    <input type="text" name="transaction_number" placeholder="e.g. AGT-20260805-1234" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#ee018d] focus:outline-none focus:ring-1 focus:ring-[#ee018d] text-slate-900 placeholder-slate-400">
                                </div>
                                <div>
                                    <input type="email" name="email" placeholder="Enter your email" class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#ee018d] focus:outline-none focus:ring-1 focus:ring-[#ee018d] text-slate-900 placeholder-slate-400">
                                </div>
                                <button type="submit" class="w-max ml-auto block rounded-lg text-white px-5 py-2 text-sm font-semibold transition shadow-sm" style="background-color: #20b28e;">
                                    Check Status
                                </button>
                            </form>
                        </div>
                    </div>
                    <a href="{{ url('/download') }}" class="block rounded-xl px-4 py-3 {{ request()->is('download') ? 'bg-white/15 text-white' : 'text-white hover:bg-white/15 hover:text-white' }}">Download App</a>
                    <div class="border-t border-white/10 pt-3">
                        @if(!empty($headerData['phone']))
                            <a href="tel:{{ $headerData['phone'] }}" class="block text-sm text-white/90 hover:text-white">Call us: {{ $headerData['phone'] }}</a>
                        @endif
                        @if(!empty($headerData['email']))
                            <a href="mailto:{{ $headerData['email'] }}" class="block text-sm text-white/90 hover:text-white">Email: {{ $headerData['email'] }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </header>
        @endif

        <main class="flex-grow">
            @unless($isAuthPage || request()->is('/') || request()->routeIs('book.new') || request()->is('book/new') || request()->is('schedules') || request()->is('schedules*') || request()->is('download') || request()->is('download*'))
                @if(session()->has('booking_draft'))
                    <div class="w-full bg-pink-50/95 border-b border-pink-200 px-4 sm:px-6 lg:px-8 py-3.5 text-slate-900 shadow-sm">
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
                @include('partials.global-skeleton')
            @endunless
            @yield('content')
        </main>

        @if(! $isAuthPage)
        @unless(request()->is('book*') || request()->is('booking*') || request()->is('payment*'))
            @include('partials.why-travel-section')
        @endunless
        <footer class="relative overflow-hidden bg-gradient-to-b from-[#008000] via-[#004a00] to-[#042402] text-white pt-16 pb-8">
            <div class="w-full px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-8 pb-8 border-b border-white/10">
                    <!-- Column 1: Logo & Tagline -->
            <div class="space-y-4">
                <div class="flex flex-col items-start gap-3">
                    <img src="{{ storage_asset_path(data_get($headerData, 'logo')) ?: asset('images/amiga_logo_white_outline.png') }}" alt="{{ data_get($headerData, 'company_name', 'Amiga Gracia') }}" class="h-20 sm:h-24 lg:h-28 w-auto">
                    <p class="text-white/90 font-semibold text-sm sm:text-base">{{ $footerData['tagline'] ?? 'Kay Amiga Hassle Free Ka!' }}</p>
                </div>

                <!-- Social Icons -->
                <div class="flex gap-4 pt-2">
                    @forelse($footerData['social_links'] ?? [] as $social)
                        <a href="{{ $social['url'] ?? '#' }}" target="_blank" class="h-10 w-10 rounded-full bg-white/10 hover:bg-emerald-500 flex items-center justify-center transition text-white" aria-label="{{ $social['platform'] ?? 'Social' }}">
                            <span class="text-sm font-bold">{{ strtoupper(substr($social['platform'] ?? 'SM', 0, 2)) }}</span>
                        </a>
                    @empty
                        <a href="https://www.facebook.com/profile.php?id=100072122019511" target="_blank" class="h-10 w-10 rounded-full bg-white/10 hover:bg-emerald-500 flex items-center justify-center transition text-white">
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                                <path d="M22 12c0-5.52-4.48-10-10-10S2 6.48 2 12c0 4.84 3.44 8.87 8 9.8V15H8v-3h2V9.5C10 7.57 11.57 6 13.5 6H16v3h-2c-.55 0-1 .45-1 1V12h3v3h-3v6.8c4.56-.93 8-4.96 8-9.8z"/>
                            </svg>
                        </a>
                        <a href="https://www.tiktok.com/@amigagracia?_r=1" target="_blank" class="h-10 w-10 rounded-full bg-white/10 hover:bg-emerald-500 flex items-center justify-center transition text-white">
                            <svg class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                                <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.17-2.86-.74-3.94-1.74-.22-.2-.43-.42-.62-.65v7.17c.02 1.36-.26 2.74-.91 3.97-.8 1.48-2.2 2.63-3.82 3.1-1.61.47-3.36.33-4.9-.38-1.54-.7-2.79-2.02-3.38-3.63-.59-1.61-.53-3.44.18-5 1-2.2 3.32-3.75 5.75-3.64.09 0 .17.02.26.03V10.7c-1.43-.07-2.91.43-3.9 1.48-.99 1.05-1.41 2.58-1.15 4.02.26 1.44 1.22 2.68 2.53 3.3 1.31.62 2.87.58 4.14-.14 1.27-.72 2.05-2.09 2.08-3.56v-15.8z"/>
                            </svg>
                        </a>
                    @endforelse
                </div>
            </div>

                    <!-- Column 2: Sitemap -->
                    <div class="space-y-4">
                        <h5 class="text-sm font-bold uppercase tracking-wider text-emerald-400">Sitemap</h5>
                        <ul class="space-y-2 text-sm text-slate-300 font-medium">
                            <li><a href="{{ url('/') }}" class="hover:text-emerald-300 transition">Home</a></li>
                            <li><a href="{{ url('/about') }}" class="hover:text-emerald-300 transition">About</a></li>
                            <li><a href="{{ url('/schedules') }}" class="hover:text-emerald-300 transition">Schedules</a></li>
                            <li><a href="{{ url('/services') }}" class="hover:text-emerald-300 transition">Services</a></li>
                            <li><a href="{{ url('/tour-package') }}" class="hover:text-emerald-300 transition">Tour Packages</a></li>
                            <li><a href="{{ url('/faqs') }}" class="hover:text-emerald-300 transition">Frequently Asked Questions</a></li>
                            <li><a href="{{ url('/contact-us') }}" class="hover:text-emerald-300 transition">Contact Us</a></li>
                            <li><a href="{{ url('/download') }}" class="hover:text-emerald-300 transition">Download App</a></li>
                        </ul>
                    </div>

                    <!-- Column 3: Transit Services -->
                    <div class="space-y-4">
                        <h5 class="text-sm font-bold uppercase tracking-wider text-emerald-400">Transit</h5>
                        <ul class="space-y-2 text-sm text-slate-300 font-medium">
                            @forelse($footerData['transit_links'] ?? [] as $transit)
                                <li><a href="{{ $transit['url'] }}" class="hover:text-emerald-300 transition">{{ $transit['label'] }}</a></li>
                            @empty
                                <li><a href="{{ url('/book/new') }}" class="hover:text-emerald-300 transition">2GO</a></li>
                                <li><a href="{{ url('/book/new') }}" class="hover:text-emerald-300 transition">Starlite</a></li>
                                <li><a href="{{ url('/book/new') }}" class="hover:text-emerald-300 transition">Airline Ticketing</a></li>
                            @endforelse
                        </ul>
                    </div>

                    <!-- Column 4: Support -->
                    <div class="space-y-4">
                        <h5 class="text-sm font-bold uppercase tracking-wider text-emerald-400">Support</h5>
                        <ul class="space-y-2 text-sm text-slate-300 font-medium">
                            <li><a href="{{ url('/contact-us') }}" class="hover:text-emerald-300 transition">Contact Us</a></li>
                            <li><a href="{{ url('/faqs') }}" class="hover:text-emerald-300 transition">Frequently Asked Questions</a></li>
                            <li><a href="{{ url('/privacy-policy') }}" class="hover:text-emerald-300 transition">Privacy Policy</a></li>
                        </ul>
                    </div>

                    <!-- Column 5: Contact details -->
                    <div class="space-y-4">
                        <h5 class="text-sm font-bold uppercase tracking-wider text-emerald-400">Contact Info</h5>
                        <ul class="space-y-3 text-sm text-slate-300 font-medium">
                            <li class="flex gap-2 items-center">
                                <span class="font-semibold text-emerald-400">Mobile:</span>
                                <span>{{ data_get($footerData, 'phone', '0930-928-4278') }}</span>
                            </li>
                            <li class="flex gap-2 items-center">
                                <span class="font-semibold text-emerald-400">Landline:</span>
                                <span>{{ data_get($footerData, 'landline', '(043) 738-2989') }}</span>
                            </li>
                            <li class="flex flex-wrap gap-1 items-center">
                                <span class="font-semibold text-emerald-400">Email:</span>
                                <span class="hover:text-emerald-300 break-all"><a href="mailto:{{ data_get($footerData, 'email', 'agtsreservation@amigagracia.com') }}">{{ data_get($footerData, 'email', 'agtsreservation@amigagracia.com') }}</a></span>
                            </li>
                            <li class="text-sm leading-relaxed pt-2 text-slate-400 font-medium">
                                {{ data_get($footerData, 'address', 'Roxas Drive, Libis, Calapan City, Oriental Mindoro, 5200') }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom bar -->
                <div class="pt-12 pb-10 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-slate-400 relative z-10">
                    <div class="space-y-1 text-center md:text-left">
                        <p>&copy; 2017 – {{ date('Y') }} {{ $headerData['company_name'] ?? 'Amiga Gracia Travel Services' }}. All rights reserved.</p>
                        <p class="text-slate-500">Developed by Aries King N. Nieto and Drew M. Macaraig</p>
                    </div>
                    <div class="flex flex-wrap gap-6 items-center justify-center md:justify-end">
                        <a href="{{ url('/download') }}" class="hover:text-emerald-300 transition">Download App</a>
                        <a href="{{ url('/privacy-policy') }}" class="hover:text-emerald-300 transition">Privacy Policy</a>
                        <a href="{{ url('/contact-us') }}" class="hover:text-emerald-300 transition">Support</a>
                        @if(!empty($footerData['app_version']))
                            <span class="text-slate-500">App version {{ $footerData['app_version'] }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Watermark Background Text: AMIGA GRACIA -->
            <div class="absolute bottom-[-2.5rem] left-1/2 -translate-x-1/2 w-full text-center select-none pointer-events-none opacity-[0.03] z-0">
                <span class="text-[8vw] lg:text-[9vw] font-black uppercase tracking-widest whitespace-nowrap text-white">AMIGA GRACIA</span>
            </div>
        </footer>
        @endif
        @livewireScripts
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                let shouldScrollOnNextUpdate = false;
                let scrollTimeout = null;

                function setScrollTrigger() {
                    shouldScrollOnNextUpdate = true;
                    if (scrollTimeout) clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(() => {
                        shouldScrollOnNextUpdate = false;
                    }, 5000);
                }

                document.addEventListener('submit', () => {
                    setScrollTrigger();
                });

                document.addEventListener('click', (e) => {
                    const button = e.target.closest('button, input[type="submit"], input[type="button"]');
                    if (button) {
                        const isSubmitType = button.getAttribute('type') === 'submit';
                        const hasWireClick = button.hasAttribute('wire:click');
                        const hasWireSubmit = button.closest('form[wire\\:submit], form[wire\\:submit\\.prevent]') !== null;
                        const isPrimaryAction =
                            button.classList.contains('bg-blue-600') ||
                            button.classList.contains('bg-[#ee018d]') ||
                            button.classList.contains('bg-emerald-600') ||
                            button.classList.contains('bg-[#db2777]') ||
                            button.classList.contains('bg-[#008000]');

                        if (isSubmitType || hasWireClick || hasWireSubmit || isPrimaryAction) {
                            setScrollTrigger();
                        }
                    }
                });

                document.addEventListener('livewire:init', () => {
                    Livewire.hook('commit', ({ succeed }) => {
                        succeed(() => {
                            // Small delay to let Livewire update the DOM with error messages
                            setTimeout(() => {
                                if (shouldScrollOnNextUpdate) {
                                    const selectors = [
                                        '[aria-invalid="true"]',
                                        'p.text-rose-600',
                                        'p.text-red-600',
                                        'p.text-red-500',
                                        '.invalid-feedback',
                                        '.error-message',
                                        '.text-rose-600',
                                        '.text-red-600',
                                    ];

                                    let firstErrorElement = null;
                                    for (const selector of selectors) {
                                        const el = document.querySelector(selector);
                                        if (el && el.offsetParent !== null) { // make sure it's visible
                                            firstErrorElement = el;
                                            break;
                                        }
                                    }

                                    if (firstErrorElement) {
                                        // Scroll to slightly above the error so users see context
                                        const yOffset = -120;
                                        const y = firstErrorElement.getBoundingClientRect().top + window.pageYOffset + yOffset;
                                        window.scrollTo({ top: y, behavior: 'smooth' });

                                        shouldScrollOnNextUpdate = false;

                                        // Try to focus the associated input
                                        let input = null;
                                        if (['INPUT', 'SELECT', 'TEXTAREA'].includes(firstErrorElement.tagName)) {
                                            input = firstErrorElement;
                                        } else {
                                            const container = firstErrorElement.closest('label, .space-y-2, .grid, div');
                                            if (container) {
                                                input = container.querySelector('input:not([type="hidden"]), select, textarea');
                                            }
                                        }
                                        if (input && input.offsetParent !== null) {
                                            input.focus({ preventScroll: true });
                                        }
                                    }
                                }
                            }, 150);
                        });
                    });
                });
            });
        </script>
        @stack('scripts')
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function () {
                    navigator.serviceWorker.register('/sw.js').catch(function (error) {
                        console.warn('Service worker registration failed:', error);
                    });
                });
            }
        </script>
        {{-- Global Animate-on-Scroll CSS & JS for all pages --}}
        <style>
            .amiga-transition {
                opacity: 0;
                transform: translateY(24px);
                transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1), transform 0.7s cubic-bezier(0.16, 1, 0.3, 1);
                will-change: opacity, transform;
            }
            .amiga-visible {
                opacity: 1 !important;
                transform: none !important;
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var animatedSections = document.querySelectorAll('.amiga-animate-on-scroll');
                if (!('IntersectionObserver' in window) || animatedSections.length === 0) {
                    animatedSections.forEach(function (el) {
                        el.classList.add('amiga-visible');
                    });
                    return;
                }
                var observer = new IntersectionObserver(function (entries, observer) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('amiga-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.12,
                });
                animatedSections.forEach(function (el) {
                    observer.observe(el);
                });
            });
        </script>
    </body>
</html>
