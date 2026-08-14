{{-- Why Travel With Amiga Gracia? -- System Capabilities Banner (Responsive: Mobile Swipable Carousel with Peek + Desktop 6-Col Grid) --}}
@php
    $whyTravelFeatures = [
        [
            'id' => 1,
            'title' => 'Simplify Your Booking Experience',
            'desc' => 'Feel the flexibility and simplicity throughout your booking process with instant online e-ticketing.',
            'bg' => 'bg-red-50/80',
            'text_color' => 'text-red-600',
        ],
        [
            'id' => 2,
            'title' => 'Wide Selection of Travel Products',
            'desc' => 'Enjoy memorable journeys with major partners and tour packages.',
            'bg' => 'bg-pink-50/80',
            'text_color' => 'text-pink-600',
        ],
        [
            'id' => 3,
            'title' => 'Exclusive Deals & Promotions',
            'desc' => 'Access daily promotions, special group packages, and competitive fares for all travelers.',
            'bg' => 'bg-red-50/80',
            'text_color' => 'text-red-600',
        ],
        [
            'id' => 4,
            'title' => 'Trusted Booking Expert Since 2017',
            'desc' => 'Together with our credible shipping and airline partners, fulfilling countless travelers\' needs since 2017.',
            'bg' => 'bg-emerald-50/80',
            'text_color' => 'text-emerald-600',
        ],
        [
            'id' => 5,
            'title' => 'Affectionate Customer Support',
            'desc' => 'Giving the best assistance, our dedicated customer support is ready to help you with every journey.',
            'bg' => 'bg-blue-50/80',
            'text_color' => 'text-blue-600',
        ],
        [
            'id' => 6,
            'title' => 'Seamless Local Payment & Ticketing',
            'desc' => 'A stress-free booking experience with convenient payment options, instant vouchers, and local currency.',
            'bg' => 'bg-amber-50/80',
            'text_color' => 'text-amber-600',
        ],
    ];
@endphp

<style>
    /* Hide scrollbar for Chrome, Safari and Opera */
    .amiga-no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    /* Hide scrollbar for IE, Edge and Firefox */
    .amiga-no-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }

    /* Subtle continuous floating animation for icons */
    @keyframes amigaFloatSubtle {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-7px); }
    }
    .amiga-float-subtle {
        animation: amigaFloatSubtle 4.5s ease-in-out infinite;
    }
    /* Stagger animation delays for a smooth wave effect */
    .amiga-delay-1 { animation-delay: 0s; }
    .amiga-delay-2 { animation-delay: 0.75s; }
    .amiga-delay-3 { animation-delay: 1.5s; }
    .amiga-delay-4 { animation-delay: 2.25s; }
    .amiga-delay-5 { animation-delay: 3.0s; }
    .amiga-delay-6 { animation-delay: 3.75s; }
</style>

<section class="bg-slate-100/95 border-t border-slate-200/80 py-12 sm:py-16 relative z-20 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl sm:text-3xl font-black text-slate-800 text-center mb-8 sm:mb-12 tracking-tight amiga-animate-on-scroll amiga-transition">
            Why travel with Amiga Gracia?
        </h2>

        {{-- MOBILE & TABLET VIEW: Horizontal Swipable Carousel with Adjacent Slide Peeking & Pagination Dots --}}
        <div x-data="{
                activeSlide: 0,
                slides: @js(array_keys($whyTravelFeatures)),
                scrollTo(index) {
                    this.activeSlide = index;
                    const container = this.$refs.carousel;
                    const slideEl = container.children[index];
                    if (slideEl) {
                        slideEl.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                    }
                },
                updateActive() {
                    const container = this.$refs.carousel;
                    if (!container) return;
                    const scrollLeft = container.scrollLeft;
                    const slideWidth = container.scrollWidth / this.slides.length;
                    const index = Math.min(this.slides.length - 1, Math.max(0, Math.round(scrollLeft / slideWidth)));
                    if (this.activeSlide !== index) {
                        this.activeSlide = index;
                    }
                }
             }" class="block lg:hidden relative w-full -mx-4 sm:-mx-6 amiga-animate-on-scroll amiga-transition">
            
            <div x-ref="carousel"
                 @scroll.passive="updateActive()"
                 class="flex overflow-x-auto snap-x snap-mandatory amiga-no-scrollbar gap-6 px-[14vw] sm:px-[20vw] py-4 items-center">
                @foreach($whyTravelFeatures as $index => $feature)
                    <div class="w-[72vw] sm:w-[60vw] shrink-0 snap-center flex flex-col items-center justify-center text-center select-none py-2 group cursor-pointer">
                        <div class="w-24 h-24 mb-5 relative flex items-center justify-center rounded-3xl bg-white shadow-lg border border-slate-200/80 transition-all duration-300 ease-out group-hover:-translate-y-2 group-hover:scale-110 group-hover:shadow-xl group-hover:border-slate-300 amiga-float-subtle amiga-delay-{{ $feature['id'] }}">
                            <div class="absolute inset-2.5 rounded-2xl {{ $feature['bg'] }} transition-transform duration-300 group-hover:scale-105 -z-0"></div>
                            <div class="transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                                @include('partials.why-travel-icon', ['id' => $feature['id'], 'color' => $feature['text_color']])
                            </div>
                        </div>
                        <h3 class="font-bold text-base sm:text-lg text-slate-800 mb-2 leading-snug px-2 group-hover:text-[#216417] transition-colors duration-200">
                            {{ $feature['title'] }}
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed font-medium max-w-xs mx-auto">
                            {{ $feature['desc'] }}
                        </p>
                    </div>
                @endforeach
            </div>

            {{-- Pagination Dots --}}
            <div class="flex items-center justify-center gap-2 mt-4">
                <template x-for="(slide, idx) in slides" :key="idx">
                    <button type="button"
                            @click="scrollTo(idx)"
                            :class="activeSlide === idx ? 'w-6 bg-slate-600' : 'w-2 bg-slate-300 hover:bg-slate-400'"
                            class="h-2 rounded-full transition-all duration-300 cursor-pointer focus:outline-none"
                            :aria-label="'Go to slide ' + (idx + 1)"></button>
                </template>
            </div>
        </div>

        {{-- DESKTOP VIEW: 6-Column Grid --}}
        <div class="hidden lg:grid lg:grid-cols-6 gap-8 text-center">
            @foreach($whyTravelFeatures as $feature)
                <div class="flex flex-col items-center group amiga-animate-on-scroll amiga-transition cursor-pointer">
                    <div class="w-20 h-20 mb-4 relative flex items-center justify-center rounded-2xl bg-white shadow-md border border-slate-200/70 transition-all duration-300 ease-out group-hover:-translate-y-2 group-hover:scale-110 group-hover:shadow-xl group-hover:border-slate-300 amiga-float-subtle amiga-delay-{{ $feature['id'] }}">
                        <div class="absolute inset-2 rounded-xl {{ $feature['bg'] }} transition-transform duration-300 group-hover:scale-105 -z-0"></div>
                        <div class="transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3">
                            @include('partials.why-travel-icon', ['id' => $feature['id'], 'color' => $feature['text_color']])
                        </div>
                    </div>
                    <h3 class="font-bold text-base text-slate-800 mb-2 leading-snug group-hover:text-[#216417] transition-colors duration-200">
                        {{ $feature['title'] }}
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-500 leading-relaxed font-medium">
                        {{ $feature['desc'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</section>
