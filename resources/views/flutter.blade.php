@extends('layouts.app')

@section('content')
<div class="py-12 bg-slate-900 text-white min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="px-3 py-1 text-xs font-semibold bg-emerald-500/10 text-emerald-400 rounded-full border border-emerald-500/20">Flutter Integration</span>
            <h1 class="text-4xl font-extrabold tracking-tight text-white mt-4 sm:text-5xl">
                Amiga Gracia <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-400">Mobile & Web App</span>
            </h1>
            <p class="mt-4 text-slate-400 text-lg">
                Seamlessly integrated Flutter application within your Laravel system.
            </p>
        </div>

        @php
            $flutterBuildExists = file_exists(public_path('flutter-assets/index.html'));
        @endphp

        @if($flutterBuildExists)
            <!-- Flutter Web App Live Iframe Container -->
            <div class="bg-slate-950 rounded-2xl border border-slate-800 shadow-2xl overflow-hidden">
                <div class="bg-slate-900 px-6 py-4 border-b border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                        </span>
                        <span class="text-sm font-semibold text-slate-300">Live Flutter Web Build Running</span>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="document.getElementById('flutter-frame').contentWindow.location.reload();" class="p-1.5 hover:bg-slate-800 rounded text-slate-400 hover:text-white transition" title="Reload App">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 3v5h-5" /></svg>
                        </button>
                    </div>
                </div>
                <div class="relative w-full" style="height: 75vh;">
                    <iframe id="flutter-frame" src="/flutter-assets/index.html" class="absolute inset-0 w-full h-full border-0"></iframe>
                </div>
            </div>
        @else
            <!-- Setup Instructions and Interactive Simulator Mock -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                
                <!-- Left: Beautiful smartphone simulator preview -->
                <div class="lg:col-span-5 flex justify-center">
                    <div class="relative w-[340px] h-[680px] bg-slate-950 rounded-[45px] p-4 border-[6px] border-slate-800 shadow-[0_0_50px_rgba(0,0,0,0.8)] flex flex-col overflow-hidden">
                        <!-- Speaker & Camera notch -->
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-40 h-6 bg-slate-800 rounded-b-2xl z-50 flex items-center justify-center gap-2">
                            <div class="w-12 h-1 bg-slate-900 rounded-full"></div>
                            <div class="w-2.5 h-2.5 bg-slate-900 rounded-full"></div>
                        </div>

                        <!-- Simulator Screen Content -->
                        <div class="flex-grow rounded-[32px] bg-[#0F172A] overflow-hidden flex flex-col relative pt-6 text-slate-100">
                            <!-- Status Bar -->
                            <div class="px-5 py-2 flex justify-between items-center text-[11px] font-medium text-slate-400">
                                <span>9:41</span>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M12 3c-4.97 0-9 4.03-9 9 0 2.12.74 4.07 1.97 5.61L4.35 19.4c-.39.39-.39 1.02 0 1.41.39.39 1.02.39 1.41 0l1.79-1.79C9.09 19.68 10.5 20 12 20c4.97 0 9-4.03 9-9s-4.03-9-9-9zm0 15c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6z"/></svg>
                                    <span class="w-4 h-2.5 border border-slate-500 rounded-sm relative flex items-center p-0.5"><span class="bg-slate-400 h-full w-2"></span></span>
                                </div>
                            </div>

                            <!-- Mock Flutter App Interface -->
                            <div class="flex-grow flex flex-col p-5">
                                <div class="flex items-center justify-between mb-6">
                                    <div>
                                        <p class="text-xs text-slate-400">Kumusta,</p>
                                        <h3 class="text-base font-bold">Amiga Traveler</h3>
                                    </div>
                                    <div class="w-9 h-9 rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                    </div>
                                </div>

                                <div class="bg-gradient-to-r from-emerald-600 to-teal-600 rounded-2xl p-4 mb-6 shadow-lg relative overflow-hidden">
                                    <div class="absolute right-0 bottom-0 opacity-10 translate-x-4 translate-y-4">
                                        <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M10.18 9c-.39 0-.75-.24-.9-.61L7.5 4H4c-.55 0-1-.45-1-1s.45-1 1-1h4c.39 0 .75.24.9.61L10.68 7H20c.55 0 1 .45 1 1s-.45 1-1 1h-9.82z"/></svg>
                                    </div>
                                    <h4 class="text-sm font-semibold text-white/90">Explore Island Transit</h4>
                                    <p class="text-2xl font-bold mt-1 text-white">Book Ferry Tickets</p>
                                    <p class="text-xs text-white/70 mt-2">Smooth & secure travel inside Oriental Mindoro</p>
                                </div>

                                <div class="mb-4">
                                    <h5 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Quick Actions</h5>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-3 text-center">
                                            <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center mx-auto mb-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                            </div>
                                            <span class="text-xs font-medium">New Booking</span>
                                        </div>
                                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-3 text-center">
                                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center mx-auto mb-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                            </div>
                                            <span class="text-xs font-medium">Ticket Status</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-auto bg-slate-900 border border-slate-800 rounded-2xl p-4 text-center">
                                    <p class="text-xs text-slate-400">Ready to build</p>
                                    <h4 class="text-sm font-semibold mt-1">Flutter App Inside Laravel</h4>
                                    <div class="mt-3 flex justify-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-slate-600"></span>
                                        <span class="w-2 h-2 rounded-full bg-slate-600"></span>
                                        <span class="w-2 h-2 rounded-full bg-slate-600"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Bottom Home Bar -->
                            <div class="pb-3 flex justify-center">
                                <div class="w-32 h-1 bg-slate-700 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Setup Instructions & Step-by-Step details -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 shadow-xl">
                        <h3 class="text-lg font-bold text-slate-100 flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            Flutter App Development Guide
                        </h3>
                        <p class="text-sm text-slate-400 leading-relaxed mb-4">
                            A Flutter app workspace has been created successfully in <code class="text-emerald-400 bg-slate-900 px-1.5 py-0.5 rounded">/flutter_app</code>. Follow these steps to build and link the app inside the Laravel route.
                        </p>

                        <!-- Step List -->
                        <div class="space-y-4">
                            <!-- Step 1 -->
                            <div class="flex gap-4">
                                <div class="w-6 h-6 rounded-full bg-slate-800 flex items-center justify-center text-xs font-bold text-emerald-400 shrink-0">1</div>
                                <div class="space-y-1">
                                    <h4 class="text-sm font-semibold text-slate-200">Verify/Install Flutter SDK</h4>
                                    <p class="text-xs text-slate-400">Download and extract the Flutter SDK from the <a href="https://docs.flutter.dev/get-started/install" target="_blank" class="text-emerald-400 hover:underline">official Flutter website</a> and add it to your system Environment Variables (PATH).</p>
                                </div>
                            </div>

                            <!-- Step 2 -->
                            <div class="flex gap-4">
                                <div class="w-6 h-6 rounded-full bg-slate-800 flex items-center justify-center text-xs font-bold text-emerald-400 shrink-0">2</div>
                                <div class="space-y-2">
                                    <h4 class="text-sm font-semibold text-slate-200">Compile the Web Build</h4>
                                    <p class="text-xs text-slate-400">Navigate to the Flutter directory and run the compilation command:</p>
                                    <div class="relative bg-slate-900 rounded-lg p-3 text-xs font-mono text-slate-300 border border-slate-800">
                                        cd flutter_app<br>
                                        flutter build web --base-href "/flutter-assets/"
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3 -->
                            <div class="flex gap-4">
                                <div class="w-6 h-6 rounded-full bg-slate-800 flex items-center justify-center text-xs font-bold text-emerald-400 shrink-0">3</div>
                                <div class="space-y-2">
                                    <h4 class="text-sm font-semibold text-slate-200">Link Build Assets to Laravel Public</h4>
                                    <p class="text-xs text-slate-400">Copy the compiled build artifacts to the Laravel public directory (or create a symbolic link):</p>
                                    <div class="relative bg-slate-900 rounded-lg p-3 text-xs font-mono text-slate-300 border border-slate-800">
                                        # Windows Powershell command:<br>
                                        New-Item -ItemType SymbolicLink -Path "..\public\flutter-assets" -Target "build\web"
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Environment & Location Box -->
                    <div class="bg-slate-950 p-6 rounded-2xl border border-slate-800 shadow-xl flex flex-col md:flex-row gap-4 items-start md:items-center justify-between">
                        <div>
                            <h4 class="text-sm font-bold text-slate-200">Flutter Code Location</h4>
                            <p class="text-xs text-slate-400 mt-1">Ready for editing in your editor.</p>
                        </div>
                        <a href="file:///c:/laragon/www/amiga-travel/flutter_app/lib/main.dart" class="text-xs bg-slate-800 text-slate-300 hover:text-white px-4 py-2 rounded-lg border border-slate-700 hover:bg-slate-700 transition">
                            Open main.dart
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
