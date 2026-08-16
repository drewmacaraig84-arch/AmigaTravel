<div class="space-y-6">

    {{-- ========================================================
         PAYMENT DEADLINE NOTICE (only shown before proof is submitted)
         ======================================================== --}}
    @if (!$showThankYou && !$isExpired && $deadlineTimestamp > 0)
        <div
            id="payment-deadline-notice"
            class="rounded-2xl border border-amber-200 bg-amber-50 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-start gap-3"
        >
            {{-- Icon --}}
            <div class="shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-amber-800 mb-1">Friendly Payment Reminder</p>
                <p class="text-sm text-amber-700 leading-relaxed">
                    We kindly ask that payment be completed within <strong>1 hour</strong> of receiving this link.
                    If payment is not received within this time, your booking will unfortunately be
                    <strong>automatically cancelled</strong> to allow others to reserve the spot.
                    We truly appreciate your understanding and prompt action!
                </p>

                {{-- Countdown --}}
                <div class="mt-3 flex items-center gap-2">
                    <span class="text-xs font-medium text-amber-700">Time remaining:</span>
                    <span
                        id="payment-countdown"
                        class="inline-flex items-center gap-1 rounded-full bg-amber-100 border border-amber-300 px-3 py-1 text-sm font-bold text-amber-800 tabular-nums"
                    >
                        <svg class="w-3.5 h-3.5 shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke-width="2"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/>
                        </svg>
                        <span id="countdown-text">Calculating…</span>
                    </span>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================
         EXPIRED / CANCELLED NOTICE
         ======================================================== --}}
    @if ($isExpired)
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 sm:p-6 text-center">
            <div class="flex justify-center mb-3">
                <svg class="w-10 h-10 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-rose-800">Booking Cancelled</h3>
            <p class="mt-2 text-sm text-rose-700">
                We're sorry — your booking has been automatically cancelled because payment was not
                received within the 1-hour window. Please create a new booking if you'd still like to travel with us.
                We hope to welcome you soon! 🙏
            </p>
            <a
                href="{{ url('/') }}"
                class="mt-4 inline-flex items-center justify-center rounded-3xl px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-90"
                style="background:#216417;"
            >
                Back to Home
            </a>
        </div>
    @endif

    {{-- ========================================================
         THANK YOU STATE (after proof uploaded)
         ======================================================== --}}
    @if ($showThankYou)
        <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4 sm:p-6 text-center">
            <h3 class="text-lg sm:text-xl font-semibold text-emerald-900">Thank you for your booking!</h3>
            <p class="mt-3 text-sm text-emerald-800">
                Your proof of payment has been received. We will verify your payment and update your booking status shortly.
                A confirmation email has been sent to <span class="font-medium">{{ $transaction->booking->client_email }}</span>.
            </p>
            <p class="mt-2 text-sm text-emerald-700">
                Transaction: <span class="font-semibold">{{ $transaction->booking->transaction_number }}</span>
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-center">
            <a
                href="{{ url('/?transaction_number=' . urlencode($transaction->booking->transaction_number) . '&show_cancel_suggestion=1') }}"
                class="inline-flex items-center justify-center rounded-3xl px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 w-full sm:w-auto"
                style="background:#216417;"
            >
                Done
            </a>
            <a
                href="{{ url('/book/status?transaction_number=' . urlencode($transaction->booking->transaction_number) . '&show_cancellation_reminder=1') }}"
                class="inline-flex items-center justify-center rounded-3xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 w-full sm:w-auto"
            >
                Check my booking
            </a>
        </div>
    @endif

    {{-- ========================================================
         PROOF UPLOAD FORM (only when not cancelled and not submitted)
         ======================================================== --}}
    @if (!$showThankYou && !$isExpired)
        <div class="space-y-4">
            <label class="block text-sm font-medium text-slate-700">
                Reference / Transaction Number
                <span class="text-xs font-normal text-slate-500">(e.g., GCash, Maya, Bank Transfer Ref No.)</span>
            </label>
            <input
                type="text"
                wire:model.live="reference_number"
                id="payment-reference-input"
                class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 shadow-sm focus:border-[#db2777] focus:outline-none focus:ring-2 focus:ring-[#db2777]/20 transition-all"
                placeholder="Enter payment reference number (e.g., GCash Ref No.)"
            />
            @error('reference_number')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror

            {{-- Helper notice: once ref number is entered other actions lock --}}
            <div id="ref-number-hint" class="hidden">
                <p class="text-xs text-slate-500 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    You've entered a reference number. Please upload your proof of payment and click <strong>Upload proof</strong> to complete.
                </p>
            </div>

            <label class="block text-sm font-medium text-slate-700">Upload proof of payment</label>
            <div class="mt-3">
                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-slate-300 border-dashed rounded-2xl cursor-pointer bg-slate-50 hover:bg-slate-100">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg class="w-8 h-8 mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        <p class="mb-1 text-sm text-slate-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                        <p class="text-xs text-slate-500">PNG, JPG, GIF up to 10MB</p>
                    </div>
                    <input type="file" wire:model.live="proof" class="hidden" />
                </label>
                @if($proof)
                    <div class="mt-3 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3 shadow-sm">
                        <img src="{{ $proof->temporaryUrl() }}" alt="Proof preview" class="h-16 w-16 rounded-lg object-cover border border-emerald-200 bg-white" />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-bold text-emerald-700 truncate">{{ $proof->getClientOriginalName() }}</p>
                            <p class="text-xs text-emerald-700/80">File selected successfully</p>
                        </div>
                        <label class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-emerald-200 bg-white text-emerald-700 hover:bg-emerald-100 cursor-pointer" title="Change image">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                            <input type="file" wire:model.live="proof" class="hidden" />
                        </label>
                    </div>
                @endif
                @error('proof')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            
            @if($isUploading)
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-slate-600">Uploading...</span>
                        <span class="text-sm font-semibold text-emerald-700">{{ $uploadProgress }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                        <div 
                            class="bg-emerald-600 h-2.5 rounded-full transition-all duration-300 ease-out"
                            style="width: {{ $uploadProgress }}%"
                        ></div>
                    </div>
                </div>
            @endif
        </div>

        <button 
            type="button" 
            wire:click.prevent="submitProof" 
            id="upload-proof-btn"
            class="inline-flex items-center justify-center rounded-3xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto"
            @disabled($isUploading || !$proof)
        >
            @if ($isUploading)
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Uploading...
            @else
                Upload proof
            @endif
        </button>
    @endif
</div>

{{-- ============================================================
     COUNTDOWN JAVASCRIPT
     Uses localStorage so the deadline persists across reloads.
     Key: amiga_payment_deadline_<transaction_id>
     ============================================================ --}}
@if (!$showThankYou && !$isExpired && $deadlineTimestamp > 0)
<script>
(function () {
    const SERVER_DEADLINE = {{ $deadlineTimestamp }};
    const STORAGE_KEY     = 'amiga_payment_deadline_{{ $transaction->id }}';
    const countdownEl     = document.getElementById('countdown-text');
    const noticeEl        = document.getElementById('payment-deadline-notice');

    if (! countdownEl) return;

    // Persist deadline in localStorage so refresh doesn't reset it.
    // Always trust the server value if it comes BEFORE what's stored
    // (meaning the server reset it – e.g. admin extended). Otherwise keep
    // the stored value so the user can't gain extra time by reloading.
    let storedDeadline = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10);

    if (SERVER_DEADLINE > 0) {
        // If nothing stored, or server deadline is earlier (shorter window), use server
        if (storedDeadline === 0 || SERVER_DEADLINE < storedDeadline) {
            storedDeadline = SERVER_DEADLINE;
            localStorage.setItem(STORAGE_KEY, storedDeadline);
        }
    }

    const DEADLINE = storedDeadline;

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function tick() {
        const now = Math.floor(Date.now() / 1000);
        const remaining = DEADLINE - now;

        if (remaining <= 0) {
            countdownEl.textContent = '00:00:00';
            if (noticeEl) {
                noticeEl.classList.remove('border-amber-200', 'bg-amber-50');
                noticeEl.classList.add('border-rose-200', 'bg-rose-50');
                // Update text colour
                noticeEl.querySelectorAll('.text-amber-800, .text-amber-700, .text-amber-500').forEach(el => {
                    el.classList.add('text-rose-700');
                    el.classList.remove('text-amber-800', 'text-amber-700', 'text-amber-500');
                });
                const countdownBadge = document.getElementById('payment-countdown');
                if (countdownBadge) {
                    countdownBadge.classList.remove('bg-amber-100', 'border-amber-300', 'text-amber-800');
                    countdownBadge.classList.add('bg-rose-100', 'border-rose-300', 'text-rose-800');
                }
            }
            // Reload after 3 s so Livewire picks up the cancelled state from the server
            setTimeout(() => window.location.reload(), 3000);
            return;
        }

        const h = Math.floor(remaining / 3600);
        const m = Math.floor((remaining % 3600) / 60);
        const s = remaining % 60;
        countdownEl.textContent = pad(h) + ':' + pad(m) + ':' + pad(s);

        // Turn badge orange-red in last 5 minutes
        const countdownBadge = document.getElementById('payment-countdown');
        if (countdownBadge && remaining <= 300) {
            countdownBadge.classList.remove('bg-amber-100', 'border-amber-300', 'text-amber-800');
            countdownBadge.classList.add('bg-rose-100', 'border-rose-300', 'text-rose-800');
        }
    }

    tick();
    const timer = setInterval(tick, 1000);

    // Clean up when Livewire navigates away
    document.addEventListener('livewire:navigating', () => clearInterval(timer));
})();
</script>
@endif

{{-- ============================================================
     REFERENCE NUMBER → DISABLE OTHER BUTTONS SCRIPT
     When the user types a transaction ref number, all navigation
     buttons (except "Upload proof") are disabled so they don't
     accidentally leave the page.
     ============================================================ --}}
@if (!$showThankYou && !$isExpired)
<script>
(function () {
    function applyDisableState(hasRef) {
        const hint = document.getElementById('ref-number-hint');
        if (hint) hint.classList.toggle('hidden', !hasRef);
    }

    function watchInput() {
        const input = document.getElementById('payment-reference-input');
        if (! input) return;

        function onChange() {
            applyDisableState(input.value.trim().length > 0);
        }

        input.addEventListener('input', onChange);
        // Run once on page load in case the field is pre-filled
        onChange();
    }

    // Run after Livewire has rendered
    document.addEventListener('livewire:init', watchInput);
    document.addEventListener('livewire:update', watchInput);
    watchInput();
})();
</script>
@endif
