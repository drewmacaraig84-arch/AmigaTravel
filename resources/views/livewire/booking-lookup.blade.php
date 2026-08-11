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

                        {{-- ⏱ 5-Minute Cancellation Reminder Dialog --}}
                        @if($showCancellationReminder)
                            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
                                <div class="w-full max-w-md rounded-[2rem] bg-white p-7 shadow-2xl ring-1 ring-slate-200">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <p class="text-sm font-bold uppercase tracking-widest text-amber-700">Cancellation Window</p>
                                        </div>
                                        <button wire:click="dismissCancellationReminder" type="button" class="rounded-full bg-slate-100 p-2 text-slate-500 transition hover:bg-slate-200">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <h2 class="mt-4 text-xl font-bold text-slate-900">Your booking has been submitted.</h2>
                                    <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                                        Cancellation is free within 5 minutes after providing proof of payment.
                                    </p>

                                    <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                                        Please complete payment to issue your tickets.
                                    </div>

                                    <div class="mt-6 flex flex-wrap gap-3 justify-end">
                                        <button wire:click="dismissCancellationReminder" type="button" class="rounded-3xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                                            Keep my booking
                                        </button>
                                        <button wire:click="requestCancellation" type="button" class="rounded-3xl bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">
                                            Cancel my booking
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
                                            <ul class="space-y-2 list-disc pl-5">
                                                <li>Would you like to proceed with rebooking?</li>
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
                                        {{ $booking->status === 'operator_cancelled' ? 'Cancelled by Operator' : ucfirst($booking->status) }}
                                        @if($booking->rebooking_status === 'pending')
                                            (Rebooking Pending)
                                        @endif
                                    </span>
                                </div>
                            </div>

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

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-2xl bg-white p-4 border border-slate-200">
                                    <p class="text-sm text-slate-500">Route</p>
                                    <p class="font-medium text-slate-900">{{ $booking->origin }} → {{ $booking->destination }}</p>
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

                            <div>
                                <h3 class="font-semibold text-slate-900 mb-3">Passengers</h3>
                                <div class="space-y-2">
                                    @foreach($booking->passengers as $passenger)
                                        <div class="rounded-2xl bg-white p-4 border border-slate-200 flex items-center justify-between">
                                            <div>
                                                <span class="text-slate-800">{{ ucfirst($passenger->type) }}{{ $passenger->name ? ' — ' . $passenger->name : '' }}</span>
                                                @if($passenger->birthdate)
                                                    <span class="text-xs text-slate-500 ml-2">(Bday: {{ $passenger->birthdate->format('Y-m-d') }})</span>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <span class="text-sm text-slate-600">{{ $passenger->discount->name ?? 'No discount' }}</span>
                                                @if($passenger->id_number)
                                                    <p class="text-xs text-slate-400">ID: {{ $passenger->id_number }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

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
                                                @if(!$booking->is_rebooked)
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
                                                $refundBreakdown = $booking->getRefundBreakdown(! $cancellationExpired);
                                            @endphp
                                            <div class="mt-2 p-3 bg-white/60 rounded-xl space-y-1 text-sm text-amber-900 border border-amber-100/50">
                                                <div class="flex justify-between">
                                                    <span>Base Ticket Price:</span>
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
                                                </div>
                                                <div class="flex justify-between pt-1 mt-1 border-t border-amber-200/50 font-bold text-base">
                                                    <span>Total Refundable:</span>
                                                    <span class="text-green-700">₱{{ number_format($refundBreakdown['refundable_amount'], 2) }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        </div>

                                        {{-- Show compiled destination as read-only summary --}}
                                        @if(filled($refund_destination) && blank($refund_account_number))
                                            <div class="mt-3 rounded-xl bg-white border border-amber-100 px-4 py-3">
                                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Refund will be sent to</p>
                                                <p class="text-sm text-slate-800">{{ $refund_destination }}</p>
                                            </div>
                                        @else
                                            <div class="mt-3 space-y-3">
                                                <div>
                                                    <label class="mb-1 block text-sm font-medium text-slate-700">Refund Method</label>
                                                    <select wire:model="refund_method" class="block w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2" style="--tw-ring-color:#ee018d;">
                                                        <option value="GCash">GCash</option>
                                                        <option value="Online Wallet">Online Wallet</option>
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
                                                        {{ $refund_method === 'GCash' ? 'GCash Number' : 'Account Number' }}
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
                                                        placeholder="Full name on the account"
                                                        class="block w-full rounded-2xl border border-slate-300 px-4 py-3 text-sm shadow-sm focus:outline-none focus:ring-2" style="--tw-ring-color:#ee018d;" />
                                                    @error('refund_account_name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                                                </div>
                                            </div>
                                        @endif

                                        <div class="mt-4 flex flex-wrap gap-3">
                                            <button wire:click.prevent="confirmCancellation" type="button" class="inline-flex items-center justify-center rounded-3xl px-6 py-3 text-sm font-semibold text-white shadow-sm transition" style="background:#ee018d;" onmouseover="this.style.background='#c30172'" onmouseout="this.style.background='#ee018d'">
                                                @if(! $cancellationExpired)
                                                    Confirm Cancellation (100% Refund)
                                                @else
                                                    Confirm Cancellation
                                                @endif
                                            </button>
                                            <button wire:click.prevent="cancelCancellationRequest" type="button" class="inline-flex items-center justify-center rounded-3xl border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">
                                                Cancel Request
                                            </button>
                                            @if(!$booking->is_rebooked)
                                                <button wire:click.prevent="confirmRebookingRequest" type="button" class="inline-flex items-center justify-center rounded-3xl border border-blue-500 px-6 py-3 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">
                                                    Switch to Rebook
                                                </button>
                                            @endif
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
                                                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 opacity-50 cursor-not-allowed flex flex-col justify-between">
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
                                                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4 opacity-50 cursor-not-allowed flex flex-col justify-between">
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
                                                    <h4 class="text-xs font-bold uppercase tracking-wider text-blue-800 mb-4">Rebooking Fee Computation</h4>
                                                    <div class="space-y-3 text-sm">
                                                        <div class="flex justify-between text-slate-600">
                                                            <span>Original Booking Base Fare (Before)</span>
                                                            @php
                                                                $paxCount = $booking->passengers()->count() ?: 1;
                                                                $booking->loadMissing('transportClasses');
                                                                $_tcs       = $booking->transportClasses->values();
                                                                $origDep    = (float)($booking->schedule_price ?? 0)
                                                                            + (float)(optional($_tcs->get(0))->pivot?->price ?? 0)
                                                                            + (float)($booking->schedule_accommodation_price ?? 0);
                                                                $origRet    = (float)($booking->return_schedule_price ?? 0)
                                                                            + (float)(optional($_tcs->get(1))->pivot?->price ?? 0)
                                                                            + (float)($booking->return_schedule_accommodation_price ?? 0);
                                                                $displayOrigFare = ($origDep + $origRet) * $paxCount;
                                                            @endphp
                                                            <span class="font-medium">₱{{ number_format($displayOrigFare, 2) }}</span>
                                                        </div>
                                                        <div class="flex justify-between text-slate-900 font-semibold">
                                                            <span>New Schedule Total</span>
                                                            <span>₱{{ number_format($rebooking_new_total, 2) }}</span>
                                                        </div>
                                                        <div class="flex justify-between text-slate-600 pt-2 border-t border-slate-100">
                                                            <span>Rate Difference</span>
                                                            <span class="font-medium">₱{{ number_format($rebooking_rate_diff, 2) }}</span>
                                                        </div>
                                                        <div class="flex justify-between text-slate-600">
                                                            <span>Refund Surcharge</span>
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

                                                <label class="block">
                                                    <span class="mb-2 block text-sm font-medium text-slate-700">Proof of Payment (GCash / Maya Receipt)</span>
                                                    <input type="file" wire:model="rebookingProof" class="mt-2 block w-full text-sm text-slate-600" />
                                                    @error('rebookingProof')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                                </label>

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
                                        <p class="mt-2 text-sm text-green-700">Your rebooking fee payment has been received. Please contact us to complete your rebooking.</p>
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
