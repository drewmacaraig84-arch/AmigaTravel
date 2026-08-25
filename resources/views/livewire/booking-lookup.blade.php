<div class="min-h-screen bg-transparent py-8 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-3xl">
        <div class="rounded-[2rem] bg-white/85 backdrop-blur-md shadow-xl ring-1 ring-slate-200 overflow-hidden">
            <div class="px-6 py-8 sm:px-10" style="background: linear-gradient(135deg, #ee018d 0%, #b1015d 100%);">
                <a href="{{ url('/') }}" class="text-white/80 text-sm hover:text-white">← Back to Home</a>
                <h1 class="mt-2 text-2xl sm:text-3xl font-semibold text-white">Check My Booking</h1>
            </div>

            <div class="p-6 sm:p-10 space-y-6">

                @if(! $searched)
                    <div class="text-center py-10">
                        @if($errors->any())
                            <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-rose-100 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 mb-2">Invalid Details</h2>
                            <ul class="text-rose-600 text-sm mb-2 list-disc pl-5 max-w-sm mx-auto text-left">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <p class="text-slate-500 text-sm mt-1">Please check your details and try again from the My Booking menu.</p>
                        @else
                            <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 mb-2">Check My Booking</h2>
                            <p class="text-slate-600">Please use the "My Booking" menu in the top navigation bar to check your booking status.</p>
                        @endif
                    </div>
                @else
                    @if($bookings && $bookings->count() > 1)
                        <div class="space-y-6">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900">Multiple Bookings Found</h2>
                                <p class="text-sm text-slate-600 mt-1">We found {{ $bookings->count() }} bookings matching your email. Select one to view details.</p>
                            </div>
                            
                            <div class="grid gap-4">
                                @foreach($bookings as $b)
                                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:shadow-md hover:border-[#ee018d]">
                                        <div class="flex items-start justify-between flex-wrap gap-4">
                                            <div>
                                                <p class="text-xs font-semibold uppercase tracking-widest text-slate-500 mb-1">Transaction Number</p>
                                                <p class="text-lg font-bold text-slate-900">{{ $b->transaction_number }}</p>
                                                
                                                <div class="mt-3 space-y-1">
                                                    @if($b->accommodations->first())
                                                        @php
                                                            $sched = $b->accommodations->first()->schedule;
                                                        @endphp
                                                        @if($sched)
                                                            <p class="text-sm text-slate-700"><strong>Route:</strong> {{ $sched->ferryRoute->origin }} → {{ $sched->ferryRoute->destination }}</p>
                                                            <p class="text-sm text-slate-700"><strong>Travel Date:</strong> {{ Carbon\Carbon::parse($sched->departure_time)->format('M d, Y') }}</p>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="flex flex-col items-end gap-3">
                                                @php
                                                    $statusColors = [
                                                        'pending' => ['bg' => '#fef3c7', 'text' => '#92400e'],
                                                        'confirmed' => ['bg' => '#dcfce7', 'text' => '#166534'],
                                                        'cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                                                        'operator_cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                                                    ];
                                                    $sStyle = $statusColors[$b->status] ?? $statusColors['pending'];
                                                @endphp
                                                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold" style="background: {{ $sStyle['bg'] }}; color: {{ $sStyle['text'] }};">
                                                    {{ ucfirst(str_replace('_', ' ', $b->status)) }}
                                                </span>
                                                <button wire:click="viewBooking('{{ $b->transaction_number }}')" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">
                                                    View Details
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @elseif($booking)
                        @php
                            $statusColors = [
                                'pending' => ['bg' => '#fef3c7', 'text' => '#92400e'],
                                'confirmed' => ['bg' => '#dcfce7', 'text' => '#166534'],
                                'cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                                'operator_cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                            ];
                            $statusStyle = $statusColors[$booking->status] ?? $statusColors['pending'];
                        @endphp
                        @if($feedback)
                            <div class="rounded-3xl border border-pink-200 bg-pink-50 p-4 text-sm text-pink-700">
                                {{ $feedback }}
                            </div>
                        @endif

                        {{-- ⏱ Policy Reminder Dialog (5-Min Cancel, 24h Refund & Rebook) --}}
                        @if($showCancellationReminder)
                            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
                                <div class="w-full max-w-md rounded-[2rem] bg-white p-7 shadow-2xl ring-1 ring-slate-200">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm font-bold uppercase tracking-widest text-amber-700">Booking Policy Reminder</p>
                                        </div>
                                        <button wire:click="dismissCancellationReminder" type="button" class="rounded-full bg-slate-100 p-2 text-slate-500 transition hover:bg-slate-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <h2 class="mt-4 text-xl font-bold text-slate-900">Your booking has been submitted.</h2>
                                    <p class="mt-1.5 text-xs text-slate-500">
                                        Please take note of our cancellation, refund, and rebooking guidelines:
                                    </p>

                                    <div class="mt-4 space-y-2.5">
                                        <div class="flex items-start gap-3 rounded-2xl bg-amber-50/70 p-3 border border-amber-200/70 text-xs text-amber-900">
                                            <span class="text-base leading-none">⏱</span>
                                            <div>
                                                <strong class="font-semibold text-amber-950">Free Cancellation (5 Minutes):</strong>
                                                <p class="mt-0.5 text-amber-800">You are eligible for a 100% full refund if cancelled within <strong>5 minutes</strong> of booking submission.</p>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-3 rounded-2xl bg-blue-50/70 p-3 border border-blue-200/70 text-xs text-blue-900">
                                            <span class="text-base leading-none">⏳</span>
                                            <div>
                                                <strong class="font-semibold text-blue-950">Refund & Rebooking Cutoff:</strong>
                                                <p class="mt-0.5 text-blue-800">Refund and rebooking requests are strictly accepted up to <strong>24 hours before departure</strong>.</p>
                                            </div>
                                        </div>

                                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-3 border border-slate-200 text-xs text-slate-700">
                                            <span class="text-base leading-none">💳</span>
                                            <div>
                                                <strong class="font-semibold text-slate-900">Disbursement Timeframe:</strong>
                                                <p class="mt-0.5 text-slate-600">Approved refunds are processed and disbursed within <strong>24–48 hours</strong>.</p>
                                            </div>
                                        </div>
                                    </div>

                                    @if($booking->transaction && $booking->transaction->payment_status === 'unpaid')
                                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800 font-medium">
                                        Please complete payment to issue your official tickets.
                                    </div>
                                    @endif

                                    <div class="mt-6 flex justify-end">
                                        <button wire:click="dismissCancellationReminder" type="button" class="w-full sm:w-auto rounded-3xl bg-slate-900 px-8 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 shadow-sm">
                                            OK
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($showCancellationWarning || $showRebookingWarning)
                            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
                                <div class="w-full max-w-xl rounded-[2rem] bg-white p-6 shadow-2xl ring-1 ring-slate-200">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">{{ $showCancellationWarning ? 'Confirm Cancellation' : 'Confirm Rebooking' }}</p>
                                            <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ $showCancellationWarning ? 'Cancel your booking?' : 'Proceed with rebooking?' }}</h2>
                                        </div>
                                        @if($showCancellationWarning)
                                            <button wire:click.prevent="cancelCancellationRequest" type="button" class="rounded-full bg-slate-100 p-2 text-slate-600 transition hover:bg-slate-200">
                                                <span class="sr-only">Close</span>
                                                ×
                                            </button>
                                        @else
                                            <button wire:click.prevent="cancelRebookingWarning" type="button" class="rounded-full bg-slate-100 p-2 text-slate-600 transition hover:bg-slate-200">
                                                <span class="sr-only">Close</span>
                                                ×
                                            </button>
                                        @endif
                                    </div>

                                    <div class="mt-4 space-y-4 text-sm text-slate-700">
                                        @if($showCancellationWarning)
                                            <p>
                                                This booking is eligible for cancellation. 
                                                @if(! $cancellationExpired)
                                                    Confirming will allow you to request a 100% refund.
                                                @else
                                                    Confirming will allow you to request a 50% refund.
                                                @endif
                                            </p>
                                            <div class="rounded-2xl border border-pink-100 bg-pink-50 p-3 text-sm text-pink-700">
                                                @if(! $cancellationExpired)
                                                    Cancellation fee: 0% (₱0.00).
                                                @else
                                                    Cancellation fee: 50% of total price.
                                                @endif
                                            </div>
                                        @else
                                            <ul class="mt-4 list-inside list-disc space-y-2 text-sm text-slate-600">
                                                <li>Please select your preferred new travel date.</li>
                                                <li>Rebooking charges apply and fare difference (if applicable.)</li>
                                            </ul>
                                            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-3 text-sm text-blue-700">
                                                To proceed with rebooking, please select your preferred new travel date and submit your proof of payment for the rebooking fee.
                                            </div>
                                        @endif
                                    </div>

                                    <div class="mt-6 flex flex-wrap gap-3 justify-end">
                                        @if($showCancellationWarning)
                                            <button wire:click.prevent="cancelCancellationRequest" type="button" class="rounded-3xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                                                No, go back
                                            </button>
                                            <button wire:click.prevent="confirmCancellationRequest" type="button" class="rounded-3xl bg-pink-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-pink-700">
                                                Yes, continue
                                            </button>
                                        @else
                                            <button wire:click.prevent="cancelRebookingWarning" type="button" class="rounded-3xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                                                No, go back
                                            </button>
                                            <button wire:click.prevent="confirmRebookingRequest" type="button" class="rounded-3xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                                                Yes, continue
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6 space-y-6">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm text-slate-500">Transaction Number</p>
                                    <p class="text-lg font-semibold text-slate-900">{{ $booking->transaction_number }}</p>
                                </div>
                                <div class="flex gap-2 items-center flex-wrap justify-end">
                                    @if($booking->hasPromoTicket())
                                        <span class="rounded-full bg-amber-400 text-white px-3 py-1.5 text-[10px] font-extrabold shadow-sm uppercase tracking-wider">
                                            PROMO TICKET
                                        </span>
                                    @endif
                                    <span class="rounded-full px-4 py-1.5 text-sm font-semibold" @style(['background' => $statusStyle['bg'], 'color' => $statusStyle['text']])>
                                        {{ in_array($booking->status, ['cancelled', 'operator_cancelled']) && (float) $booking->refund_amount > 0 ? $booking->getRefundStatusLabel() : ($booking->status === 'operator_cancelled' ? 'Cancelled by Operator' : ($booking->status === 'cancelled' ? 'Cancelled' : ucfirst($booking->status))) }}
                                        @if($booking->rebooking_status === 'pending')
                                            (Rebooking Pending)
                                        @endif
                                    </span>
                                </div>
                            </div>

                            @if(in_array($booking->status, ['cancelled', 'operator_cancelled']) && (float) $booking->refund_amount > 0)
                                <div class="rounded-2xl border {{ $booking->isRefundCompleted() ? 'border-emerald-200 bg-emerald-50/70' : 'border-amber-200 bg-amber-50/70' }} p-5 shadow-sm space-y-3">
                                    <div class="flex items-center gap-2.5 {{ $booking->isRefundCompleted() ? 'text-emerald-900' : 'text-amber-900' }} font-bold text-sm">
                                        @if($booking->isRefundCompleted())
                                            <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Refund Successfully Disbursed
                                        @else
                                            <svg class="h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Refund Request In Progress (24–48 Hours)
                                        @endif
                                    </div>
                                    <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                                        {{ $booking->getRefundMessage() }}
                                    </p>
                                    <div class="flex flex-wrap gap-2.5 pt-1">
                                        <a href="{{ route('ticket.refund-acknowledgement', $booking->transaction_number) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-2xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-slate-800 transition">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                            Download Refund Acknowledgement
                                        </a>
                                        @if(filled($booking->refund_proof))
                                            <a href="{{ storage_asset_path($booking->refund_proof) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-2xl border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition">
                                                <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                View Proof of Refund
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if($booking->service_cancellation_id || $booking->status === 'operator_cancelled')
                                <div class="rounded-2xl border border-amber-300 bg-amber-50 p-5 shadow-sm space-y-3">
                                    <div class="flex items-center gap-2 text-amber-900 font-bold">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Schedule Disrupted — Choose New Travel Date
                                    </div>
                                    <p class="text-xs text-amber-800 font-medium">Your trip was impacted by an operator cancellation. You can choose a new travel date at ₱0 fee.</p>
                                    <a href="{{ route('booking.reschedule', $booking->transaction_number) }}" class="inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-amber-700 transition">
                                        Choose New Travel Date &rarr;
                                    </a>
                                </div>
                            @endif

                            @php
                                $slaNote = $booking->getSlaVoucherNote();
                            @endphp
                            @if($slaNote && in_array($booking->status, ['pending', \App\Models\Booking::STATUS_PENDING_REBOOKING]))
                                <div class="rounded-2xl border border-pink-200 bg-pink-50/70 p-4 sm:p-5 shadow-sm space-y-2">
                                    <div class="flex items-center gap-2.5 text-[#ee018d] font-bold text-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0 text-[#ee018d]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Our Verification Guarantee
                                    </div>
                                    <p class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                                        We are reviewing your booking. As part of our service guarantee, if your booking is not processed within <strong>{{ \App\Models\PaymentSetting::current()->getSlaVoucherHours() }} hours</strong> of payment submission, you will automatically receive a <strong>₱{{ number_format(\App\Models\PaymentSetting::current()->getSlaVoucherAmount(), 0) }} travel voucher valid for 90 days</strong> towards your next trip.
                                    </p>
                                    <div class="flex items-center gap-2 pt-1 text-xs text-pink-700 font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <span>Download the <strong>Amiga Gracia App</strong> to easily claim, track, and redeem your reward vouchers!</span>
                                    </div>
                                </div>
                            @endif

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl bg-white p-4 border border-slate-200">
                                    <p class="text-sm text-slate-500">Route</p>
                                    <p class="font-medium text-slate-900">{{ $booking->origin }} → {{ $booking->destination }}</p>
                                </div>
                                <div class="rounded-2xl bg-white p-4 border border-slate-200">
                                    <p class="text-sm text-slate-500">Operator</p>
                                    <p class="font-medium text-slate-900">{{ $booking->getOperatorName() ?: '—' }}</p>
                                </div>
                                <div class="rounded-2xl bg-white p-4 border border-slate-200">
                                    <p class="text-sm text-slate-500">Travel Dates</p>
                                    <p class="font-medium text-slate-900">{{ $booking->departure_date->format('M d, Y') }}{{ $booking->return_date ? ' → ' . $booking->return_date->format('M d, Y') : ' (One-way)' }}</p>
                                </div>
                                <div class="rounded-2xl bg-white p-4 border border-slate-200">
                                    <p class="text-sm text-slate-500">Schedule</p>
                                    @if($booking->schedule_departure_time || $booking->schedule_service)
                                        <p class="font-medium text-slate-900">
                                            {{ $booking->schedule_service }}
                                            @if($booking->schedule_departure_time && $booking->schedule_arrival_time)
                                                ({{ $booking->schedule_departure_time }} → {{ $booking->schedule_arrival_time }})
                                            @elseif($booking->schedule_departure_time)
                                                ({{ $booking->schedule_departure_time }})
                                            @endif
                                        </p>
                                        @if($booking->return_date && ($booking->return_schedule_departure_time || $booking->return_schedule_service))
                                            <p class="text-sm text-slate-600 mt-1">
                                                Return: {{ $booking->return_schedule_service }}
                                                @if($booking->return_schedule_departure_time && $booking->return_schedule_arrival_time)
                                                    ({{ $booking->return_schedule_departure_time }} → {{ $booking->return_schedule_arrival_time }})
                                                @elseif($booking->return_schedule_departure_time)
                                                    ({{ $booking->return_schedule_departure_time }})
                                                @endif
                                            </p>
                                        @endif
                                    @else
                                        <p class="font-medium text-slate-900">Not recorded</p>
                                    @endif
                                    @if($booking->schedule_price)
                                        <p class="text-sm text-slate-600 mt-1">₱{{ number_format($booking->schedule_price, 2) }} per passenger{{ $booking->return_date ? ' (round trip)' : '' }}</p>
                                    @endif
                                </div>
                                <div class="rounded-2xl bg-white p-4 border border-slate-200">
                                    <p class="text-sm text-slate-500">Booked by</p>
                                    <p class="font-medium text-slate-900">{{ $booking->client_name }}</p>
                                </div>
                                <div class="rounded-2xl bg-white p-4 border border-slate-200">
                                    <p class="text-sm text-slate-500">Payment Status</p>
                                    <p class="font-medium text-slate-900">{{ $booking->transaction ? ucfirst($booking->transaction->payment_status) : 'N/A' }}</p>
                                </div>
                            </div>

                            @php
                                $activePax = $booking->getActivePassengers();
                                $refundedPax = $booking->getRefundedPassengers();
                                $rebookedPax = $booking->getRebookedHistoryPassengers();
                            @endphp

                            {{-- 🟢 SECTION 1: ACTIVE PASSENGERS & BOARDING PASSES --}}
                            <div>
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                    <div>
                                        <h3 class="font-semibold text-slate-900 text-base flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                                            Active Travel Itinerary & Boarding Passes
                                        </h3>
                                        <p class="text-xs text-slate-500">Active passengers travelling on this schedule</p>
                                    </div>
                                    @if($activePax->isNotEmpty())
                                        <div class="flex items-center gap-2">
                                            <button type="button" wire:click="selectAllPassengers" class="text-xs font-semibold text-pink-600 hover:text-pink-700 bg-pink-50 hover:bg-pink-100 px-3 py-1.5 rounded-lg border border-pink-200 transition">
                                                Select All
                                            </button>
                                            <button type="button" wire:click="deselectAllPassengers" class="text-xs font-semibold text-slate-600 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg border border-slate-200 transition">
                                                Deselect
                                            </button>
                                        </div>
                                    @endif
                                </div>

                                @if($activePax->isEmpty())
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 text-center text-sm text-slate-500">
                                        All passenger items on this booking have been refunded or rescheduled.
                                    </div>
                                @else
                                    <div class="space-y-3">
                                        @foreach($activePax->sortBy('item_number') as $passenger)
                                            @php
                                                $pItemNum = (int) ($passenger->item_number ?? 1);
                                                $pStatus = $passenger->status ?? 'pending';
                                                $isActionLocked = ! $passenger->isActiveBookingItem();
                                                $isSelected = in_array($pItemNum, array_map('intval', $selectedPassengerItems), true) && ! $isActionLocked;
                                                $pStatusLabel = $passenger->getStatusLabel();
                                                $itemStatusConfig = [
                                                    'pending'            => ['bg' => '#fef3c7', 'text' => '#92400e', 'border' => '#fde68a'],
                                                    'confirmed'          => ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#86efac'],
                                                    'operator_rebooking' => ['bg' => '#e0e7ff', 'text' => '#3730a3', 'border' => '#a5b4fc'],
                                                ];
                                                $pStyle = $itemStatusConfig[$pStatus] ?? ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#86efac'];
                                                $fareAndClass = $passenger->getEffectiveFareAndClass();
                                                $webFee = $passenger->getEffectiveWebAdminFee();
                                                $txFee = $passenger->getEffectiveTransactionFee();
                                                $itemTotal = $passenger->getEffectiveItemTotal();
                                            @endphp
                                            <div x-data="{ expanded: false }" class="rounded-2xl border transition-all duration-200 {{ $isSelected ? 'ring-2 ring-pink-500 shadow-sm' : '' }}" style="background: {{ $pStyle['bg'] }}18; border-color: {{ $isSelected ? '#ec4899' : $pStyle['border'] }};">
                                                {{-- Header row --}}
                                                <div class="p-3.5 sm:p-4 flex items-center justify-between gap-2 border-b border-black/5 cursor-pointer" @click="expanded = !expanded">
                                                    <div class="flex items-center gap-3 min-w-0">
                                                        {{-- Checkbox --}}
                                                        <div @click.stop class="flex items-center">
                                                            <input type="checkbox"
                                                                wire:model.live="selectedPassengerItems"
                                                                value="{{ $pItemNum }}"
                                                                id="pax_item_{{ $pItemNum }}"
                                                                @if($isActionLocked) disabled @endif
                                                                class="w-4 h-4 rounded text-pink-600 focus:ring-pink-500 border-slate-300 {{ $isActionLocked ? 'opacity-35 cursor-not-allowed bg-slate-100' : 'cursor-pointer' }}">
                                                        </div>
                                                        {{-- Item Number badge --}}
                                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-900 text-white text-xs font-bold shrink-0">
                                                            {{ $pItemNum }}
                                                        </span>
                                                        {{-- Name and Ticket # --}}
                                                        <div class="min-w-0">
                                                            <div class="flex items-center gap-2">
                                                                <label for="{{ $isActionLocked ? '' : 'pax_item_' . $pItemNum }}" @click.stop class="font-semibold text-slate-900 cursor-pointer hover:text-pink-600 text-sm leading-tight truncate">
                                                                    {{ $passenger->name ?? 'Passenger' }}
                                                                </label>
                                                                <span class="text-[11px] font-medium px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                                                    {{ ucfirst($passenger->type ?? 'adult') }}
                                                                </span>
                                                            </div>
                                                            @if($passenger->ticket_number)
                                                                <p class="text-xs text-slate-400 font-mono leading-tight mt-0.5">{{ $passenger->ticket_number }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold" style="background: {{ $pStyle['bg'] }}; color: {{ $pStyle['text'] }}; border: 1px solid {{ $pStyle['border'] }};">
                                                            {{ $pStatusLabel }}
                                                        </span>
                                                        <span class="text-xs font-bold text-slate-900 hidden sm:inline-block">
                                                            ₱{{ number_format($itemTotal, 2) }}
                                                        </span>
                                                        <button type="button" @click.stop="expanded = !expanded" class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-black/5 transition">
                                                            <svg class="w-4 h-4 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- Collapsible Body --}}
                                                <div x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-3.5 sm:p-4 pt-3 space-y-3">
                                                    {{-- Details row --}}
                                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600 bg-white/50 p-2.5 rounded-xl border border-black/5">
                                                        <span><span class="font-medium text-slate-500">Type:</span> {{ ucfirst($passenger->type ?? 'adult') }}</span>
                                                        @if($passenger->birthdate)
                                                            <span><span class="font-medium text-slate-500">Bday:</span> {{ $passenger->birthdate->format('Y-m-d') }}</span>
                                                        @endif
                                                        @if($passenger->discount?->name)
                                                            <span><span class="font-medium text-slate-500">Discount:</span> {{ $passenger->discount->name }}</span>
                                                        @endif
                                                        @if($passenger->id_number)
                                                            <span><span class="font-medium text-slate-500">ID#:</span> {{ $passenger->id_number }}</span>
                                                        @endif
                                                        @if($passenger->hasPassportInfo())
                                                            <span class="inline-flex items-center gap-1 font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                                                <span>🛂 Passport:</span>
                                                                <span class="font-mono">{{ $passenger->passport_number }}</span>
                                                            </span>
                                                        @endif
                                                        @if($passenger->hasExtraBaggage())
                                                            <span class="inline-flex items-center gap-1 font-semibold text-sky-700 bg-sky-50 px-2 py-0.5 rounded-md border border-sky-200">
                                                                <span>🧳 Baggage:</span>
                                                                <span>{{ $passenger->extra_baggage_weight }}</span>
                                                                <span class="font-normal text-sky-600">(+₱{{ number_format($passenger->extra_baggage_price, 2) }})</span>
                                                            </span>
                                                        @endif
                                                    </div>

                                                    {{-- Individual Financial breakdown --}}
                                                    <div class="rounded-xl bg-white/90 border border-slate-200 px-3.5 py-2.5 space-y-1 shadow-sm">
                                                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Item Price Breakdown</p>
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 text-xs text-slate-600">
                                                            <div class="flex justify-between"><span class="text-slate-500">Ticket & Transport Class</span><span class="font-medium">₱{{ number_format($fareAndClass, 2) }}</span></div>
                                                            @if($passenger->discount_amount > 0)
                                                                <div class="flex justify-between"><span class="text-slate-500">Discount ({{ $passenger->discount?->name ?? 'Discount' }})</span><span class="text-green-600 font-medium">-₱{{ number_format($passenger->discount_amount, 2) }}</span></div>
                                                            @endif
                                                            @if($passenger->voucher_discount_share > 0)
                                                                <div class="flex justify-between"><span class="text-slate-500">Voucher Share</span><span class="text-green-600 font-medium">-₱{{ number_format($passenger->voucher_discount_share, 2) }}</span></div>
                                                            @endif
                                                            @if($passenger->points_discount_share > 0)
                                                                <div class="flex justify-between"><span class="text-slate-500">Gracia Points</span><span class="text-green-600 font-medium">-₱{{ number_format($passenger->points_discount_share, 2) }}</span></div>
                                                            @endif
                                                            @if($webFee > 0)
                                                                <div class="flex justify-between"><span class="text-slate-500">Web Admin Fee</span><span class="font-medium text-slate-600">₱{{ number_format($webFee, 2) }}</span></div>
                                                            @endif
                                                            @if($txFee > 0)
                                                                <div class="flex justify-between"><span class="text-slate-500">Transaction Fee</span><span class="font-medium text-slate-600">₱{{ number_format($txFee, 2) }}</span></div>
                                                            @endif
                                                        </div>
                                                        <div class="flex justify-between font-bold text-slate-900 text-xs pt-1.5 mt-1 border-t border-slate-100">
                                                            <span>Item Total</span>
                                                            <span style="color:#ee018d;" class="text-sm">₱{{ number_format($itemTotal, 2) }}</span>
                                                        </div>
                                                    </div>

                                                    {{-- Action buttons for this active passenger item --}}
                                                    @if(in_array($passenger->status, ['rebooked', 'confirmed']) && ($booking->status === 'confirmed' || $passenger->is_rebooked))
                                                        <div class="flex items-center justify-end pt-1">
                                                            <a href="{{ route('ticket.passenger-pdf', $passenger->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-white shadow-sm transition">
                                                                <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                                Download Ticket (Item {{ $pItemNum }})
                                                            </a>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- 💰 SECTION 2: CANCELLED & REFUNDED ITEMS (IF ANY) --}}
                            @if($refundedPax->isNotEmpty())
                                <div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-4 sm:p-5 space-y-3">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <h3 class="font-semibold text-slate-900 text-base flex items-center gap-2">
                                            <span class="text-amber-600">💰</span> Cancelled & Refunded Items
                                        </h3>
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 border border-amber-300">
                                            {{ $refundedPax->count() }} item(s) refunded
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600">These passenger items were cancelled and are no longer valid for travel.</p>
                                    <div class="space-y-2">
                                        @foreach($refundedPax->sortBy('item_number') as $rp)
                                            <div class="rounded-xl bg-white p-3 border border-amber-200/80 flex items-center justify-between flex-wrap gap-3">
                                                <div class="flex items-center gap-3">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-900 text-xs font-bold shrink-0 border border-amber-300">
                                                        {{ $rp->item_number }}
                                                    </span>
                                                    <div>
                                                        <p class="font-semibold text-slate-900 text-sm">{{ $rp->name ?? 'Passenger' }} <span class="text-xs text-slate-500 font-normal">({{ ucfirst($rp->type ?? 'adult') }})</span></p>
                                                        <p class="text-xs text-slate-500">
                                                            Refund: <strong class="text-amber-700">₱{{ number_format((float) $rp->refund_amount, 2) }}</strong>
                                                            @if($rp->refund_destination) &bull; Sent to: {{ $rp->refund_destination }} @endif
                                                            @if($rp->refund_reference) &bull; Ref: <strong class="text-slate-800">{{ $rp->refund_reference }}</strong> @endif
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $rp->refund_status === 'completed' ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                                                        {{ $rp->refund_status === 'completed' ? 'Refund Disbursed' : 'Refund Pending' }}
                                                    </span>
                                                    @if($booking->transaction_number)
                                                        <a href="{{ route('ticket.refund-acknowledgement', $booking->transaction_number) }}" target="_blank" class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-slate-800 text-white transition">
                                                            Receipt
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- 🔄 SECTION 3: RESCHEDULE / REBOOKING HISTORY (IF ANY) --}}
                            @if($rebookedPax->isNotEmpty())
                                <div class="rounded-2xl border border-purple-200 bg-purple-50/40 p-4 sm:p-5 space-y-3">
                                    <div class="flex items-center justify-between flex-wrap gap-2">
                                        <h3 class="font-semibold text-slate-900 text-base flex items-center gap-2">
                                            <span class="text-purple-600">🔄</span> Rebooking & Reschedule History
                                        </h3>
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-purple-100 text-purple-800 border border-purple-300">
                                            {{ $rebookedPax->count() }} item(s) replaced
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600">Original schedule tickets that have been rescheduled to a new itinerary.</p>
                                    <div class="space-y-2">
                                        @foreach($rebookedPax->sortBy('item_number') as $reb)
                                            <div class="rounded-xl bg-white p-3 border border-purple-200/80 flex items-center justify-between flex-wrap gap-3">
                                                <div class="flex items-center gap-3">
                                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-purple-100 text-purple-900 text-xs font-bold shrink-0 border border-purple-300">
                                                        {{ $reb->item_number }}
                                                    </span>
                                                    <div>
                                                        <p class="font-semibold text-slate-900 text-sm">{{ $reb->name ?? 'Passenger' }} <span class="text-xs text-slate-500 font-normal">({{ ucfirst($reb->type ?? 'adult') }})</span></p>
                                                        <p class="text-xs text-purple-700 font-medium">Original ticket replaced & rescheduled to active journey</p>
                                                    </div>
                                                </div>
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-300">
                                                    Rescheduled
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($booking->accommodations->isNotEmpty())
                                <div>
                                    <h3 class="font-semibold text-slate-900 mb-3">Accommodations</h3>
                                    <div class="space-y-2">
                                        @foreach($booking->accommodations as $accommodation)
                                            <div class="rounded-2xl bg-white p-4 border border-slate-200 flex items-center justify-between">
                                                <span class="text-slate-800">{{ $accommodation->name }}</span>
                                                <span class="text-sm text-slate-600">₱{{ number_format($accommodation->pivot->price, 2) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            {{-- Price Breakdown --}}
                            <div class="rounded-2xl bg-white p-4 border border-slate-200 mt-4">
                                <h3 class="font-semibold text-slate-900 mb-3">Price Breakdown</h3>
                                <div class="space-y-2 text-sm text-slate-600">
                                    @foreach($this->priceBreakdown as $item)
                                        <div class="flex justify-between {{ $item['class'] }}">
                                            <span>{{ $item['label'] }}</span>
                                            <span>{{ $item['amount'] < 0 ? '-' : '' }}₱{{ number_format(abs($item['amount']), 2) }}</span>
                                        </div>
                                    @endforeach
                                    <div class="pt-2 mt-2 border-t border-slate-100 flex items-center justify-between font-semibold text-slate-900 text-base">
                                        <span>Total Price</span>
                                        <span style="color:#216417;">₱{{ number_format($booking->total_price + ($booking->transaction->rebooking_fee ?? 0), 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="flex flex-wrap gap-3">
                                    @if(!in_array($booking->status, ['cancelled', 'operator_cancelled']))
                                        {{-- Done button only for unpaid pending bookings --}}
                                        @if($booking->transaction && in_array($booking->transaction->payment_status, ['pending', 'unpaid'], true) && $booking->status === 'pending')
                                            <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-3xl px-6 py-3 text-sm font-semibold text-white shadow-sm transition" style="background:#ee018d;" onmouseover="this.style.background='#c30172'" onmouseout="this.style.background='#ee018d'">
                                                Done
                                            </a>
                                        @endif

                                        @if($booking->canCancelOrRebook())
                                            @if(! $cancellationRequested && ! $rebookingRequested)
                                                @if(! $cancellationExpired)
                                                    <button wire:click.prevent="requestCancellation" type="button" class="inline-flex items-center justify-center rounded-3xl border border-pink-500 px-6 py-3 text-sm font-semibold text-pink-700 transition hover:bg-pink-50">
                                                        Cancel Booking
                                                    </button>
                                                @else
                                                    <button type="button" disabled class="inline-flex items-center justify-center rounded-3xl border border-slate-300 bg-slate-100 px-6 py-3 text-sm font-semibold text-slate-400">
                                                        Cancel Booking
                                                    </button>
                                                    @if($booking->isRefundEligible())
                                                        <button wire:click.prevent="requestCancellation" type="button" class="inline-flex items-center justify-center rounded-3xl border border-rose-500 px-6 py-3 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">
                                                            Request Refund
                                                        </button>
                                                    @else
                                                        <button type="button" disabled class="inline-flex items-center justify-center rounded-3xl border border-slate-300 bg-slate-100 px-6 py-3 text-sm font-semibold text-slate-400">
                                                            Request Refund
                                                        </button>
                                                    @endif
                                                @endif
                                                @if(!$booking->hasBeenRebooked() && empty($booking->rebooking_status))
                                                    @if($booking->transaction && $booking->transaction->payment_status === 'paid')
                                                        <button wire:click.prevent="requestRebooking" type="button" class="inline-flex items-center justify-center rounded-3xl border border-blue-500 px-6 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">
                                                            Rebook
                                                        </button>
                                                    @else
                                                        <button type="button" disabled class="inline-flex items-center justify-center rounded-3xl border border-slate-300 bg-slate-100 px-6 py-3 text-sm font-semibold text-slate-400" title="Booking must be verified and paid to rebook.">
                                                            Rebook
                                                        </button>
                                                    @endif
                                                @endif
                                            @endif
                                        @else
                                            <div class="space-y-2">
                                                <button type="button" disabled class="inline-flex items-center justify-center rounded-3xl border border-slate-200 bg-slate-100 px-6 py-3 text-sm font-semibold text-slate-500 shadow-sm">
                                                    Actions Unavailable
                                                </button>
                                                <p class="text-xs text-slate-500">You cannot cancel or rebook this booking as the departure date has passed.</p>
                                            </div>
                                        @endif
                                    @endif
                                </div>

                                @if($cancellationRequested)
                                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4" @if(! $cancellationExpired) wire:poll.1s="tickCancelCountdown" @endif>
                                        <div class="flex flex-col gap-2 border-b border-amber-200/60 pb-3 mb-3">
                                            <div class="flex items-center justify-between gap-2">
                                                <div>
                                                    <p class="text-sm font-bold text-amber-800">
                                                        @if(! $cancellationExpired)
                                                            100% Refund Window Active
                                                        @else
                                                            Refund Available
                                                        @endif
                                                    </p>
                                                    <p class="mt-1 text-xs text-amber-700">
                                                        @if(! $cancellationExpired)
                                                            Confirm cancellation to receive a 100% refund. Cancellation is free within 5 minutes of booking.
                                                        @else
                                                            The 100% refund window has expired. See the breakdown of your refund below.
                                                        @endif
                                                    </p>
                                                </div>
                                                @if(! $cancellationExpired)
                                                    <span class="rounded-full bg-white px-3 py-1 text-sm font-semibold text-amber-700 shrink-0">
                                                        {{ gmdate('i:s', max(0, $cancelCountdown)) }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            @php
                                                $selectedItemsForRefund = ! empty($selectedPassengerItems) ? $selectedPassengerItems : $booking->passengers->pluck('item_number')->toArray();
                                                $refundBreakdown = $booking->getPartialRefundBreakdown($selectedItemsForRefund, ! $cancellationExpired);
                                                $selectedRefundLabel = $booking->getAffectedItemsLabel($selectedItemsForRefund);
                                            @endphp
                                            <div class="mt-2 p-3 bg-white/70 rounded-xl space-y-1 text-sm text-amber-900 border border-amber-100/50">
                                                <div class="flex items-center justify-between pb-1 mb-1 border-b border-amber-200/50">
                                                    <span class="text-xs font-bold uppercase tracking-wider text-amber-800">Affected Passengers:</span>
                                                    <span class="font-semibold text-xs px-2 py-0.5 rounded bg-amber-100 text-amber-900">{{ $selectedRefundLabel }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Selected Items Base Total:</span>
                                                    <span>₱{{ number_format($refundBreakdown['base_ticket'], 2) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span>Surcharge ({{ $refundBreakdown['surcharge_pct'] }}%):</span>
                                                    <span>-₱{{ number_format($refundBreakdown['surcharge_amount'], 2) }}</span>
                                                </div>
                                                <div class="flex justify-between">
                                                    <span class="font-semibold text-slate-800">Non-Refundable Fees</span>
                                                    <span>-₱{{ number_format($refundBreakdown['non_refundable_fees'], 2) }}</span>
                                                </div>
                                                <div class="pl-4 space-y-1 text-xs text-amber-800/80">
                                                    <div class="flex justify-between">
                                                        <span>Web Admin Fee</span>
                                                        <span>₱{{ number_format($refundBreakdown['web_admin_fee'], 2) }}</span>
                                                    </div>
                                                    <div class="flex justify-between">
                                                        <span>Transaction Fee</span>
                                                        <span>₱{{ number_format($refundBreakdown['transaction_fee'], 2) }}</span>
                                                    </div>
                                                    @if(($refundBreakdown['rebooking_revalidation_fee'] ?? 0) > 0)
                                                        <div class="flex justify-between">
                                                            <span>Revalidation Fee</span>
                                                            <span>₱{{ number_format($refundBreakdown['rebooking_revalidation_fee'], 2) }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex justify-between pt-1.5 mt-1 border-t border-amber-200/50 font-bold text-base">
                                                    <span>Estimated Refund:</span>
                                                    <span class="text-green-700">₱{{ number_format($refundBreakdown['refundable_amount'], 2) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        </div>

                                        {{-- Always show editable refund input fields --}}
                                        <div class="mt-3 space-y-3">
                                            @if(filled($refund_destination) && blank($refund_account_number))
                                                <div class="rounded-xl bg-white border border-amber-200 px-4 py-3">
                                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Previous Designated Destination</p>
                                                    <p class="text-sm text-slate-800 font-medium">{{ $refund_destination }}</p>
                                                    <p class="text-[11px] text-slate-400 mt-0.5">You may update the details below:</p>
                                                </div>
                                            @endif

                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-slate-700">Refund Method</label>
                                                <select wire:model.live="refund_method" class="block w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2" style="--tw-ring-color:#ee018d;">
                                                    <option value="GCash">GCash</option>
                                                    <option value="Online Wallet">Online Wallet (e.g. Maya)</option>
                                                    <option value="Bank Account">Bank Account</option>
                                                </select>
                                                @error('refund_method')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                                            </div>

                                            {{-- Institution (shown for Bank Account & Online Wallet) --}}
                                            @if(in_array($refund_method, ['Bank Account', 'Online Wallet']))
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-slate-700">
                                                    {{ $refund_method === 'Bank Account' ? 'Bank Name' : 'Wallet Provider' }}
                                                </label>
                                                <input type="text" wire:model.defer="refund_bank_name"
                                                    placeholder="{{ $refund_method === 'Bank Account' ? 'e.g. BDO, BPI, Metrobank' : 'e.g. Maya, PayMaya, GrabPay' }}"
                                                    class="block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2" style="--tw-ring-color:#ee018d;" />
                                                @error('refund_bank_name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                                            </div>
                                            @endif

                                            {{-- Account / Number --}}
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-slate-700">
                                                    {{ $refund_method === 'GCash' ? 'GCash Mobile Number' : 'Account Number' }}
                                                </label>
                                                <input type="text" wire:model.defer="refund_account_number"
                                                    placeholder="{{ $refund_method === 'GCash' ? 'e.g. 0917xxxxxxx' : 'e.g. 1234-5678-9012' }}"
                                                    class="block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2" style="--tw-ring-color:#ee018d;" />
                                                @error('refund_account_number')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                                            </div>

                                            {{-- Account Name --}}
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-slate-700">Account Name</label>
                                                <input type="text" wire:model.defer="refund_account_name"
                                                    placeholder="Full name registered on the account"
                                                    class="block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2" style="--tw-ring-color:#ee018d;" />
                                                @error('refund_account_name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                                            </div>

                                            {{-- Verification Documents Upload --}}
                                            <div class="col-span-full border-t border-slate-200/80 pt-4 mt-2">
                                                <p class="text-sm font-bold text-slate-800 flex items-center gap-1.5 mb-1">
                                                    <svg class="w-4 h-4 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                    Verification &amp; Authorization Documents
                                                </p>
                                                <p class="text-xs text-slate-500 mb-3">Please upload the required verification documents to help our finance team verify and disburse your refund quickly.</p>

                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                    {{-- Valid ID Upload --}}
                                                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:border-pink-300 transition">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1">
                                                                <span>1. Valid ID (Photo/PDF)</span>
                                                            </label>
                                                            @if($refund_id_image)
                                                                <button type="button" wire:click="$set('refund_id_image', null)" class="text-xs font-semibold text-rose-600 hover:underline">Remove</button>
                                                            @endif
                                                        </div>

                                                        @if($refund_id_image)
                                                            @php
                                                                $isIdPdf = in_array(strtolower(pathinfo($refund_id_image->getClientOriginalName(), PATHINFO_EXTENSION)), ['pdf']);
                                                            @endphp
                                                            <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-2 text-center">
                                                                @if(!$isIdPdf && method_exists($refund_id_image, 'temporaryUrl'))
                                                                    <img src="{{ $refund_id_image->temporaryUrl() }}" class="mx-auto max-h-28 rounded-lg object-contain shadow-sm" alt="Valid ID Preview" />
                                                                @else
                                                                    <div class="py-3 flex flex-col items-center justify-center text-slate-600">
                                                                        <svg class="w-8 h-8 text-pink-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                                                        <span class="text-[11px] font-semibold truncate max-w-full px-1">{{ $refund_id_image->getClientOriginalName() }}</span>
                                                                    </div>
                                                                @endif
                                                                <p class="mt-1 text-[11px] text-emerald-600 font-medium flex items-center justify-center gap-1">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                                    ID attached
                                                                </p>
                                                            </div>
                                                        @else
                                                            <label class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-3.5 text-center cursor-pointer hover:bg-pink-50/30 hover:border-pink-300 transition">
                                                                <svg class="w-7 h-7 text-slate-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                                <span class="text-xs font-semibold text-slate-600">Upload Valid ID</span>
                                                                <span class="text-[10px] text-slate-400 mt-0.5">JPG, PNG, PDF (Max 10MB)</span>
                                                                <input type="file" wire:model="refund_id_image" accept="image/*,application/pdf" class="hidden" />
                                                            </label>
                                                        @endif
                                                        <div wire:loading wire:target="refund_id_image" class="text-xs text-pink-600 mt-1 font-medium">Uploading ID...</div>
                                                        @error('refund_id_image')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                                    </div>

                                                    {{-- Original Ticket Upload --}}
                                                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:border-pink-300 transition">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1">
                                                                <span>2. Original Ticket</span>
                                                            </label>
                                                            @if($refund_ticket_file)
                                                                <button type="button" wire:click="$set('refund_ticket_file', null)" class="text-xs font-semibold text-rose-600 hover:underline">Remove</button>
                                                            @endif
                                                        </div>

                                                        @if($refund_ticket_file)
                                                            @php
                                                                $isTicketPdf = in_array(strtolower(pathinfo($refund_ticket_file->getClientOriginalName(), PATHINFO_EXTENSION)), ['pdf']);
                                                            @endphp
                                                            <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-2 text-center">
                                                                @if(!$isTicketPdf && method_exists($refund_ticket_file, 'temporaryUrl'))
                                                                    <img src="{{ $refund_ticket_file->temporaryUrl() }}" class="mx-auto max-h-28 rounded-lg object-contain shadow-sm" alt="Original Ticket Preview" />
                                                                @else
                                                                    <div class="py-3 flex flex-col items-center justify-center text-slate-600">
                                                                        <svg class="w-8 h-8 text-pink-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                                                                        <span class="text-[11px] font-semibold truncate max-w-full px-1">{{ $refund_ticket_file->getClientOriginalName() }}</span>
                                                                    </div>
                                                                @endif
                                                                <p class="mt-1 text-[11px] text-emerald-600 font-medium flex items-center justify-center gap-1">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                                    Ticket attached
                                                                </p>
                                                            </div>
                                                        @else
                                                            <label class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-3.5 text-center cursor-pointer hover:bg-pink-50/30 hover:border-pink-300 transition">
                                                                <svg class="w-7 h-7 text-slate-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                                                                <span class="text-xs font-semibold text-slate-600">Upload Ticket/Receipt</span>
                                                                <span class="text-[10px] text-slate-400 mt-0.5">PDF or Image (Max 10MB)</span>
                                                                <input type="file" wire:model="refund_ticket_file" accept="image/*,application/pdf" class="hidden" />
                                                            </label>
                                                        @endif
                                                        <div wire:loading wire:target="refund_ticket_file" class="text-xs text-pink-600 mt-1 font-medium">Uploading ticket...</div>
                                                        @error('refund_ticket_file')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                                    </div>

                                                    {{-- Authorization Letter Upload --}}
                                                    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm hover:border-pink-300 transition">
                                                        <div class="flex items-center justify-between mb-2">
                                                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1">
                                                                <span>3. Authorization Letter</span>
                                                            </label>
                                                            @if($refund_auth_letter)
                                                                <button type="button" wire:click="$set('refund_auth_letter', null)" class="text-xs font-semibold text-rose-600 hover:underline">Remove</button>
                                                            @endif
                                                        </div>

                                                        @if($refund_auth_letter)
                                                            @php
                                                                $isAuthPdf = in_array(strtolower(pathinfo($refund_auth_letter->getClientOriginalName(), PATHINFO_EXTENSION)), ['pdf']);
                                                            @endphp
                                                            <div class="relative overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-2 text-center">
                                                                @if(!$isAuthPdf && method_exists($refund_auth_letter, 'temporaryUrl'))
                                                                    <img src="{{ $refund_auth_letter->temporaryUrl() }}" class="mx-auto max-h-28 rounded-lg object-contain shadow-sm" alt="Authorization Letter Preview" />
                                                                @else
                                                                    <div class="py-3 flex flex-col items-center justify-center text-slate-600">
                                                                        <svg class="w-8 h-8 text-pink-500 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                        <span class="text-[11px] font-semibold truncate max-w-full px-1">{{ $refund_auth_letter->getClientOriginalName() }}</span>
                                                                    </div>
                                                                @endif
                                                                <p class="mt-1 text-[11px] text-emerald-600 font-medium flex items-center justify-center gap-1">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                                    Authorization attached
                                                                </p>
                                                            </div>
                                                        @else
                                                            <label class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-3.5 text-center cursor-pointer hover:bg-pink-50/30 hover:border-pink-300 transition">
                                                                <svg class="w-7 h-7 text-slate-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                <span class="text-xs font-semibold text-slate-600">Upload Auth Letter</span>
                                                                <span class="text-[10px] text-slate-400 mt-0.5">Required for representative / permit refund</span>
                                                                <input type="file" wire:model="refund_auth_letter" accept="image/*,application/pdf" class="hidden" />
                                                            </label>
                                                        @endif
                                                        <div wire:loading wire:target="refund_auth_letter" class="text-xs text-pink-600 mt-1 font-medium">Uploading auth letter...</div>
                                                        @error('refund_auth_letter')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex flex-wrap gap-3" x-data="{ showRefundModal: false }">
                                            <button @click="showRefundModal = true" type="button" class="inline-flex items-center justify-center rounded-3xl px-6 py-3 text-sm font-semibold text-white shadow-sm transition" style="background:#ee018d;" onmouseover="this.style.background='#c30172'" onmouseout="this.style.background='#ee018d'">
                                                @if(! $cancellationExpired)
                                                    Confirm Cancellation (100% Refund)
                                                @else
                                                    Confirm Refund
                                                @endif
                                            </button>
                                            <button wire:click.prevent="cancelCancellationRequest" type="button" class="inline-flex items-center justify-center rounded-3xl border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                                                Cancel Request
                                            </button>
                                            @if(!$booking->hasBeenRebooked() && empty($booking->rebooking_status))
                                                @if($booking->transaction && $booking->transaction->payment_status === 'paid')
                                                    <button wire:click.prevent="confirmRebookingRequest" type="button" class="inline-flex items-center justify-center rounded-3xl border border-blue-500 px-6 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">
                                                        Switch to Rebook
                                                    </button>
                                                @endif
                                            @endif

                                            <!-- Alpine.js Refund Modal -->
                                            <div x-cloak x-show="showRefundModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm px-4" x-transition.opacity>
                                                <div @click.away="showRefundModal = false" class="w-full max-w-sm rounded-3xl bg-white p-6 shadow-2xl transform" x-transition.scale.origin.bottom>
                                                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                                                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    </div>
                                                    <h3 class="text-center text-lg font-bold text-slate-900">Refund Processing</h3>
                                                    <p class="mt-2 text-center text-sm text-slate-500">Your refund will be processed within 48 hours.</p>
                                                    <div class="mt-6 flex justify-center">
                                                        <button @click="showRefundModal = false; $wire.confirmCancellation()" type="button" class="w-full rounded-2xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2">
                                                            OK
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($rebookingRequested && ! $rebookingPaid)
                                    <div class="rounded-2xl border border-blue-200 bg-blue-50/50 p-6 shadow-sm">
                                        <div class="flex items-center justify-between border-b border-blue-200/60 pb-4 mb-6">
                                            <div>
                                                <p class="text-base font-bold text-blue-900">Rebook Booking #{{ $booking->transaction_number }}</p>
                                                <p class="text-xs text-blue-700">Select your new travel dates, preferred schedule, and accommodation.</p>
                                            </div>
                                            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-800">
                                                @if($rebooking_step === 'departure_date') Step 1: Departure @elseif($rebooking_step === 'departure_accommodation') Step 2: Departure Accommodation @elseif($rebooking_step === 'return_date') Step 3: Return @elseif($rebooking_step === 'return_accommodation') Step 4: Return Accommodation @else Review & Payment @endif
                                            </span>
                                        </div>

                                        @if($feedback)
                                            <div class="mb-4 rounded-xl border border-rose-300 bg-rose-50 p-4 text-sm text-rose-700 shadow-sm">
                                                <div class="flex items-start">
                                                    <svg class="h-5 w-5 flex-shrink-0 text-rose-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    <div>
                                                        <p class="font-bold">Reminder</p>
                                                        <p class="mt-1">{{ $feedback }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- STEP 1: DEPARTURE DATE & SCHEDULE --}}
                                        @if($rebooking_step === 'departure_date')
                                            <div class="space-y-4">
                                                <div>
                                                    <label class="block text-sm font-bold text-slate-700 mb-2">1. Select New Departure Date</label>
                                                    <input 
                                                        type="date" 
                                                        wire:model.live="rebooking_departure_date" 
                                                        min="{{ today()->format('Y-m-d') }}"
                                                        class="w-full max-w-xs rounded-xl border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-white" 
                                                    />
                                                    @error('rebooking_departure_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                                </div>

                                                @if($rebooking_departure_date)
                                                    <div class="mt-6">
                                                        <h4 class="text-sm font-bold text-slate-900 mb-3">Available Departure Schedules for {{ \Carbon\Carbon::parse($rebooking_departure_date)->format('M d, Y') }}</h4>
                                                        @php $depSchedules = $this->availableRebookingDepartureSchedules; @endphp
                                                        @if($depSchedules->isEmpty())
                                                            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                                                                No available schedules found for this date. Please try selecting another date.
                                                            </div>
                                                        @else
                                                            <div class="grid gap-3 sm:grid-cols-2">
                                                                @foreach($depSchedules as $sch)
                                                                    <div 
                                                                        wire:key="dep-sch-{{ $sch->id }}"
                                                                        wire:click="selectRebookingDepartureSchedule({{ $sch->id }}, {{ $booking->getMode() === 'airline' ? 0 : $sch->price }})"
                                                                        class="cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-blue-500 hover:bg-blue-50/40 transition flex flex-col justify-between"
                                                                    >
                                                                        <div>
                                                                            <div class="flex items-center justify-between">
                                                                                <span class="text-base font-bold text-slate-900">{{ \Carbon\Carbon::parse($sch->departure_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($sch->arrival_time)->format('g:i A') }}</span>
                                                                                <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800">{{ $sch->ferryRoute->mode ?? 'ferry' }}</span>
                                                                            </div>
                                                                            <p class="mt-1 text-xs text-slate-500">{{ $sch->ferryRoute->operator ?? 'Operator' }}</p>
                                                                            <p class="mt-1 text-xs text-slate-600 font-medium">{{ $sch->ferryRoute->origin ?? '' }} &rarr; {{ $sch->ferryRoute->destination ?? '' }}</p>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif

                                                <div class="pt-4 flex justify-end">
                                                    <button wire:click.prevent="$set('rebookingRequested', false); $set('feedback', null)" type="button" class="inline-flex items-center justify-center rounded-3xl border border-slate-300 bg-white px-6 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                                                        Cancel Rebooking
                                                    </button>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- STEP 2: DEPARTURE ACCOMMODATION --}}
                                        @if($rebooking_step === 'departure_accommodation')
                                            <div class="space-y-4">
                                                <div class="flex items-center justify-between border-b border-slate-200/60 pb-3">
                                                    <div>
                                                        <h4 class="text-base font-bold text-slate-900">2. Choose Departure Accommodation</h4>
                                                        <p class="text-xs text-slate-500">Select your preferred accommodation for the chosen schedule.</p>
                                                    </div>
                                                    <button wire:click="setRebookingStep('departure_date')" type="button" class="text-xs font-semibold text-blue-600 hover:underline">&larr; Change Schedule</button>
                                                </div>

                                                @php $depAccommodations = $this->rebookingDepartureAccommodations; @endphp
                                                @if($depAccommodations->isEmpty())
                                                    <div class="rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                                                        No accommodation options found for this schedule.
                                                    </div>
                                                @else
                                                    <div class="grid gap-3 sm:grid-cols-2">
                                                        @foreach($depAccommodations as $acc)
                                                            @if($acc->disabled)
                                                                <div wire:key="dep-acc-{{ $acc->id }}" class="rounded-xl border border-slate-100 bg-slate-50 p-4 opacity-50 cursor-not-allowed flex flex-col justify-between">
                                                                    <div>
                                                                        <div class="flex items-center justify-between mb-1">
                                                                            <span class="text-sm font-bold text-slate-500">{{ $acc->name }}</span>
                                                                            <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs text-slate-500">Not eligible</span>
                                                                        </div>
                                                                        @if($acc->description)
                                                                            <p class="mt-1 text-xs text-slate-400 line-clamp-2">{{ $acc->description }}</p>
                                                                        @endif
                                                                    </div>
                                                                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                                                                        <span class="text-xs text-slate-400">Total per person</span>
                                                                        <span class="text-sm font-bold text-slate-400">₱{{ number_format(($rebooking_dep_schedule_price ?? 0) + $acc->price, 2) }}</span>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div
                                                                    wire:key="dep-acc-{{ $acc->id }}"
                                                                    wire:click="selectRebookingDepartureAccommodation('{{ $acc->id }}', {{ $acc->price }})"
                                                                    class="cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-blue-500 hover:bg-blue-50/40 transition flex flex-col justify-between"
                                                                >
                                                                    <div>
                                                                        <span class="text-sm font-bold text-slate-900">{{ $acc->name }}</span>
                                                                        @if($acc->description)
                                                                            <p class="mt-1 text-xs text-slate-500 line-clamp-2">{{ $acc->description }}</p>
                                                                        @endif
                                                                    </div>
                                                                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                                                                        <span class="text-xs text-slate-500">Total per person</span>
                                                                        <span class="text-sm font-bold text-blue-600">₱{{ number_format(($rebooking_dep_schedule_price ?? 0) + $acc->price, 2) }}</span>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- STEP 3: RETURN DATE & SCHEDULE --}}
                                        @if($rebooking_step === 'return_date')
                                            <div class="space-y-4">
                                                <div class="flex items-center justify-between border-b border-slate-200/60 pb-3">
                                                    <h4 class="text-base font-bold text-slate-900">3. Select Return Date & Schedule</h4>
                                                    <button wire:click="setRebookingStep('departure_accommodation')" type="button" class="text-xs font-semibold text-blue-600 hover:underline">&larr; Back to Departure</button>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-bold text-slate-700 mb-2">Select Return Date</label>
                                                    <input 
                                                        type="date" 
                                                        wire:model.live="rebooking_return_date" 
                                                        min="{{ $rebooking_departure_date ?? today()->format('Y-m-d') }}"
                                                        class="w-full max-w-xs rounded-xl border border-slate-300 px-4 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-white" 
                                                    />
                                                    @error('rebooking_return_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                                </div>

                                                @if($rebooking_return_date)
                                                    <div class="mt-6">
                                                        <h4 class="text-sm font-bold text-slate-900 mb-3">Available Return Schedules for {{ \Carbon\Carbon::parse($rebooking_return_date)->format('M d, Y') }}</h4>
                                                        @php $retSchedules = $this->availableRebookingReturnSchedules; @endphp
                                                        @if($retSchedules->isEmpty())
                                                            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                                                                No available schedules found for this date.
                                                            </div>
                                                        @else
                                                            <div class="grid gap-3 sm:grid-cols-2">
                                                                @foreach($retSchedules as $sch)
                                                                    <div 
                                                                        wire:key="ret-sch-{{ $sch->id }}"
                                                                        wire:click="selectRebookingReturnSchedule({{ $sch->id }}, {{ $booking->getMode() === 'airline' ? 0 : $sch->price }})"
                                                                        class="cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-blue-500 hover:bg-blue-50/40 transition flex flex-col justify-between"
                                                                    >
                                                                        <div>
                                                                            <div class="flex items-center justify-between">
                                                                                <span class="text-base font-bold text-slate-900">{{ \Carbon\Carbon::parse($sch->departure_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($sch->arrival_time)->format('g:i A') }}</span>
                                                                                <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800">{{ $sch->ferryRoute->mode ?? 'ferry' }}</span>
                                                                            </div>
                                                                            <p class="mt-1 text-xs text-slate-500">{{ $sch->ferryRoute->operator ?? 'Operator' }}</p>
                                                                            <p class="mt-1 text-xs text-slate-600 font-medium">{{ $sch->ferryRoute->origin ?? '' }} &rarr; {{ $sch->ferryRoute->destination ?? '' }}</p>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- STEP 4: RETURN ACCOMMODATION --}}
                                        @if($rebooking_step === 'return_accommodation')
                                            <div class="space-y-4">
                                                <div class="flex items-center justify-between border-b border-slate-200/60 pb-3">
                                                    <div>
                                                        <h4 class="text-base font-bold text-slate-900">4. Choose Return Accommodation</h4>
                                                        <p class="text-xs text-slate-500">Select your preferred accommodation for the return schedule.</p>
                                                    </div>
                                                    <button wire:click="setRebookingStep('return_date')" type="button" class="text-xs font-semibold text-blue-600 hover:underline">&larr; Change Return Schedule</button>
                                                </div>

                                                @php $retAccommodations = $this->rebookingReturnAccommodations; @endphp
                                                @if($retAccommodations->isEmpty())
                                                    <div class="rounded-xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">
                                                        No accommodation options found for this schedule.
                                                    </div>
                                                @else
                                                    <div class="grid gap-3 sm:grid-cols-2">
                                                        @foreach($retAccommodations as $acc)
                                                            @if($acc->disabled)
                                                                <div wire:key="ret-acc-{{ $acc->id }}" class="rounded-xl border border-slate-100 bg-slate-50 p-4 opacity-50 cursor-not-allowed flex flex-col justify-between">
                                                                    <div>
                                                                        <div class="flex items-center justify-between mb-1">
                                                                            <span class="text-sm font-bold text-slate-500">{{ $acc->name }}</span>
                                                                            <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs text-slate-500">Not eligible</span>
                                                                        </div>
                                                                        @if($acc->description)
                                                                            <p class="mt-1 text-xs text-slate-400 line-clamp-2">{{ $acc->description }}</p>
                                                                        @endif
                                                                    </div>
                                                                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                                                                        <span class="text-xs text-slate-400">Total per person</span>
                                                                        <span class="text-sm font-bold text-slate-400">₱{{ number_format(($rebooking_ret_schedule_price ?? 0) + $acc->price, 2) }}</span>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <div
                                                                    wire:key="ret-acc-{{ $acc->id }}"
                                                                    wire:click="selectRebookingReturnAccommodation('{{ $acc->id }}', {{ $acc->price }})"
                                                                    class="cursor-pointer rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:border-blue-500 hover:bg-blue-50/40 transition flex flex-col justify-between"
                                                                >
                                                                    <div>
                                                                        <span class="text-sm font-bold text-slate-900">{{ $acc->name }}</span>
                                                                        @if($acc->description)
                                                                            <p class="mt-1 text-xs text-slate-500 line-clamp-2">{{ $acc->description }}</p>
                                                                        @endif
                                                                    </div>
                                                                    <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3">
                                                                        <span class="text-xs text-slate-500">Total per person</span>
                                                                        <span class="text-sm font-bold text-blue-600">₱{{ number_format(($rebooking_ret_schedule_price ?? 0) + $acc->price, 2) }}</span>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        {{-- STEP 5: REVIEW & PAYMENT --}}
                                        @if($rebooking_step === 'confirm')
                                            <div class="space-y-6">
                                                <div class="flex items-center justify-between border-b border-slate-200/60 pb-3">
                                                    <div>
                                                        <h4 class="text-base font-bold text-slate-900">Review & Payment Before / After Booking</h4>
                                                        <p class="text-xs text-slate-500">Review your new schedule and fare difference computation.</p>
                                                    </div>
                                                    <button wire:click="setRebookingStep('{{ $rebooking_is_round_trip ? 'return_accommodation' : 'departure_accommodation' }}')" type="button" class="text-xs font-semibold text-blue-600 hover:underline">&larr; Back to edits</button>
                                                </div>

                                                <div class="grid gap-4 sm:grid-cols-2">
                                                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">New Departure Selection</p>
                                                        <p class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($rebooking_departure_date)->format('M d, Y') }}</p>
                                                        @if($rebooking_dep_schedule_id)
                                                            @php $sch = \App\Models\Schedule::find($rebooking_dep_schedule_id); @endphp
                                                            @if($sch)
                                                                <p class="text-xs text-slate-600 mt-1">{{ \Carbon\Carbon::parse($sch->departure_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($sch->arrival_time)->format('g:i A') }}</p>
                                                            @endif
                                                        @endif
                                                    </div>

                                                    @if($rebooking_is_round_trip && $rebooking_return_date)
                                                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">New Return Selection</p>
                                                            <p class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($rebooking_return_date)->format('M d, Y') }}</p>
                                                            @if($rebooking_ret_schedule_id)
                                                                @php $rsch = \App\Models\Schedule::find($rebooking_ret_schedule_id); @endphp
                                                                @if($rsch)
                                                                    <p class="text-xs text-slate-600 mt-1">{{ \Carbon\Carbon::parse($rsch->departure_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($rsch->arrival_time)->format('g:i A') }}</p>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="rounded-2xl border border-blue-300 bg-white p-6 shadow-sm">
                                                    <div class="flex items-center justify-between mb-4">
                                                        <h4 class="text-xs font-bold uppercase tracking-wider text-blue-800">Rebooking Fee Computation</h4>
                                                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200">
                                                            {{ $this->selectedItemsLabel }}
                                                        </span>
                                                    </div>
                                                    <div class="space-y-3 text-sm">
                                                        <div class="flex justify-between text-slate-600">
                                                            <span>Original Base Fare (Selected Items)</span>
                                                            <span class="font-medium">₱{{ number_format(max(0, $rebooking_new_total - $rebooking_rate_diff), 2) }}</span>
                                                        </div>
                                                        <div class="flex justify-between text-slate-900 font-semibold">
                                                            <span>New Schedule Total</span>
                                                            <span>₱{{ number_format($rebooking_new_total, 2) }}</span>
                                                        </div>
                                                        <div class="flex justify-between pt-2 mt-2 border-t border-slate-100 text-slate-600">
                                                            <span>Rate Difference</span>
                                                            <span class="font-medium">₱{{ number_format($rebooking_rate_diff, 2) }}</span>
                                                        </div>
                                                        <div class="flex justify-between text-slate-600">
                                                            <span>Rebooking Surcharge</span>
                                                            <span class="font-medium">₱{{ number_format($rebooking_surcharge, 2) }}</span>
                                                        </div>
                                                        <div class="flex justify-between text-slate-600">
                                                            <span>Revalidation Fee</span>
                                                            <span class="font-medium">₱{{ number_format($rebooking_revalidation_fee, 2) }}</span>
                                                        </div>
                                                        <div class="border-t border-slate-200 pt-3 flex justify-between text-base font-bold text-blue-900">
                                                            <span>Total Rebooking Fee</span>
                                                            <span>₱{{ number_format($rebooking_total_to_pay, 2) }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                @php 
                                                    $rebookingQrPath = App\Models\PaymentSetting::current()->qr_code_path ?? null;
                                                @endphp
                                                <div class="rounded-2xl border border-slate-200 bg-white p-4" x-data="{ qrModalOpen: false }">
                                                    <div class="flex items-start justify-between gap-4">
                                                        <div>
                                                            <p class="text-sm font-bold text-slate-900">Scan QR to Pay Rebooking Total</p>
                                                            <p class="mt-1 text-xs text-slate-500">Please pay ₱{{ number_format($rebooking_total_to_pay, 2) }} and upload your receipt below.</p>
                                                        </div>
                                                        <button type="button" class="flex-shrink-0 rounded-xl border border-slate-200 bg-slate-50 p-2 text-left transition hover:border-blue-300 hover:bg-blue-50" @click="qrModalOpen = true" aria-label="View payment QR code">
                                                            @if($rebookingQrPath)
                                                                <img src="{{ storage_asset_path($rebookingQrPath) }}" alt="QR code" class="h-14 w-14 rounded-lg object-contain" />
                                                            @else
                                                                <div class="flex h-14 w-14 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-white text-slate-400">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                                        <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                                                                        <path d="M8 8h.01M8 12h.01M8 16h.01M12 8h4M12 12h4M12 16h4"></path>
                                                                    </svg>
                                                                </div>
                                                            @endif
                                                        </button>
                                                    </div>
                                                    <p class="mt-2 text-xs text-slate-500">Tap the QR preview to enlarge it.</p>

                                                    <div x-show="qrModalOpen" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 p-4" style="display: none;">
                                                        <div class="w-full max-w-md rounded-3xl bg-white p-5 shadow-2xl" role="dialog" aria-modal="true" aria-label="Enlarged QR code preview">
                                                            <div class="flex items-center justify-between">
                                                                <p class="text-sm font-semibold text-slate-900">Payment QR Code</p>
                                                                <button type="button" class="rounded-full border border-slate-200 px-3 py-1 text-sm text-slate-600 hover:bg-slate-100" @click="qrModalOpen = false">Close</button>
                                                            </div>
                                                            <div class="mt-4">
                                                                @if($rebookingQrPath)
                                                                    <img src="{{ storage_asset_path($rebookingQrPath) }}" alt="Enlarged QR code" class="mx-auto max-h-96 w-full rounded-2xl object-contain" />
                                                                @else
                                                                    <div class="flex min-h-48 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-6 text-center">
                                                                        <div>
                                                                            <p class="text-sm font-semibold text-slate-700">No QR code available</p>
                                                                            <p class="mt-1 text-sm text-slate-500">The admin has not uploaded a payment QR image yet.</p>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="mt-4 space-y-4">
                                                    <div>
                                                        <label for="rebooking-reference-number" class="mb-2 block text-sm font-semibold text-slate-700">Reference Number</label>
                                                        <input id="rebooking-reference-number" type="text" wire:model.defer="rebooking_reference_number" class="block w-full rounded-xl border border-slate-300 bg-white px-4 py-3 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 transition-all" placeholder="e.g., GCash Ref No. / Bank Transfer Ref No." />
                                                        @error('rebooking_reference_number')<p class="mt-2 text-sm font-bold text-rose-600">{{ $message }}</p>@enderror
                                                    </div>

                                                    <div class="border-2 border-dashed border-blue-200 bg-blue-50/50 rounded-2xl p-6 text-center hover:bg-blue-50 transition">
                                                        <label class="cursor-pointer">
                                                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 mb-3">
                                                                <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                                            </div>
                                                            <span class="block text-sm font-bold text-blue-900">Upload Proof of Payment</span>
                                                            <span class="mt-1 block text-xs text-blue-600">Click to browse for GCash or Maya receipt</span>
                                                            <input type="file" wire:model="rebookingProof" class="hidden" accept="image/*" />
                                                        </label>
                                                        <div wire:loading wire:target="rebookingProof" class="mt-3 text-xs font-medium text-blue-500 flex items-center justify-center">
                                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                                            Uploading file...
                                                        </div>
                                                        @if($rebookingProof)
                                                            <div class="mt-4 flex items-center justify-center gap-3 rounded-xl border border-emerald-200 bg-white p-3 text-left shadow-sm">
                                                                <img src="{{ $rebookingProof->temporaryUrl() }}" alt="Rebooking proof preview" class="h-16 w-16 rounded-lg object-cover border border-slate-200" />
                                                                <div class="flex-1 min-w-0">
                                                                    <p class="text-sm font-bold text-emerald-700 truncate">{{ $rebookingProof->getClientOriginalName() }}</p>
                                                                    <p class="text-xs text-slate-500">File selected successfully</p>
                                                                </div>
                                                                <label class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100 cursor-pointer" title="Change image">
                                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                                                    <input type="file" wire:model="rebookingProof" class="hidden" accept="image/*" />
                                                                </label>
                                                            </div>
                                                        @endif
                                                        @error('rebookingProof')<p class="mt-3 text-sm font-bold text-rose-600">{{ $message }}</p>@enderror
                                                    </div>
                                                </div>

                                                <div class="flex flex-wrap gap-3 pt-2">
                                                    <button 
                                                        type="button" 
                                                        wire:click.prevent="submitRebookingProof" 
                                                        class="inline-flex items-center justify-center rounded-3xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition"
                                                        @disabled($isUploadingRebooking || !$rebookingProof)
                                                    >
                                                        @if($isUploadingRebooking)
                                                            <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                            Uploading...
                                                        @else
                                                            Upload & Confirm Rebooking
                                                        @endif
                                                    </button>
                                                    <button wire:click.prevent="$set('rebookingRequested', false); $set('feedback', null)" type="button" class="inline-flex items-center justify-center rounded-3xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                @if($rebookingPaid)
                                    <div class="rounded-2xl border border-green-200 bg-green-50 p-4">
                                        <p class="text-sm font-semibold text-green-800">Rebooking Fee Paid!</p>
                                        <p class="mt-2 text-sm text-green-700">Your rebooking fee payment has been received. we will email you for your confirmation.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="text-center py-10">
                            <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-rose-100 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <h2 class="text-xl font-bold text-slate-900 mb-2">Booking Not Found</h2>
                            <p class="text-slate-600">We couldn't find a booking matching the provided details.</p>
                            <p class="text-slate-500 text-sm mt-1">Please check your details and try again from the My Booking menu.</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
