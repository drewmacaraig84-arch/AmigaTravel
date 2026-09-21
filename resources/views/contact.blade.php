@extends('layouts.app')

@section('content')
<div class="bg-transparent min-h-screen py-12 px-4 sm:px-6 lg:px-8" x-data="{ 
    submitted: false,
    name: '',
    email: '',
    subject: '',
    message: '',
    loading: false,
    async submitForm() {
        if (!this.name || !this.email || !this.message) return;
        this.loading = true;

        try {
            const response = await fetch('{{ route('contact.submit') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    name: this.name,
                    email: this.email,
                    subject: this.subject,
                    message: this.message,
                })
            });

            if (!response.ok) {
                throw new Error('Unable to send inquiry');
            }

            this.loading = false;
            this.submitted = true;
            this.name = '';
            this.email = '';
            this.subject = '';
            this.message = '';
        } catch (error) {
            this.loading = false;
            alert('Unable to send your inquiry right now. Please try again later.');
        }
    }
}">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-16 relative ws-sbtn-container amiga-animate-on-scroll amiga-transition">
            @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'hero' })" class="ws-sbtn absolute top-0 right-0"></button> @endif
            <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700 bg-emerald-100 px-3 py-1 rounded-full">{{ data_get($pageContent, 'badge', 'Contact Us') }}</span>
            <h1 class="mt-4 text-4xl sm:text-5xl font-black text-slate-900 tracking-tight">{{ data_get($pageContent, 'title', 'Ready To Explore? Let\'s Connect and Start Planning Your Next Adventure') }}</h1>
            <p class="mt-4 text-lg text-black font-semibold max-w-2xl mx-auto">
                {{ data_get($pageContent, 'description', 'Have questions about routes, ticketing, or custom tour packages? Drop us a message, and our travel specialists will get back to you shortly.') }}
            </p>
        </div>

        <div class="grid lg:grid-cols-3 gap-8 items-stretch">
            <!-- Contact Info Sidebar -->
            <div class="lg:col-span-1 space-y-6 relative ws-sbtn-container amiga-animate-on-scroll amiga-transition">
                @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'sidebar' })" class="ws-sbtn absolute -top-4 -right-4 z-10"></button> @endif
                <!-- Phone -->
                <div class="bg-white/85 backdrop-blur-md p-6 sm:p-8 rounded-[2rem] shadow-md ring-1 ring-slate-100 flex items-start gap-4">
                    <div class="h-12 w-12 bg-pink-100 rounded-2xl flex items-center justify-center text-pink-600 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold text-slate-900">{{ data_get($pageContent, 'phone_label', 'Phone Numbers') }}</h3>
                        <div class="mt-2 text-sm text-slate-500 font-semibold space-y-1">
                            {!! nl2br(e(data_get($pageContent, 'phones', "Mobile: 0930-928-4278\nLandline: (043) 738-2989"))) !!}
                        </div>
                    </div>
                </div>

                <!-- Email -->
                <div class="bg-white/85 backdrop-blur-md p-6 sm:p-8 rounded-[2rem] shadow-md ring-1 ring-slate-100 flex items-start gap-4">
                    <div class="h-12 w-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-700 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold text-slate-900">{{ data_get($pageContent, 'email_label', 'Email Addresses') }}</h3>
                        <div class="mt-2 text-sm text-slate-500 font-semibold space-y-1">
                            @foreach(explode("\n", data_get($pageContent, 'emails', "agtsreservation@amigagracia.com\namigagracia.travelservices@gmail.com")) as $email)
                                @if(trim($email))
                                    <p class="truncate hover:text-emerald-700"><a href="mailto:{{ trim($email) }}">{{ trim($email) }}</a></p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="bg-white/85 backdrop-blur-md p-6 sm:p-8 rounded-[2rem] shadow-md ring-1 ring-slate-100 flex items-start gap-4">
                    <div class="h-12 w-12 bg-blue-100 rounded-2xl flex items-center justify-center text-blue-600 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold text-slate-900">{{ data_get($pageContent, 'location_label', 'Office Location') }}</h3>
                        <p class="mt-2 text-sm text-slate-500 leading-relaxed font-semibold">
                            {!! nl2br(e(data_get($pageContent, 'location_address', "Roxas Drive, Libis, Calapan City,\nOriental Mindoro, 5200"))) !!}
                        </p>
                    </div>
                </div>

                <!-- Socials -->
                <div class="bg-white/85 backdrop-blur-md p-6 sm:p-8 rounded-[2rem] shadow-md ring-1 ring-slate-100 flex items-start gap-4">
                    <div class="h-12 w-12 bg-purple-100 rounded-2xl flex items-center justify-center text-purple-600 flex-shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="font-bold text-slate-900">{{ data_get($pageContent, 'socials_label', 'Social Media') }}</h3>
                        <div class="mt-2 text-sm text-slate-500 font-semibold space-y-1">
                            @php
                                $socials = data_get($pageContent, 'social_links', [['name' => 'Facebook: Amiga Gracia', 'url' => 'https://www.facebook.com/profile.php?id=100072122019511']]);
                            @endphp
                            @foreach($socials as $social)
                                <p class="hover:text-purple-600"><a href="{{ data_get($social, 'url') }}" target="_blank">{{ data_get($social, 'name') }}</a></p>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="lg:col-span-2 bg-white/85 backdrop-blur-md rounded-[2rem] p-6 sm:p-10 shadow-md ring-1 ring-slate-100 flex flex-col justify-between relative ws-sbtn-container amiga-animate-on-scroll amiga-transition" style="transition-delay: 100ms;">
                @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'form_labels' })" class="ws-sbtn absolute top-2 right-2"></button> @endif
                <div class="relative">
                    <h2 class="text-2xl font-bold text-slate-900 mb-6">{{ data_get($pageContent, 'form_title', 'Send an Inquiry') }}</h2>

                    <!-- Success Message -->
                    <div x-show="submitted" x-transition class="p-6 bg-emerald-50 rounded-2xl border border-emerald-200 text-emerald-800 mb-6 flex gap-4 items-start">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h3 class="font-bold text-emerald-950">{{ data_get($pageContent, 'success_title', 'Inquiry Sent Successfully!') }}</h3>
                            <p class="text-xs text-emerald-700 mt-1">{{ data_get($pageContent, 'success_desc', 'Thank you for contacting us. One of our travel consultants will get in touch with you shortly at the email address provided.') }}</p>
                            <button type="button" @click="submitted = false" class="mt-4 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-full transition">{{ data_get($pageContent, 'success_btn', 'Send Another Message') }}</button>
                        </div>
                    </div>

                    <!-- Main Form -->
                    <form @submit.prevent="submitForm()" x-show="!submitted" class="space-y-6">
                        <div class="grid sm:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ data_get($pageContent, 'label_name', 'Your Name *') }}</label>
                                <input type="text" id="name" x-model="name" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#216417] text-sm text-slate-800">
                            </div>
                            <div>
                                <label for="email" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ data_get($pageContent, 'label_email', 'Email Address *') }}</label>
                                <input type="email" id="email" x-model="email" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#216417] text-sm text-slate-800">
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ data_get($pageContent, 'label_subject', 'Subject') }}</label>
                            <input type="text" id="subject" x-model="subject" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#216417] text-sm text-slate-800">
                        </div>

                        <div>
                            <label for="message" class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">{{ data_get($pageContent, 'label_message', 'Message *') }}</label>
                            <textarea id="message" x-model="message" rows="5" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-[#216417] text-sm text-slate-800 resize-none"></textarea>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" :disabled="loading" class="px-8 py-3.5 bg-[#216417] hover:bg-green-800 text-white font-bold rounded-full shadow-lg transition flex items-center gap-2 cursor-pointer disabled:opacity-50">
                                <span x-show="!loading">{{ data_get($pageContent, 'btn_send', 'Send Message') }}</span>
                                <span x-show="loading" class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ data_get($pageContent, 'btn_sending', 'Sending...') }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Embedded Map -->
        <div class="mt-12 bg-white/85 backdrop-blur-md rounded-[2rem] overflow-hidden shadow-md ring-1 ring-slate-100 relative ws-sbtn-container">
            @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'map' })" class="ws-sbtn absolute top-2 right-2 z-10"></button> @endif
            <div class="flex flex-col gap-4 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">{{ data_get($pageContent, 'map_title', 'Find Our Calapan Office') }}</h3>
                        <p class="mt-2 text-sm text-slate-500 max-w-2xl">
                            {{ data_get($pageContent, 'map_desc', 'Exact location: Roxas Drive, Libis, Calapan City, Oriental Mindoro, 5200') }}
                        </p>
                    </div>
                    <a href="{{ data_get($pageContent, 'map_link', 'https://www.google.com/maps/search/?api=1&query=13.414934994237933,121.18487931805026') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-full border border-emerald-200 bg-emerald-50 text-emerald-700 px-4 py-2 text-sm font-semibold transition hover:bg-emerald-100 hover:text-emerald-900">
                        {{ data_get($pageContent, 'map_btn', 'Open in Google Maps') }}
                    </a>
                </div>
            </div>
            <div class="aspect-[21/9] w-full overflow-hidden border-t border-slate-200">
                <iframe class="w-full h-full" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="{{ data_get($pageContent, 'map_embed', 'https://maps.google.com/maps?q=13.414934994237933,121.18487931805026&z=17&output=embed') }}" aria-label="Map showing office location"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection
