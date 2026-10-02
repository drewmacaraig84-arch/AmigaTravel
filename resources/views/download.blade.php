@extends('layouts.app')

@section('content')
<div class="bg-transparent min-h-screen">

    <!-- Script for PWA Install Prompt -->
    <script>
        window.deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            window.deferredPrompt = e;
            console.log('beforeinstallprompt captured');
        });

        async function triggerAddToHomeScreen() {
            const modal = document.getElementById('ios-modal');
            if (window.deferredPrompt) {
                window.deferredPrompt.prompt();
                const { outcome } = await window.deferredPrompt.userChoice;
                window.deferredPrompt = null;
                if (outcome === 'accepted') {
                    modal.classList.add('hidden');
                    setTimeout(() => {
                        window.location.href = '/app/';
                    }, 500);
                }
            } else {
                // For browsers without programmatic install prompt (e.g. iOS Safari or already standalone),
                // navigate directly to /app/ where the full Amiga Gracia app is loaded
                window.location.href = '/app/';
            }
        }
    </script>

    <!-- Add to Home Screen Modal -->
    <div id="ios-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity backdrop-blur-sm" style="background-color: rgba(15, 23, 42, 0.4);" aria-hidden="true" onclick="document.getElementById('ios-modal').classList.add('hidden')"></div>

            <div class="relative z-10 w-full max-w-lg bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all border border-slate-100">
                <div class="px-6 py-8 sm:p-10">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-2xl bg-emerald-50 sm:mx-0 shadow-sm border border-emerald-100">
                            <img src="{{ asset('images/app-icon-original.png') }}" alt="Amiga Gracia" class="h-10 w-10 rounded-xl object-contain">
                        </div>
                        <div class="mt-4 text-center sm:mt-0 sm:ml-6 sm:text-left">
                            <h3 class="text-2xl leading-6 font-black text-slate-900" id="modal-title">
                                Add to Home Screen
                            </h3>
                            <div class="mt-4">
                                <p class="text-base text-slate-700 font-semibold leading-relaxed">
                                    How to add to iOS Home Screen
                                </p>
                                <p class="mt-2 text-sm text-slate-500 leading-relaxed text-left">
                                    1. Open this page in <strong>Safari</strong>.<br>
                                    2. Tap the <strong>Share</strong> button at the bottom of the screen.<br>
                                    3. Scroll down and tap <strong>Add to Home Screen</strong>.<br>
                                    4. Tap <strong>Add</strong> in the top right corner.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 sm:px-10 sm:flex sm:flex-row-reverse gap-3">
                    <button type="button" onclick="window.location.href = '/app/'" class="w-full inline-flex justify-center items-center rounded-xl border border-transparent shadow-sm px-6 py-3 bg-[#216417] text-base font-bold text-white hover:bg-[#14400e] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#216417] sm:w-auto sm:text-sm transition-colors">
                        Open Web App
                        <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                    <button type="button" onclick="document.getElementById('ios-modal').classList.add('hidden')" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-6 py-3 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 sm:w-auto sm:text-sm transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    @php
        $downloadSteps = $pageContent['download_steps'] ?? [
            [
                'number' => '1',
                'title' => 'Download APK',
                'description' => 'Tap the "Download Android APK" button to download the installation file to your device.',
                'icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4',
                'icon_color' => '#216417',
                'bg_color' => '#eaf5e8',
            ],
            [
                'number' => '2',
                'title' => 'Allow Install',
                'description' => 'Open the downloaded file and allow installation from unknown sources if your device prompts you.',
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'icon_color' => '#ee018d',
                'bg_color' => '#fce7f3',
            ],
            [
                'number' => '3',
                'title' => 'You\'re All Set!',
                'description' => 'The app icon appears on your home screen. Open it to start booking trips and earning points!',
                'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                'icon_color' => '#216417',
                'bg_color' => '#eaf5e8',
            ],
        ];
        $downloadFeatures = $pageContent['download_features'] ?? [
            [
                'title' => 'Lightning Fast',
                'description' => 'Loads instantly, even on slow connections.',
                'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
                'bg_color' => '#eaf5e8',
                'icon_color' => '#216417',
            ],
            [
                'title' => 'Home Screen Icon',
                'description' => 'Quick access from your phone\'s home screen.',
                'icon' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
                'bg_color' => '#fce7f3',
                'icon_color' => '#ee018d',
            ],
            [
                'title' => 'Secure & Private',
                'description' => 'Your data stays safe with HTTPS encryption.',
                'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                'bg_color' => '#eaf5e8',
                'icon_color' => '#216417',
            ],
            [
                'title' => 'Always Updated',
                'description' => 'Automatically gets the latest features.',
                'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                'bg_color' => '#eff6ff',
                'icon_color' => '#2563eb',
            ],
        ];
    @endphp

    @php
        $pubspecPath = base_path('flutter_app/pubspec.yaml');
        $version = '1.0.0+1';
        if (file_exists($pubspecPath)) {
            $content = file_get_contents($pubspecPath);
            if (preg_match('/^version:\s*(.+)$/m', $content, $matches)) {
                $version = trim($matches[1]);
            }
        }
        
        $apkPath = public_path('downloads/amiga-travel.apk');
        $size = file_exists($apkPath) ? round(filesize($apkPath) / 1048576, 1) . ' MB' : '17.6 MB';
    @endphp

    <!-- Hero Section -->
    <div class="relative overflow-hidden" style="background: linear-gradient(135deg, #216417 0%, #14400e 60%, #0a2d06 100%);">
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
        <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
            <!-- Background Glows -->
            <div class="absolute top-10 left-10 w-72 h-72 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 right-10 w-96 h-96 bg-[#ee018d]/10 rounded-full blur-3xl"></div>
            
            <!-- Faded Blurry Logo Watermark -->
            <img src="{{ asset('images/amiga_logo_white_outline.png') }}" alt="" class="absolute top-[15%] lg:top-[38%] left-1/2 lg:left-[28%] -translate-y-1/2 -translate-x-1/2 w-[350px] sm:w-[500px] lg:w-[750px] object-contain pointer-events-none" style="opacity: 0.08; filter: blur(3px);">
        </div>
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 pb-20 sm:pt-12 sm:pb-28 relative z-10">
            @include('partials.global-skeleton')
            <div class="flex flex-col lg:flex-row items-center gap-12">
                <!-- Left: Text Content -->
                <div class="flex-1 text-center lg:text-left relative ws-sbtn-container amiga-animate-on-scroll amiga-transition">
                    @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'hero' })" class="ws-sbtn absolute top-0 -right-4 z-10"></button> @endif
                    <div class="flex flex-col lg:flex-row items-center lg:items-start gap-6 mb-6">
                        <div class="flex flex-col items-center lg:items-start">
                            <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-emerald-400 bg-white/10 backdrop-blur-sm px-4 py-1.5 rounded-full border border-white/20 mb-3">
                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838l-3.598 1.543A3.002 3.002 0 007 13a3 3 0 00-2 5.236V18a1 1 0 001 1h8a1 1 0 001-1v-.764A3.001 3.001 0 0013 13a3.002 3.002 0 00-.244-1.18l2.85-1.22a1 1 0 000-1.84l-5.212-2.68zM7 14a1 1 0 100 2 1 1 0 000-2zm6 0a1 1 0 100 2 1 1 0 000-2z"/></svg>
                                {{ data_get($pageContent, 'badge', 'Now Available') }}
                            </span>
                            <h1 class="text-4xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                                {!! data_get($pageContent, 'title', 'Get the <span class="text-emerald-400">Amiga Gracia</span> App') !!}
                            </h1>
                        </div>
                    </div>
                    <p class="mt-6 text-base sm:text-lg text-white/80 max-w-lg mx-auto lg:mx-0 leading-relaxed">
                        {{ data_get($pageContent, 'description', 'Book ferry tickets, flights, and tour packages right from your phone. Available on Google Play, Huawei AppGallery, and as a direct Android APK — with iOS support coming soon.') }}
                    </p>

                    <!-- Download Badges (5 Store Buttons) -->
                    <div class="mt-8 lg:justify-start justify-center">
                        <p class="text-xs font-semibold uppercase tracking-widest text-white/50 mb-4 lg:text-left text-center">Available on</p>
                        <div class="flex flex-wrap gap-3 lg:justify-start justify-center">

                            {{-- 1. Google Play (ACTIVE) --}}
                            <a href="https://play.google.com/store/apps/details?id=com.amiga.travel.flutter_app"
                               target="_blank" rel="noopener noreferrer"
                               class="group relative inline-flex items-center gap-3 px-4 py-2.5 bg-black hover:bg-zinc-900 text-white rounded-xl border border-white/10 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 min-w-[155px]"
                               title="Get it on Google Play">
                                {{-- Official Google Play icon --}}
                                <svg class="h-7 w-7 shrink-0" viewBox="0 0 512 512" fill="none">
                                    <path d="M48.08 28.14c-6.37 3.54-10.58 10.37-10.58 18.94v417.83c0 8.57 4.21 15.41 10.58 18.95l1.1.64 234.05-234.05v-5.52L49.18 27.5l-1.1.64z" fill="#4285F4"/>
                                    <path d="M360.4 340.71l-78.17-78.17v-5.52l78.18-78.18 1.77 1.01 92.59 52.61c26.44 15.02 26.44 39.63 0 54.65L362.17 339.7l-1.77 1.01z" fill="#FBBC04"/>
                                    <path d="M362.17 339.7L282.23 259.76 48.08 493.86c8.71 9.22 23.1 10.36 39.34 1.16L362.17 339.7z" fill="#EA4335"/>
                                    <path d="M362.17 172.3L87.42 17.03C71.18 7.83 56.79 8.97 48.08 18.19l234.15 234.14L362.17 172.3z" fill="#34A853"/>
                                </svg>
                                <div class="flex flex-col leading-tight">
                                    <span class="text-[9px] font-medium text-white/60 uppercase tracking-wide">GET IT ON</span>
                                    <span class="text-sm font-bold text-white">Google Play</span>
                                </div>
                            </a>

                            {{-- 2. App Store (COMING SOON) --}}
                            <div class="group relative inline-flex items-center gap-3 px-4 py-2.5 bg-black/70 text-white/40 rounded-xl border border-white/10 shadow-lg cursor-not-allowed min-w-[155px] select-none"
                                 title="Coming Soon">
                                {{-- Official App Store icon (exact match to Image 2) --}}
                                <img src="{{ asset('images/badges/appstore-icon.png') }}"
                                     alt="Apple App Store"
                                     class="h-7 w-7 rounded-lg shrink-0 opacity-50 object-contain shadow-sm">
                                <div class="flex flex-col leading-tight">
                                    <span class="text-[9px] font-medium text-white/30 uppercase tracking-wide">DOWNLOAD ON THE</span>
                                    <span class="text-sm font-bold text-white/40">App Store</span>
                                </div>
                                <span class="absolute -top-2 -right-2 bg-amber-400 text-black text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wide shadow">Soon</span>
                            </div>

                            {{-- 3. App Gallery (ACTIVE) --}}
                            <a href="https://appgallery.cloud.huawei.com/ag/n/app/C118908953?locale=en_US&source=appshare&subsource=C118908953&shareTo=cn.wps.moffice_eng&shareFrom=appmarket&shareIds=09edb7c78c444967817cddaacd713db8_cn.wps.moffice_eng&callType=SHARE"
                               target="_blank" rel="noopener noreferrer"
                               class="group relative inline-flex items-center gap-3 px-4 py-2.5 bg-black hover:bg-zinc-900 text-white rounded-xl border border-white/10 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 min-w-[155px]"
                               title="Explore It On App Gallery">
                                {{-- Official Huawei AppGallery icon (exact match to Image 1) --}}
                                <img src="{{ asset('images/badges/appgallery-icon.png') }}"
                                     alt="Huawei AppGallery"
                                     class="h-7 w-7 rounded-md shrink-0 object-contain shadow-sm">
                                <div class="flex flex-col leading-tight">
                                    <span class="text-[9px] font-medium text-white/60 uppercase tracking-wide">EXPLORE IT ON</span>
                                    <span class="text-sm font-bold text-white">App Gallery</span>
                                </div>
                            </a>

                            {{-- 4. Download Here - Android (ACTIVE) --}}
                            <a href="{{ asset('downloads/amiga-travel.apk') }}"
                               download
                               class="group relative inline-flex items-center gap-3 px-4 py-2.5 bg-black hover:bg-zinc-900 text-white rounded-xl border border-white/10 shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 min-w-[155px]"
                               title="Download Android APK">
                                {{-- Official Android robot head --}}
                                <svg class="h-7 w-7 shrink-0" viewBox="0 0 32 32" fill="none">
                                    {{-- antennae --}}
                                    <line x1="11" y1="3" x2="8.5" y2="7" stroke="#3DDC84" stroke-width="2" stroke-linecap="round"/>
                                    <line x1="21" y1="3" x2="23.5" y2="7" stroke="#3DDC84" stroke-width="2" stroke-linecap="round"/>
                                    {{-- head --}}
                                    <path d="M6 14C6 10.13 10.48 7 16 7s10 3.13 10 7v1H6v-1z" fill="#3DDC84"/>
                                    {{-- eyes --}}
                                    <circle cx="12" cy="11.5" r="1.3" fill="#0d2b0e"/>
                                    <circle cx="20" cy="11.5" r="1.3" fill="#0d2b0e"/>
                                    {{-- body --}}
                                    <rect x="6" y="15" width="20" height="11" rx="2" fill="#3DDC84"/>
                                    {{-- arms --}}
                                    <rect x="1.5" y="15" width="3.5" height="8" rx="1.75" fill="#3DDC84"/>
                                    <rect x="27" y="15" width="3.5" height="8" rx="1.75" fill="#3DDC84"/>
                                    {{-- legs --}}
                                    <rect x="9" y="26" width="4" height="5" rx="2" fill="#3DDC84"/>
                                    <rect x="19" y="26" width="4" height="5" rx="2" fill="#3DDC84"/>
                                </svg>
                                <div class="flex flex-col leading-tight">
                                    <span class="text-[9px] font-medium text-white/60 uppercase tracking-wide">ANDROID</span>
                                    <span class="text-sm font-bold text-white">Download Here</span>
                                </div>
                            </a>

                            {{-- 5. Download Here - iOS (COMING SOON) --}}
                            <div class="group relative inline-flex items-center gap-3 px-4 py-2.5 bg-black/70 text-white/40 rounded-xl border border-white/10 shadow-lg cursor-not-allowed min-w-[155px] select-none"
                                 title="Coming Soon for iOS">
                                {{-- Official Apple logo (exact match to Image 3) --}}
                                <img src="{{ asset('images/badges/apple-icon.png') }}"
                                     alt="Apple iOS"
                                     class="h-7 w-7 shrink-0 opacity-45 object-contain">
                                <div class="flex flex-col leading-tight">
                                    <span class="text-[9px] font-medium text-white/30 uppercase tracking-wide">iOS</span>
                                    <span class="text-sm font-bold text-white/40">Download Here</span>
                                </div>
                                <span class="absolute -top-2 -right-2 bg-amber-400 text-black text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wide shadow">Soon</span>
                            </div>

                        </div>
                        <p class="mt-3 flex items-center gap-1.5 text-[11px] text-white/30 lg:justify-start justify-center">
                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            App Store &amp; iOS download coming soon.
                        </p>
                    </div>

                    <!-- App Info Grid -->
                    <div class="mt-8 pt-8 border-t border-white/10 grid grid-cols-2 sm:grid-cols-4 gap-4 text-white/70 max-w-lg mx-auto lg:mx-0 text-left">
                        <div>
                            <p class="text-[10px] uppercase font-bold tracking-wider text-emerald-400">Version</p>
                            <p class="text-sm font-semibold text-white mt-0.5">{{ $version }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold tracking-wider text-emerald-400">File Size</p>
                            <p class="text-sm font-semibold text-white mt-0.5">{{ $size }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold tracking-wider text-emerald-400">Platform</p>
                            <p class="text-sm font-semibold text-white mt-0.5">iOS &amp; Android</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold tracking-wider text-emerald-400">Verified</p>
                            <p class="text-sm font-semibold text-white mt-0.5 flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 text-emerald-400 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                Safe
                            </p>
                        </div>
                    </div>


                </div>

                <!-- Right: Phone Mockup -->
                <div class="flex-shrink-0 relative amiga-animate-on-scroll amiga-transition" style="transition-delay: 100ms;">
                    <div class="w-64 sm:w-72 h-[500px] sm:h-[560px] rounded-[3rem] border-[6px] border-white/20 bg-white/5 backdrop-blur-md shadow-2xl overflow-hidden relative">
                        <!-- Phone Notch -->
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-28 h-6 bg-black/40 rounded-b-2xl z-20"></div>
                        <!-- Phone Screen Content -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center">
                            <img src="{{ asset('images/app-icon-original.png') }}" alt="Amiga Gracia App" class="h-24 w-24 rounded-3xl mb-4 drop-shadow-xl border-2 border-white/25 bg-white object-contain">
                            <h3 class="text-white font-extrabold text-xl tracking-wide">Amiga Gracia</h3>
                            <p class="text-white/60 text-xs mt-1">Travel Services</p>
                            <div class="mt-6 w-full space-y-3">
                                <div class="bg-white/10 rounded-xl p-3 flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </div>
                                    <span class="text-white/80 text-xs font-medium">Book Ferry Tickets</span>
                                </div>
                                <div class="bg-white/10 rounded-xl p-3 flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                    </div>
                                    <span class="text-white/80 text-xs font-medium">Flight Bookings</span>
                                </div>
                                <div class="bg-white/10 rounded-xl p-3 flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                                        <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <span class="text-white/80 text-xs font-medium">Tour Packages</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Glow behind phone -->
                    <div class="absolute -inset-8 bg-emerald-500/10 rounded-full blur-3xl -z-10"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- APK Benefits Section -->
    @php
        $downloadFeatures = $pageContent['download_features'] ?? [
            [
                'title' => 'Gracia Point System',
                'description' => 'Earn points on every booking made through the app. Redeem them for discounts on your future trips!',
                'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'icon_color' => '#216417',
                'bg_color' => '#eaf5e8',
            ],
            [
                'title' => 'Exclusive Vouchers',
                'description' => 'Get access to app-only promotions, seasonal vouchers, and special partner discounts.',
                'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z',
                'icon_color' => '#ee018d',
                'bg_color' => '#fce7f3',
            ]
        ];
    @endphp
    @if(!empty($downloadFeatures))
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white/85 backdrop-blur-md rounded-[2rem] shadow-sm border border-slate-100 p-8 sm:p-12 amiga-animate-on-scroll amiga-transition">
            <div class="text-center mb-10">
                <span class="text-xs font-semibold uppercase tracking-wider px-3 py-1 rounded-full" style="color: #ee018d; background: #fce7f3;">{{ data_get($pageContent, 'apk_benefits_label', 'Exclusive App Benefits') }}</span>
                <h2 class="mt-4 text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ data_get($pageContent, 'apk_benefits_title', 'Why download the app?') }}</h2>
            </div>
            <div class="grid sm:grid-cols-2 gap-8 lg:gap-12">
                @foreach($downloadFeatures as $feature)
                <div class="flex gap-4">
                    <div class="h-14 w-14 shrink-0 rounded-2xl flex items-center justify-center shadow-sm" style="background: {{ data_get($feature, 'bg_color', '#eaf5e8') }}; color: {{ data_get($feature, 'icon_color', '#216417') }};">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ data_get($feature, 'icon', 'M13 10V3L4 14h7v7l9-11h-7z') }}" /></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-lg">{{ data_get($feature, 'title') }}</h3>
                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">{{ data_get($feature, 'description') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- How to Install Section — Platform Tabs -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="text-center mb-10 amiga-animate-on-scroll amiga-transition">
            <span class="text-xs font-semibold uppercase tracking-wider px-3 py-1 rounded-full" style="color: #216417; background: #eaf5e8;">Installation Guide</span>
            <h2 class="mt-4 text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Get Started on Any Device</h2>
            <p class="mt-3 text-slate-500 max-w-xl mx-auto">Choose your platform below and follow the steps to install the Amiga Gracia app.</p>
        </div>

        {{-- Platform Tab Switcher --}}
        <div x-data="{ tab: 'android' }" class="amiga-animate-on-scroll amiga-transition">

            {{-- Tab Pills --}}
            <div class="flex flex-wrap justify-center gap-2 mb-10">
                <button @click="tab = 'android'"
                    :class="tab === 'android' ? 'bg-[#216417] text-white shadow-lg' : 'bg-white text-slate-600 border border-slate-200 hover:border-[#216417] hover:text-[#216417]'"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full font-bold text-sm transition-all duration-200">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18c0 .55.45 1 1 1h1v3.5c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5V19h2v3.5c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5V19h1c.55 0 1-.45 1-1V8H6v10zM12 5a3 3 0 013 3H9a3 3 0 013-3zM19.5 8c-.83 0-1.5.67-1.5 1.5v6c0 .83.67 1.5 1.5 1.5s1.5-.67 1.5-1.5v-6c0-.83-.67-1.5-1.5-1.5zM4.5 8C3.67 8 3 8.67 3 9.5v6c0 .83.67 1.5 1.5 1.5S6 16.33 6 15.5v-6C6 8.67 5.33 8 4.5 8z"/></svg>
                    Android APK
                </button>
                <button @click="tab = 'stores'"
                    :class="tab === 'stores' ? 'bg-[#216417] text-white shadow-lg' : 'bg-white text-slate-600 border border-slate-200 hover:border-[#216417] hover:text-[#216417]'"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full font-bold text-sm transition-all duration-200">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg>
                    App Stores
                </button>
                <button @click="tab = 'ios'"
                    :class="tab === 'ios' ? 'bg-[#216417] text-white shadow-lg' : 'bg-white text-slate-600 border border-slate-200 hover:border-[#216417] hover:text-[#216417]'"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full font-bold text-sm transition-all duration-200">
                    <svg class="h-4 w-4" viewBox="0 0 814 1000" fill="currentColor"><path d="M788.1 340.9c-5.8 4.5-108.2 62.2-108.2 190.5 0 148.4 130.3 200.9 134.2 202.2-.6 3.2-20.7 71.9-68.7 141.9-42.8 61.6-87.5 123.1-155.5 123.1s-85.5-39.5-164-39.5c-76 0-103.7 40.8-165.9 40.8s-105-57.8-155.5-127.4C46 790.7 0 663 0 541.8c0-207.8 135.4-317.7 268.9-317.7 99.7 0 182.7 66.3 244.7 66.3 59.1 0 152.8-70.5 263.1-70.5zm-17.6-172.5c59.2-71.4 102.1-170.5 102.1-269.6 0-14.4-1.3-28.8-3.8-41.9-97.5 3.8-213 65.3-281.2 145.3-54.5 62.9-103.9 162-103.9 262.8 0 16.5 2.6 33 3.9 38.4 6.5 1.3 17 2.6 27.5 2.6 86.5 0 193.5-57.2 255.4-137.6z"/></svg>
                    iOS
                    <span class="text-[9px] font-black px-1.5 py-0.5 rounded-full bg-amber-400 text-black uppercase">Soon</span>
                </button>
            </div>

            {{-- Android APK Tab --}}
            <div x-show="tab === 'android'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid sm:grid-cols-3 gap-8 pt-4">

                    {{-- Step 1 --}}
                    <div class="relative bg-white/85 backdrop-blur-md rounded-[2rem] p-8 shadow-md ring-1 ring-slate-100 text-center group hover:shadow-lg transition amiga-animate-on-scroll amiga-transition">
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 h-8 w-8 rounded-full font-black text-sm flex items-center justify-center text-white shadow-md bg-[#216417]">1</div>
                        <div class="h-16 w-16 mx-auto rounded-2xl flex items-center justify-center mb-5 group-hover:scale-105 transition bg-[#eaf5e8]">
                            <svg class="h-8 w-8 text-[#216417]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-lg">Download APK</h3>
                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">Tap the <strong>"Android — Download Here"</strong> button above to save the APK file to your device.</p>
                    </div>

                    {{-- Step 2 --}}
                    <div class="relative bg-white/85 backdrop-blur-md rounded-[2rem] p-8 shadow-md ring-1 ring-slate-100 text-center group hover:shadow-lg transition amiga-animate-on-scroll amiga-transition" style="transition-delay: 100ms;">
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 h-8 w-8 rounded-full font-black text-sm flex items-center justify-center text-white shadow-md bg-[#ee018d]">2</div>
                        <div class="h-16 w-16 mx-auto rounded-2xl flex items-center justify-center mb-5 group-hover:scale-105 transition bg-[#fce7f3]">
                            <svg class="h-8 w-8 text-[#ee018d]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-lg">Allow Install</h3>
                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">Open the downloaded file. If prompted, allow installation from <strong>unknown sources</strong> in your Android settings.</p>
                    </div>

                    {{-- Step 3 --}}
                    <div class="relative bg-white/85 backdrop-blur-md rounded-[2rem] p-8 shadow-md ring-1 ring-slate-100 text-center group hover:shadow-lg transition amiga-animate-on-scroll amiga-transition" style="transition-delay: 200ms;">
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 h-8 w-8 rounded-full font-black text-sm flex items-center justify-center text-white shadow-md bg-[#216417]">3</div>
                        <div class="h-16 w-16 mx-auto rounded-2xl flex items-center justify-center mb-5 group-hover:scale-105 transition bg-[#eaf5e8]">
                            <svg class="h-8 w-8 text-[#216417]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-lg">You're All Set!</h3>
                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">The app icon appears on your home screen. Open it and start booking trips and earning Gracia Points!</p>
                    </div>

                </div>
                <p class="mt-6 text-center text-xs text-slate-400">Requires Android 8.0 or higher · APK version {{ $version }} · {{ $size }}</p>
            </div>

            {{-- App Stores Tab --}}
            <div x-show="tab === 'stores'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="grid sm:grid-cols-2 gap-8 pt-4 max-w-2xl mx-auto">

                    {{-- Google Play --}}
                    <a href="https://play.google.com/store/apps/details?id=com.amiga.travel.flutter_app" target="_blank" rel="noopener noreferrer"
                       class="group relative bg-white/85 backdrop-blur-md rounded-[2rem] p-8 shadow-md ring-1 ring-slate-100 text-center hover:shadow-xl transition amiga-animate-on-scroll amiga-transition flex flex-col items-center">
                        <div class="h-16 w-16 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-105 transition bg-[#eaf5e8]">
                            <svg class="h-9 w-9" viewBox="0 0 24 24" fill="none">
                                <path d="M3.18 23.76c.32.18.68.22 1.04.12L15.3 12 11.94 8.64 3.18 23.76z" fill="#EA4335"/>
                                <path d="M20.54 10.27l-2.62-1.49-3.26 3.07 3.26 3.08 2.65-1.51a1.5 1.5 0 000-2.62 1.5 1.5 0 00-.03-.13z" fill="#FBBC04"/>
                                <path d="M4.22.12A1.5 1.5 0 002 1.5v21a1.5 1.5 0 002.22 1.26L15.3 12 4.22.12z" fill="#4285F4"/>
                                <path d="M4.22.12L15.3 12l2.62-2.74L4.26.1a1.5 1.5 0 00-.04.02z" fill="#34A853"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-slate-900 text-lg">Google Play</h3>
                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">Search for <strong>Amiga Gracia</strong> on the Play Store or tap below to go directly to our listing.</p>
                        <span class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-[#216417] group-hover:underline">
                            Open in Play Store
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </a>

                    {{-- Huawei AppGallery --}}
                    <a href="https://appgallery.cloud.huawei.com/ag/n/app/C118908953?locale=en_US&source=appshare&subsource=C118908953&shareTo=cn.wps.moffice_eng&shareFrom=appmarket&shareIds=09edb7c78c444967817cddaacd713db8_cn.wps.moffice_eng&callType=SHARE" target="_blank" rel="noopener noreferrer"
                       class="group relative bg-white/85 backdrop-blur-md rounded-[2rem] p-8 shadow-md ring-1 ring-slate-100 text-center hover:shadow-xl transition amiga-animate-on-scroll amiga-transition flex flex-col items-center" style="transition-delay: 100ms;">
                        <div class="h-16 w-16 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-105 transition bg-red-50">
                            <img src="{{ asset('images/badges/appgallery-icon.png') }}" alt="Huawei AppGallery" class="h-11 w-11 rounded-xl object-contain shadow-sm">
                        </div>
                        <h3 class="font-bold text-slate-900 text-lg">Huawei AppGallery</h3>
                        <p class="text-sm text-slate-500 mt-2 leading-relaxed">For Huawei device owners — find <strong>Amiga Gracia</strong> on AppGallery and install it directly.</p>
                        <span class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-[#CF0A2C] group-hover:underline">
                            Open in AppGallery
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    </a>

                </div>
                <p class="mt-6 text-center text-xs text-slate-400">Tap the badge on your device to open the respective store listing.</p>
            </div>

            {{-- iOS Tab --}}
            <div x-show="tab === 'ios'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                <div class="max-w-lg mx-auto">
                    <div class="bg-white/85 backdrop-blur-md rounded-[2rem] p-10 shadow-md ring-1 ring-slate-100 text-center">
                        <div class="h-20 w-20 mx-auto rounded-3xl flex items-center justify-center mb-6 bg-slate-50">
                            <img src="{{ asset('images/badges/appstore-icon.png') }}" alt="Apple App Store" class="h-16 w-16 rounded-2xl object-contain shadow-sm">
                        </div>
                        <span class="inline-block bg-amber-100 text-amber-700 text-xs font-black uppercase tracking-wider px-3 py-1 rounded-full mb-4">Coming Soon</span>
                        <h3 class="font-black text-slate-900 text-2xl">iOS App Store</h3>
                        <p class="text-slate-500 mt-3 leading-relaxed">We're actively working on the iOS version of Amiga Gracia. It will be available on the <strong>Apple App Store</strong> soon — stay tuned!</p>
                        <div class="mt-8 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">In the meantime, iPhone &amp; iPad users can:</p>
                            <ul class="text-sm text-slate-600 space-y-2 text-left">
                                <li class="flex items-start gap-2"><svg class="h-4 w-4 text-[#216417] mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Visit <a href="{{ url('/') }}" class="font-semibold text-[#216417] hover:underline">amigagracia.com</a> from Safari to book online.</li>
                                <li class="flex items-start gap-2"><svg class="h-4 w-4 text-[#216417] mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg> Tap <strong>Share → Add to Home Screen</strong> in Safari for a shortcut icon.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Bottom CTA -->
    <div class="text-center pb-16 amiga-animate-on-scroll amiga-transition">
        <p class="text-sm text-slate-500">
            Need help?
            <a href="{{ url('/contact-us') }}" class="text-[#ee018d] font-semibold hover:underline">Contact our team</a>
            or visit our office at Roxas Drive, Libis, Calapan City.
        </p>
    </div>
</div>

<!-- PWA Install Script -->
<script>
    function pwaInstall() {
        return {
            deferredPrompt: null,
            canInstall: false,
            isInstalled: false,

            init() {
                // Check if already installed
                if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
                    this.isInstalled = true;
                }

                // Listen for beforeinstallprompt
                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    this.deferredPrompt = e;
                    this.canInstall = true;
                });

                // Listen for successful install
                window.addEventListener('appinstalled', () => {
                    this.canInstall = false;
                    this.isInstalled = true;
                    this.deferredPrompt = null;
                });
            },

            async install() {
                if (!this.deferredPrompt) return;
                this.deferredPrompt.prompt();
                const { outcome } = await this.deferredPrompt.userChoice;
                if (outcome === 'accepted') {
                    this.canInstall = false;
                    this.isInstalled = true;
                }
                this.deferredPrompt = null;
            }
        };
    }
</script>
@endsection
