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
                                <p><strong>Status:</strong> {{ in_array($booking->status, ['cancelled', 'operator_cancelled']) && (float) $booking->refund_amount > 0 ? $booking->getRefundStatusLabel() : ucfirst($booking->transaction?->payment_status ?? $booking->status) }}</p>
                                <p><strong>Route:</strong> {{ $booking->origin }} → {{ $booking->destination }}</p>
                                <p><strong>Schedule:</strong> {{ $booking->schedule_summary ?? 'Not recorded' }}</p>
                                <p><strong>Departure:</strong> {{ $booking->departure_date->format('F j, Y') }}</p>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="space-y-2 text-sm text-slate-600">
                                @php
                                    $dashStatusColors = [
                                        'pending'            => ['bg' => '#fef3c7', 'text' => '#92400e'],
                                        'confirmed'          => ['bg' => '#dcfce7', 'text' => '#166534'],
                                        'cancelled'          => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                                        'operator_cancelled' => ['bg' => '#fee2e2', 'text' => '#991b1b'],
                                        'refund_pending'     => ['bg' => '#ffedd5', 'text' => '#9a3412'],
                                        'refunded'           => ['bg' => '#dbeafe', 'text' => '#1e40af'],
                                        'rebooking_pending'  => ['bg' => '#ede9fe', 'text' => '#5b21b6'],
                                        'rebooked'           => ['bg' => '#ccfbf1', 'text' => '#134e4a'],
                                    ];
                                @endphp
                                @if($booking->passengers->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($booking->passengers->sortBy('item_number') as $pax)
                                            @php
                                                $paxStatus = $pax->status ?? 'pending';
                                                $paxStyle = $dashStatusColors[$paxStatus] ?? $dashStatusColors['pending'];
                                                $paxLabel = $pax->getStatusLabel();
                                            @endphp
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium border" style="background: {{ $paxStyle['bg'] }}; color: {{ $paxStyle['text'] }}; border-color: {{ $paxStyle['text'] }}22;">
                                                <span class="font-bold">#{{ $pax->item_number }}</span>
                                                {{ $pax->name ? Str::limit($pax->name, 15) : ucfirst($pax->type ?? 'pax') }}
                                                · {{ $paxLabel }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p><strong>Passengers:</strong> {{ $booking->passengers->count() }}</p>
                                @endif
                                <p><strong>Accommodation items:</strong> {{ $booking->accommodations->count() }}</p>
                                <p><strong>Total:</strong> ₱{{ number_format($booking->total_price, 2) }}</p>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                @if (in_array($booking->status, ['cancelled', 'operator_cancelled']) && (float) $booking->refund_amount > 0)
                                    <a href="{{ route('ticket.refund-acknowledgement', ['transaction_number' => $booking->transaction_number]) }}" target="_blank" class="inline-flex items-center justify-center rounded-3xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">Refund Acknowledgement Receipt</a>
                                    @if(filled($booking->refund_proof))
                                        <a href="{{ storage_asset_path($booking->refund_proof) }}" target="_blank" class="inline-flex items-center justify-center rounded-3xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100">View Proof of Refund</a>
                                    @endif
                                @else
                                    @if ($booking->transaction && $booking->transaction->payment_status !== 'paid')
                                        <a href="{{ route('payment.show', $booking->transaction) }}" class="inline-flex items-center justify-center rounded-3xl bg-amber-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600">Pay / Upload proof</a>
                                    @endif

                                    @if ($booking->transaction && $booking->transaction->payment_status === 'paid')
                                        @php
                                            $itineraryUrl = \Illuminate\Support\Facades\URL::route(
                                                'ticket.download',
                                                ['transaction_number' => $booking->transaction_number]
                                            );
                                            $adminPdfUrl = \Illuminate\Support\Facades\URL::route(
                                                'ticket.admin-pdf',
                                                ['transaction_number' => $booking->transaction_number]
                                            );
                                        @endphp
                                        
                                        <a href="{{ $adminPdfUrl }}" target="_blank" class="inline-flex items-center justify-center rounded-3xl bg-green-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700">Download Ticket</a>
                                        <a href="{{ $itineraryUrl }}" target="_blank" class="inline-flex items-center justify-center rounded-3xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100">Payment Acknowledgement</a>
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
