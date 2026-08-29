@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">

    {{-- Header --}}
    <div class="mb-10 text-center">
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Privacy Policy</h1>
        <p class="mt-3 text-slate-500 text-sm">Last updated: {{ date('F d, Y') }}</p>
        <div class="mt-4 mx-auto w-16 h-1 rounded-full bg-gradient-to-r from-[#008000] to-[#ee018d]"></div>
    </div>

    {{-- Card --}}
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl border border-slate-100 divide-y divide-slate-100">

        {{-- Intro --}}
        <div class="px-6 sm:px-10 py-8">
            <p class="text-slate-600 leading-relaxed">
                Welcome to <strong class="text-slate-800">Amiga Gracia Travel Services</strong>. We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains how we collect, use, and safeguard your information when you use our website and mobile application.
            </p>
            <p class="mt-4 text-slate-600 leading-relaxed">
                By using our services, you agree to the collection and use of information in accordance with this policy.
            </p>
        </div>

        {{-- Section 1 --}}
        <div class="px-6 sm:px-10 py-8">
            <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#008000] text-white text-xs font-black">1</span>
                Information We Collect
            </h2>
            <ul class="space-y-2 text-slate-600">
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span><strong class="text-slate-700">Personal Identification:</strong> Full name, email address, phone number, and date of birth.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span><strong class="text-slate-700">Passenger Details:</strong> Names and ages of co-passengers included in your booking.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span><strong class="text-slate-700">Booking Information:</strong> Travel routes, schedules, seat classes, booking references, and transaction history.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span><strong class="text-slate-700">Payment Information:</strong> Payment method and proof of payment uploads. We do not store full card numbers or PINs.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span><strong class="text-slate-700">Device &amp; Usage Data:</strong> Device type, OS, app version, and usage patterns to improve our service.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span><strong class="text-slate-700">Referral &amp; Loyalty Data:</strong> Referral codes, Gracia Coins balance, and voucher redemption history.</span></li>
            </ul>
        </div>

        {{-- Section 2 --}}
        <div class="px-6 sm:px-10 py-8">
            <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#008000] text-white text-xs font-black">2</span>
                How We Use Your Information
            </h2>
            <ul class="space-y-2 text-slate-600">
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span>Process and confirm your ferry and airline ticket bookings.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span>Send booking confirmations, e-tickets, and travel updates via email and push notifications.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span>Process rebooking, cancellation, and refund requests.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span>Manage your Gracia Coins rewards and referral program.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span>Apply voucher discounts to eligible bookings.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span>Provide customer support and respond to inquiries.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span>Improve the functionality, security, and experience of our platform.</span></li>
            </ul>
        </div>

        {{-- Section 3 --}}
        <div class="px-6 sm:px-10 py-8">
            <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#008000] text-white text-xs font-black">3</span>
                Sharing of Information
            </h2>
            <p class="text-slate-600 mb-3">We do <strong>not</strong> sell, rent, or trade your personal information. We share only when necessary:</p>
            <ul class="space-y-2 text-slate-600">
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span><strong class="text-slate-700">Transport Operators:</strong> Passenger names and booking details are shared with ferry and airline operators (e.g., Starlite Ferries, 2GO Travel, Cebu Pacific, PAL, AirAsia) to fulfill your booking.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span><strong class="text-slate-700">Payment Verification:</strong> Payment proof may be reviewed by authorized staff for manual verification.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span><strong class="text-slate-700">Legal Requirements:</strong> We may disclose your information if required by Philippine law or government authority.</span></li>
            </ul>
        </div>

        {{-- Section 4 --}}
        <div class="px-6 sm:px-10 py-8">
            <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#008000] text-white text-xs font-black">4</span>
                Data Retention
            </h2>
            <p class="text-slate-600 leading-relaxed">
                We retain your personal data for as long as your account is active or as needed to provide our services. Booking records are kept for a minimum of <strong class="text-slate-700">3 years</strong> for legal and audit compliance. You may request deletion of your account and associated data at any time, subject to retention obligations required by law.
            </p>
        </div>

        {{-- Section 5 --}}
        <div class="px-6 sm:px-10 py-8">
            <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#008000] text-white text-xs font-black">5</span>
                Data Security
            </h2>
            <p class="text-slate-600 leading-relaxed">
                We implement industry-standard security measures including encrypted data transmission (HTTPS), hashed passwords, and access-controlled systems. However, no method of transmission over the internet is 100% secure.
            </p>
        </div>

        {{-- Section 6 --}}
        <div class="px-6 sm:px-10 py-8">
            <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#008000] text-white text-xs font-black">6</span>
                Your Rights (Data Privacy Act of 2012)
            </h2>
            <p class="text-slate-600 mb-3">Under Republic Act No. 10173, you have the right to:</p>
            <ul class="space-y-2 text-slate-600">
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span><strong class="text-slate-700">Access</strong> &mdash; Request a copy of the personal data we hold about you.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span><strong class="text-slate-700">Rectification</strong> &mdash; Request correction of inaccurate or incomplete data.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span><strong class="text-slate-700">Erasure</strong> &mdash; Request deletion of your personal data where it is no longer necessary.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span><strong class="text-slate-700">Object</strong> &mdash; Object to the processing of your personal data.</span></li>
                <li class="flex gap-2"><span class="text-[#008000] font-bold mt-0.5">&bull;</span><span><strong class="text-slate-700">Portability</strong> &mdash; Receive your data in a structured, readable format.</span></li>
            </ul>
        </div>

        {{-- Section 7 --}}
        <div class="px-6 sm:px-10 py-8">
            <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#008000] text-white text-xs font-black">7</span>
                Children's Privacy
            </h2>
            <p class="text-slate-600 leading-relaxed">
                Our services are not directed to children under 13 years of age. Passenger details for child travelers are collected only as part of an adult's booking. If you believe a child's data has been submitted without proper consent, please contact us immediately.
            </p>
        </div>

        {{-- Section 8 --}}
        <div class="px-6 sm:px-10 py-8">
            <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#008000] text-white text-xs font-black">8</span>
                Changes to This Policy
            </h2>
            <p class="text-slate-600 leading-relaxed">
                We may update this Privacy Policy from time to time. When we do, we will revise the "Last updated" date at the top of this page. Your continued use of our services after any changes constitutes your acceptance of the updated policy.
            </p>
        </div>

        {{-- Contact --}}
        <div class="px-6 sm:px-10 py-8 bg-gradient-to-br from-emerald-50 to-white rounded-b-2xl">
            <h2 class="text-lg font-bold text-slate-800 mb-4">Contact Us</h2>
            <p class="text-slate-600 mb-4">If you have any questions or requests regarding this Privacy Policy, please contact us:</p>
            <div class="space-y-2 text-slate-700">
                <p><strong class="text-slate-800">Amiga Gracia Travel Services</strong></p>
                <p>Roxas Drive, Libis, Calapan City, Oriental Mindoro, 5200</p>
                <p>Email: <a href="mailto:agtsreservation@amigagracia.com" class="text-[#008000] hover:underline font-medium">agtsreservation@amigagracia.com</a></p>
                <p>Phone: 0930-928-4278 | (043) 738-2989</p>
            </div>
        </div>

    </div>

    {{-- Back link --}}
    <div class="mt-8 text-center">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-[#008000] transition font-medium">
            &larr; Back to Home
        </a>
    </div>
</div>
@endsection
