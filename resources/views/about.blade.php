@extends('layouts.app')

@section('content')
<div class="bg-transparent min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
        <style>
            @keyframes infinite-scroll {
                from { transform: translateX(0); }
                to { transform: translateX(calc(-100% - 1.5rem)); }
            }
            .animate-infinite-scroll {
                animation: infinite-scroll 25s linear infinite;
            }
            .pause-on-hover:hover .animate-infinite-scroll {
                animation-play-state: paused;
            }
        </style>
        <!-- Hero Section -->
        <div class="text-center mb-16 relative ws-sbtn-container amiga-animate-on-scroll amiga-transition">
            @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'hero' })" class="ws-sbtn absolute top-0 right-0"></button> @endif
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700 bg-emerald-100 px-3 py-1 rounded-full">{{ data_get($pageContent, 'badge', 'About Us') }}</span>
            <h1 class="mt-4 text-4xl sm:text-5xl font-black text-slate-900 tracking-tight">
                {{ $pageContent['title'] ?? 'Our Journey' }}
            </h1>
            <p class="mt-4 text-lg text-black font-semibold max-w-2xl mx-auto">
                {{ $pageContent['description'] ?? 'Discover the story behind Amiga Gracia Travel Services and our dedication to making every journey hassle-free and memorable.' }}
            </p>
        </div>

        <!-- History & Info Section -->
        <div class="grid md:grid-cols-2 gap-12 items-center mb-16 relative ws-sbtn-container amiga-animate-on-scroll amiga-transition">
            @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'history' })" class="ws-sbtn absolute -top-4 -right-4"></button> @endif
            <div class="space-y-6">
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 leading-tight">
                    {{ data_get($pageContent, 'history_title', 'Backed by Experience, Driven by Excellence') }}
                </h2>
                <div class="text-black font-semibold leading-relaxed space-y-4">
                    {!! data_get($pageContent, 'history_content', "
                        <p>Amiga Gracia was established on <strong>July 2017</strong>. It's humble beginning was born from the dedication of its owner <strong>Ms. Mary Grace Antaran - Ting</strong>. The experience she gained from 2GO company laid the foundation of the company's first class service, the Travel Agency was then given a partnership with 2GO and later with Starlite Ferries and Supercat, providing apprenticeship trainings, educational tours and and travel services.</p>
                        <p>The first location of its founding office operated within the municipality of Roxas, Oriental Mindoro and was later relocated within the City of Calapan after the pandemic. The company's main goal is to be named as one of the top Agency providing Travel Services not just in Oriental Mindoro but outside the province as well.</p>
                        <p>Backed by top companies, Amiga Gracia defines travel experience with first class service.</p>
                    ") !!}
                </div>
            </div>
            
            <div class="bg-white/85 backdrop-blur-md rounded-[2rem] p-8 shadow-xl ring-1 ring-slate-100 relative overflow-hidden ws-sbtn-container">
                @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'quick_facts' })" class="ws-sbtn absolute top-2 right-2 z-20"></button> @endif
                <div class="absolute top-0 right-0 h-40 w-40 bg-emerald-50 rounded-full -mr-16 -mt-16 z-0"></div>
                <div class="relative z-10 space-y-6">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-600"></span> {{ data_get($pageContent, 'quick_facts_title', 'Core Values') }}
                    </h3>
                    
                    <div class="space-y-4">
                        @php
                            $quickFacts = data_get($pageContent, 'quick_facts', [
                                ['title' => 'Growth & Innovation', 'desc' => '', 'letter' => 'G', 'color' => 'emerald'],
                                ['title' => 'Responsibility & Integrity', 'desc' => '', 'letter' => 'R', 'color' => 'emerald'],
                                ['title' => 'Accountability', 'desc' => '', 'letter' => 'A', 'color' => 'emerald'],
                                ['title' => 'Customer Excellence', 'desc' => '', 'letter' => 'C', 'color' => 'emerald'],
                                ['title' => 'Inclusivity & Collaboration', 'desc' => '', 'letter' => 'I', 'color' => 'emerald'],
                                ['title' => 'Assurance of quality & Safety', 'desc' => '', 'letter' => 'A', 'color' => 'emerald'],
                            ]);
                        @endphp
                        @foreach($quickFacts as $index => $fact)
                            @php
                                $color = data_get($fact, 'color', 'emerald');
                                $colors = [
                                    'emerald' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700'],
                                    'green' => ['bg' => 'bg-green-100', 'text' => 'text-green-700'],
                                    'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
                                    'pink' => ['bg' => 'bg-pink-100', 'text' => 'text-pink-600'],
                                    'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
                                ];
                                $colorClasses = $colors[$color] ?? $colors['emerald'];
                            @endphp
                            <div class="flex gap-4 items-center">
                                <div class="flex-shrink-0 h-10 w-10 {{ $colorClasses['bg'] }} rounded-xl flex items-center justify-center {{ $colorClasses['text'] }} font-semibold text-lg">
                                    {{ data_get($fact, 'letter', str_pad($index + 1, 2, '0', STR_PAD_LEFT)) }}
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900">{{ data_get($fact, 'title') }}</h4>
                                    @if(data_get($fact, 'desc'))
                                        <p class="text-sm text-slate-500">{{ data_get($fact, 'desc') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @php
            $aboutBookingCards = $bookingCards;
            if (empty($aboutBookingCards) && !empty($pageContent['booking_cards'])) {
                $aboutBookingCards = $pageContent['booking_cards'];
            }
        @endphp

        @if(!empty($aboutBookingCards))
            <div class="bg-white/85 backdrop-blur-md rounded-[2rem] p-8 shadow-xl mb-16 amiga-animate-on-scroll amiga-transition">
                <div class="max-w-3xl mx-auto text-center mb-8">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">{{ data_get($pageContent, 'booking_section_title') ? ucfirst(data_get($pageContent, 'booking_section_title')) : 'Request Travel Bookings' }}</p>
                    <h2 class="mt-4 text-3xl sm:text-4xl font-bold text-slate-900">{{ data_get($pageContent, 'booking_section_title') ?? 'Request Travel Bookings' }}</h2>
                    <p class="mt-4 text-base sm:text-lg text-black font-semibold max-w-2xl mx-auto">
                        {{ data_get($pageContent, 'booking_section_description') ?? 'Kay Amiga, Hassle Free Ka! Select a booking category to start your travel request.' }}
                    </p>
                </div>
                <div x-data="{
                        selectedCard: null,
                        openModal(card) { this.selectedCard = card; },
                        closeModal() { this.selectedCard = null; }
                    }"
                    class="w-full"
                >
                    @if(count($aboutBookingCards) > 3)
                        <div class="pause-on-hover flex overflow-hidden gap-6 w-full py-4 -my-4 px-4 -mx-4">
                            <div class="flex flex-nowrap gap-6 animate-infinite-scroll min-w-max">
                                @foreach($aboutBookingCards as $card)
                                    @php
                                        $rawCardImage = data_get($card, 'image');
                                        $cardImage = $rawCardImage ? storage_asset_path($rawCardImage) : 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80';
                                        $cardTitle = data_get($card, 'title', 'Travel Booking');
                                        $cardDescription = data_get($card, 'description', 'Kasiyahan po namin ang paglingkuran kayo.');
                                        $cardDetail = data_get($card, 'detail', 'Learn more about this travel booking option in detail.');
                                        $cardNote = data_get($card, 'note', 'Booking Details');
                                    @endphp
                                    <button type="button" @click='openModal({ title: @json($cardTitle), description: @json($cardDescription), detail: @json($cardDetail), image: @json($cardImage), note: @json($cardNote) })' class="w-[320px] shrink-0 group flex flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg text-left">
                                        <div class="aspect-[4/3] overflow-hidden">
                                            <img src="{{ $cardImage }}" alt="{{ $cardTitle }}" class="h-full w-full object-cover transition duration-200 group-hover:scale-105" />
                                        </div>
                                        <div class="p-6 flex flex-col gap-4 flex-grow">
                                            <div>
                                                <span class="inline-flex items-center gap-2 text-[10px] font-semibold uppercase tracking-[0.3em] text-[#ee018d]">
                                                    <span class="h-2.5 w-2.5 rounded-full bg-[#ee018d]"></span>
                                                    About this booking
                                                </span>
                                                <h3 class="mt-4 text-xl font-bold text-slate-900">{{ $cardTitle }}</h3>
                                            </div>
                                            <p class="text-sm text-slate-600 leading-relaxed">{{ $cardDescription }}</p>
                                            <p class="text-sm text-slate-500 leading-relaxed">{{ $cardDetail }}</p>
                                            <div class="mt-auto">
                                                <span class="inline-flex items-center justify-center rounded-full bg-[#216417] px-4 py-2 text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-green-800">
                                                    {{ data_get($card, 'button_text', 'View Details') }}
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                            <div class="flex flex-nowrap gap-6 animate-infinite-scroll min-w-max" aria-hidden="true">
                                @foreach($aboutBookingCards as $card)
                                    @php
                                        $rawCardImage = data_get($card, 'image');
                                        $cardImage = $rawCardImage ? storage_asset_path($rawCardImage) : 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80';
                                        $cardTitle = data_get($card, 'title', 'Travel Booking');
                                        $cardDescription = data_get($card, 'description', 'Kasiyahan po namin ang paglingkuran kayo.');
                                        $cardDetail = data_get($card, 'detail', 'Learn more about this travel booking option in detail.');
                                        $cardNote = data_get($card, 'note', 'Booking Details');
                                    @endphp
                                    <button type="button" @click='openModal({ title: @json($cardTitle), description: @json($cardDescription), detail: @json($cardDetail), image: @json($cardImage), note: @json($cardNote) })' class="w-[320px] shrink-0 group flex flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg text-left">
                                        <div class="aspect-[4/3] overflow-hidden">
                                            <img src="{{ $cardImage }}" alt="{{ $cardTitle }}" class="h-full w-full object-cover transition duration-200 group-hover:scale-105" />
                                        </div>
                                        <div class="p-6 flex flex-col gap-4 flex-grow">
                                            <div>
                                                <span class="inline-flex items-center gap-2 text-[10px] font-semibold uppercase tracking-[0.3em] text-[#ee018d]">
                                                    <span class="h-2.5 w-2.5 rounded-full bg-[#ee018d]"></span>
                                                    About this booking
                                                </span>
                                                <h3 class="mt-4 text-xl font-bold text-slate-900">{{ $cardTitle }}</h3>
                                            </div>
                                            <p class="text-sm text-slate-600 leading-relaxed">{{ $cardDescription }}</p>
                                            <p class="text-sm text-slate-500 leading-relaxed">{{ $cardDetail }}</p>
                                            <div class="mt-auto">
                                                <span class="inline-flex items-center justify-center rounded-full bg-[#216417] px-4 py-2 text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-green-800">
                                                    {{ data_get($card, 'button_text', 'View Details') }}
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($aboutBookingCards as $card)
                                @php
                                    $rawCardImage = data_get($card, 'image');
                                    $cardImage = $rawCardImage ? storage_asset_path($rawCardImage) : 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80';
                                    $cardTitle = data_get($card, 'title', 'Travel Booking');
                                    $cardDescription = data_get($card, 'description', 'Kasiyahan po namin ang paglingkuran kayo.');
                                    $cardDetail = data_get($card, 'detail', 'Learn more about this travel booking option in detail.');
                                    $cardNote = data_get($card, 'note', 'Booking Details');
                                @endphp
                                <button type="button" @click='openModal({ title: @json($cardTitle), description: @json($cardDescription), detail: @json($cardDetail), image: @json($cardImage), note: @json($cardNote) })' class="group flex flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg text-left">
                                    <div class="aspect-[4/3] overflow-hidden">
                                        <img src="{{ $cardImage }}" alt="{{ $cardTitle }}" class="h-full w-full object-cover transition duration-200 group-hover:scale-105" />
                                    </div>
                                    <div class="p-6 flex flex-col gap-4 flex-grow">
                                        <div>
                                            <span class="inline-flex items-center gap-2 text-[10px] font-semibold uppercase tracking-[0.3em] text-[#ee018d]">
                                                <span class="h-2.5 w-2.5 rounded-full bg-[#ee018d]"></span>
                                                About this booking
                                            </span>
                                            <h3 class="mt-4 text-xl font-bold text-slate-900">{{ $cardTitle }}</h3>
                                        </div>
                                        <p class="text-sm text-slate-600 leading-relaxed">{{ $cardDescription }}</p>
                                        <p class="text-sm text-slate-500 leading-relaxed">{{ $cardDetail }}</p>
                                        <div class="mt-auto">
                                            <span class="inline-flex items-center justify-center rounded-full bg-[#216417] px-4 py-2 text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-green-800">
                                                {{ data_get($card, 'button_text', 'View Details') }}
                                            </span>
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div x-show="selectedCard" x-cloak x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/70 flex items-center justify-center p-4">
                        <div class="relative w-full max-w-3xl overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                            <button type="button" @click="closeModal()" class="absolute right-4 top-4 rounded-full bg-white/90 p-2 text-slate-700 hover:bg-white">
                                <span class="sr-only">Close</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.414L11.414 10l4.95 4.95a1 1 0 01-1.414 1.414L10 11.414l-4.95 4.95a1 1 0 01-1.414-1.414L8.586 10 3.636 5.05a1 1 0 011.414-1.414L10 8.586z" clip-rule="evenodd"/></svg>
                            </button>
                            <img x-bind:src="selectedCard.image" x-bind:alt="selectedCard.title" class="w-full max-h-80 object-cover" />
                            <div class="p-8">
                                <span class="inline-flex items-center gap-2 text-[10px] font-semibold uppercase tracking-[0.3em] text-[#ee018d] mb-4" x-text="selectedCard.note"></span>
                                <h2 class="text-2xl font-bold text-slate-900 mb-3" x-text="selectedCard.title"></h2>
                                <p class="text-sm text-slate-500 mb-4" x-text="selectedCard.description"></p>
                                <p class="text-sm text-slate-600 leading-relaxed" x-text="selectedCard.detail"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @php
            $awards = data_get($pageContent, 'awards', []);
        @endphp

        @if(!empty($awards))
            <div class="bg-white/85 backdrop-blur-md rounded-[2rem] p-8 shadow-xl mb-16">
                <div class="max-w-3xl mx-auto text-center mb-8">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">{{ data_get($pageContent, 'awards_title') ? ucfirst(data_get($pageContent, 'awards_title')) : 'Awards & Recognitions' }}</p>
                    <h2 class="mt-4 text-3xl sm:text-4xl font-bold text-slate-900">{{ data_get($pageContent, 'awards_title') ?? 'Awards & Recognitions' }}</h2>
                    <p class="mt-4 text-base sm:text-lg text-slate-600 max-w-2xl mx-auto">
                        {{ data_get($pageContent, 'awards_description') ?? 'We take pride in our service excellence and recognitions.' }}
                    </p>
                </div>
                <div x-data="{
                        selectedCard: null,
                        openModal(card) { this.selectedCard = card; },
                        closeModal() { this.selectedCard = null; }
                    }"
                    class="w-full"
                >
                    @if(count($awards) > 3)
                        <div class="pause-on-hover flex overflow-hidden gap-6 w-full py-4 -my-4 px-4 -mx-4">
                            <div class="flex flex-nowrap gap-6 animate-infinite-scroll min-w-max">
                                @foreach($awards as $card)
                                    @php
                                        $rawCardImage = data_get($card, 'image');
                                        $cardImage = $rawCardImage ? storage_asset_path($rawCardImage) : 'https://images.unsplash.com/photo-1506929562872-bb421503ef21?auto=format&fit=crop&w=600&q=80';
                                        $cardTitle = data_get($card, 'title', 'Award');
                                        $cardDescription = data_get($card, 'description', '');
                                        $cardDetail = data_get($card, 'detail', '');
                                        $cardButtonText = data_get($card, 'button_text', 'View Award');
                                    @endphp
                                    <button type="button" @click='openModal({ title: @json($cardTitle), description: @json($cardDescription), detail: @json($cardDetail), image: @json($cardImage), note: "Award" })' class="w-[320px] shrink-0 group flex flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg text-left">
                                        <div class="aspect-[4/3] overflow-hidden">
                                            <img src="{{ $cardImage }}" alt="{{ $cardTitle }}" class="h-full w-full object-cover transition duration-200 group-hover:scale-105" />
                                        </div>
                                        <div class="p-6 flex flex-col gap-4 flex-grow">
                                            <div>
                                                <h3 class="text-xl font-bold text-slate-900">{{ $cardTitle }}</h3>
                                            </div>
                                            <p class="text-sm text-slate-600 leading-relaxed">{{ $cardDescription }}</p>
                                            <div class="mt-auto pt-4">
                                                <span class="inline-flex items-center justify-center rounded-full bg-[#216417] px-4 py-2 text-sm font-semibold text-white shadow-sm transition duration-200 group-hover:bg-green-800">
                                                    {{ $cardButtonText }}
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                            <div class="flex flex-nowrap gap-6 animate-infinite-scroll min-w-max" aria-hidden="true">
                                @foreach($awards as $card)
                                    @php
                                        $rawCardImage = data_get($card, 'image');
                                        $cardImage = $rawCardImage ? storage_asset_path($rawCardImage) : 'https://images.unsplash.com/photo-1506929562872-bb421503ef21?auto=format&fit=crop&w=600&q=80';
                                        $cardTitle = data_get($card, 'title', 'Award');
                                        $cardDescription = data_get($card, 'description', '');
                                        $cardDetail = data_get($card, 'detail', '');
                                        $cardButtonText = data_get($card, 'button_text', 'View Award');
                                    @endphp
                                    <button type="button" @click='openModal({ title: @json($cardTitle), description: @json($cardDescription), detail: @json($cardDetail), image: @json($cardImage), note: "Award" })' class="w-[320px] shrink-0 group flex flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg text-left">
                                        <div class="aspect-[4/3] overflow-hidden">
                                            <img src="{{ $cardImage }}" alt="{{ $cardTitle }}" class="h-full w-full object-cover transition duration-200 group-hover:scale-105" />
                                        </div>
                                        <div class="p-6 flex flex-col gap-4 flex-grow">
                                            <div>
                                                <h3 class="text-xl font-bold text-slate-900">{{ $cardTitle }}</h3>
                                            </div>
                                            <p class="text-sm text-slate-600 leading-relaxed">{{ $cardDescription }}</p>
                                            <div class="mt-auto pt-4">
                                                <span class="inline-flex items-center justify-center rounded-full bg-[#216417] px-4 py-2 text-sm font-semibold text-white shadow-sm transition duration-200 group-hover:bg-green-800">
                                                    {{ $cardButtonText }}
                                                </span>
                                            </div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach($awards as $card)
                                @php
                                    $rawCardImage = data_get($card, 'image');
                                    $cardImage = $rawCardImage ? storage_asset_path($rawCardImage) : 'https://images.unsplash.com/photo-1506929562872-bb421503ef21?auto=format&fit=crop&w=600&q=80';
                                    $cardTitle = data_get($card, 'title', 'Award');
                                    $cardDescription = data_get($card, 'description', '');
                                    $cardDetail = data_get($card, 'detail', '');
                                    $cardButtonText = data_get($card, 'button_text', 'View Award');
                                @endphp
                                <button type="button" @click='openModal({ title: @json($cardTitle), description: @json($cardDescription), detail: @json($cardDetail), image: @json($cardImage), note: "Award" })' class="group flex flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg text-left">
                                    <div class="aspect-[4/3] overflow-hidden">
                                        <img src="{{ $cardImage }}" alt="{{ $cardTitle }}" class="h-full w-full object-cover transition duration-200 group-hover:scale-105" />
                                    </div>
                                    <div class="p-6 flex flex-col gap-4 flex-grow">
                                        <div>
                                            <h3 class="text-xl font-bold text-slate-900">{{ $cardTitle }}</h3>
                                        </div>
                                        <p class="text-sm text-slate-600 leading-relaxed">{{ $cardDescription }}</p>
                                        <div class="mt-auto pt-4">
                                            <span class="inline-flex items-center justify-center rounded-full bg-[#216417] px-4 py-2 text-sm font-semibold text-white shadow-sm transition duration-200 group-hover:bg-green-800">
                                                {{ $cardButtonText }}
                                            </span>
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div x-show="selectedCard" x-cloak x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/70 flex items-center justify-center p-4">
                        <div class="relative w-full max-w-3xl overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                            <button type="button" @click="closeModal()" class="absolute right-4 top-4 rounded-full bg-white/90 p-2 text-slate-700 hover:bg-white">
                                <span class="sr-only">Close</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.414L11.414 10l4.95 4.95a1 1 0 01-1.414 1.414L10 11.414l-4.95 4.95a1 1 0 01-1.414-1.414L8.586 10 3.636 5.05a1 1 0 011.414-1.414L10 8.586z" clip-rule="evenodd"/></svg>
                            </button>
                            <img x-bind:src="selectedCard?.image" x-bind:alt="selectedCard?.title" class="w-full max-h-80 object-cover" />
                            <div class="p-8">
                                <span class="inline-flex items-center gap-2 text-[10px] font-semibold uppercase tracking-[0.3em] text-[#ee018d] mb-4" x-text="selectedCard?.note"></span>
                                <h2 class="text-2xl font-bold text-slate-900 mb-3" x-text="selectedCard?.title"></h2>
                                <p class="text-sm text-slate-500 mb-4" x-text="selectedCard?.description"></p>
                                <p class="text-sm text-slate-600 leading-relaxed" x-text="selectedCard?.detail"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Trusted Partners Section -->
        <div class="bg-gradient-to-br from-[#216417] to-[#14400e] text-white rounded-[2rem] p-8 sm:p-12 shadow-xl mb-16 amiga-animate-on-scroll amiga-transition">
            <div class="max-w-3xl mx-auto text-center">
                <h3 class="text-2xl font-bold mb-4">Our Trusted Travel Operators</h3>
                <p class="text-emerald-100/90 mb-8 max-w-xl mx-auto">
                    We maintain strong, direct partnerships with major sea transit, cargo, and airline networks to bring you reliable service at competitive rates.
                </p>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-6 items-stretch justify-items-center">
                    <div class="bg-white backdrop-blur-sm px-4 py-4 rounded-2xl w-full text-center flex items-center justify-center h-24 hover:bg-pink-50 transition shadow-sm">
                        <img src="{{ asset('images/2GO-Logo.png') }}" alt="2GO" class="max-h-full max-w-full object-contain">
                    </div>
                    <div class="bg-white backdrop-blur-sm px-4 py-4 rounded-2xl w-full text-center flex items-center justify-center h-24 hover:bg-pink-50 transition shadow-sm">
                        <img src="{{ asset('images/Starlite_Logo.png') }}" alt="Starlite" class="max-h-full max-w-full object-contain">
                    </div>
                    <div class="bg-white backdrop-blur-sm px-4 py-4 rounded-2xl w-full text-center flex items-center justify-center h-24 hover:bg-pink-50 transition shadow-sm">
                        <img src="{{ asset('images/Pal-Logo.jfif') }}" alt="Philippine Airline" class="max-h-full max-w-full object-contain">
                    </div>
                    <div class="bg-white backdrop-blur-sm px-4 py-4 rounded-2xl w-full text-center flex items-center justify-center h-24 hover:bg-pink-50 transition shadow-sm">
                        <img src="{{ asset('images/CebuPecific-Logo.png') }}" alt="Cebu Pacific" class="max-h-full max-w-full object-contain">
                    </div>
                    <div class="bg-white backdrop-blur-sm px-4 py-4 rounded-2xl w-full text-center flex items-center justify-center h-24 hover:bg-pink-50 transition shadow-sm">
                        <img src="{{ asset('images/AirAsia-Logo.png') }}" alt="AirAsia" class="max-h-full max-w-full object-contain">
                    </div>
                </div>
            </div>
        </div>


        <!-- Call to Action -->
        <div class="text-center amiga-animate-on-scroll amiga-transition">
            <h3 class="text-xl font-bold text-slate-900">Kay Amiga, Hassle Free Ka!</h3>
            <p class="text-sm text-black font-semibold mt-2">Ready to plan your next travel or educational tour? Let's connect.</p>
            <div class="mt-6 flex justify-center gap-4">
                <a href="{{ url('/book/new') }}" class="px-6 py-3 bg-[#216417] text-white font-semibold rounded-full shadow-lg hover:bg-green-800 transition">
                    Book Now
                </a>
                <a href="{{ url('/contact-us') }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-700 font-semibold rounded-full hover:bg-slate-50 transition">
                    Contact Us
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
