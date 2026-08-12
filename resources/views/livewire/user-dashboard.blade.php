<div class="space-y-6">
    @if ($bookings->isEmpty())
        <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6 text-center">
            <h2 class="text-xl font-semibold text-slate-900">No bookings yet</h2>
            <p class="mt-2 text-slate-600">Start a new booking to see your transactions and payment status here.</p>
            <a href="{{ url('/book/new') }}" class="mt-4 inline-flex items-center justify-center rounded-3xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">Book a new trip</a>
        </div>
    @else
        <div class="rounded-3xl bg-white p-6 shadow-sm">
            <h2 class="text-2xl font-semibold text-slate-900">Your bookings</h2>
            <p class="mt-2 text-slate-600">Review your active trips, payment status, and ticket downloads.</p>

            <div class="mt-6 space-y-4">
                @foreach($bookings as $booking)
                    <div class="rounded-3xl border border-slate-200 bg-slate-50 p-6">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-sm text-slate-500">Transaction</p>
                                <p class="text-lg font-semibold text-slate-900">{{ $booking->transaction_number }}</p>
                            </div>

                            <div class="grid gap-2 text-sm text-slate-600">
                                <p><strong>Status:</strong> {{ ucfirst($booking->transaction->payment_status) }}</p>
                                <p><strong>Route:</strong> {{ $booking->origin }} → {{ $booking->destination }}</p>
                                <p><strong>Schedule:</strong> {{ $booking->schedule_summary ?? 'Not recorded' }}</p>
                                <p><strong>Departure:</strong> {{ $booking->departure_date->format('F j, Y') }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="space-y-2 text-sm text-slate-600">
                                <p><strong>Passengers:</strong> {{ $booking->passengers->count() }}</p>
                                <p><strong>Accommodation items:</strong> {{ $booking->accommodations->count() }}</p>
                                <p><strong>Total:</strong> ₱{{ number_format($booking->total_price, 2) }}</p>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                @if ($booking->transaction->payment_status !== 'paid')
                                    <a href="{{ route('payment.show', $booking->transaction) }}" class="inline-flex items-center justify-center rounded-3xl bg-amber-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600">Pay / Upload proof</a>
                                @endif

                                @if ($booking->transaction->payment_status === 'paid')
                                    @php
                                        $itineraryUrl = \Illuminate\Support\Facades\URL::route(
                                            'ticket.download',
                                            ['transaction_number' => $booking->transaction_number]
                                        );
                                        $adminPdfUrl = $booking->transaction->confirmation_pdf ? \Illuminate\Support\Facades\URL::route(
                                            'ticket.admin-pdf',
                                            ['transaction_number' => $booking->transaction_number]
                                        ) : null;
                                        $confirmationUrl = $booking->transaction->confirmation_url;
                                    @endphp
                                    
                                    <a href="{{ $itineraryUrl }}" target="_blank" class="inline-flex items-center justify-center rounded-3xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100">Payment Acknowledgement</a>
                                    
                                    @if($adminPdfUrl || $confirmationUrl)
                                        <a href="{{ $adminPdfUrl ?? $confirmationUrl }}" target="_blank" class="inline-flex items-center justify-center rounded-3xl bg-green-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">Download Ticket</a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
