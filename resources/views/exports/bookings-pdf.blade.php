<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <style>
        @page {
            size: legal landscape;
            margin: 8mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            color: #333;
            font-size: 8px; /* Reduced font size to fit 27 columns */
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
            font-size: 16px;
        }
        h2 {
            font-size: 12px;
            margin-top: 20px;
            color: #34495e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #3498db;
            color: white;
            padding: 4px 2px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #2980b9;
            font-size: 7px;
        }
        td {
            padding: 4px 2px;
            border: 1px solid #ddd;
            font-size: 7px;
            word-wrap: break-word;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 6px;
        }
        .status-confirmed        { background-color: #27ae60; color: white; }
        .status-pending          { background-color: #f39c12; color: white; }
        .status-cancelled        { background-color: #e74c3c; color: white; }
        .status-operator-cancelled { background-color: #c0392b; color: white; }
        .status-refunded         { background-color: #8e44ad; color: white; }
        .totals-row td {
            background-color: #ecf0f1;
            font-weight: bold;
            font-size: 7px;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #7f8c8d;
            border-top: 1px solid #bdc3c7;
            padding-top: 10px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <div style="margin-bottom: 15px; border-bottom: 2px solid #3498db; padding-bottom: 8px;">
        <table style="width: 100%; border: none; margin: 0;">
            <tr>
                <td style="border: none; padding: 0; vertical-align: middle;">
                    <h1 style="margin: 0; text-align: left; font-size: 16px; color: #2c3e50; border: none; padding: 0;">Amiga Gracia Travel Services</h1>
                    <div style="font-size: 11px; font-weight: bold; color: #34495e; margin-top: 2px;">BOOKING &amp; REMITTANCE REPORT</div>
                </td>
                <td style="border: none; padding: 0; text-align: right; vertical-align: middle;">
                    <div style="font-size: 8px; color: #475569; background: #f8fafc; border: 1px solid #cbd5e1; padding: 4px 8px; border-radius: 4px; display: inline-block;">
                        <strong>Date Range:</strong> 
                        @if(!empty($fromDate) && !empty($toDate))
                            {{ \Carbon\Carbon::parse($fromDate)->format('M d, Y') }} — {{ \Carbon\Carbon::parse($toDate)->format('M d, Y') }}
                        @elseif(!empty($fromDate))
                            From {{ \Carbon\Carbon::parse($fromDate)->format('M d, Y') }}
                        @elseif(!empty($toDate))
                            Up to {{ \Carbon\Carbon::parse($toDate)->format('M d, Y') }}
                        @else
                            All Recorded Dates
                        @endif
                        <br>
                        <strong>Generated On:</strong> {{ ($generatedAt ?? now())->format('M d, Y h:i A') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @php
        $sections = [];
        foreach ($groupedBookings as $title => $items) {
            $sections[] = ['title' => $title, 'items' => $items];
        }

        // Aggregate overall totals for Remittance & Volume summary
        $allUniqueBookings = collect();
        foreach ($groupedBookings as $title => $items) {
            foreach ($items as $b) {
                $allUniqueBookings->push($b);
            }
        }
        $allUniqueBookings = $allUniqueBookings->unique('id');

        $overallSales = 0.0;
        $overallRebookingFee = 0.0;
        $overallRefundRetained = 0.0;

        $volSales = 0;
        $volRefund = 0;
        $volRevalidation = 0;
        $volCancelled = 0;

        foreach ($allUniqueBookings as $b) {
            $passengers = $b->passengers;
            $paxCount = max(1, $passengers->count());

            $isRefunded = in_array($b->status, ['cancelled', 'operator_cancelled']) && (float) $b->refund_amount > 0;
            $isCancelled100 = in_array($b->status, ['cancelled', 'operator_cancelled']) && (float) $b->refund_amount <= 0;

            if ($isCancelled100) {
                // 100% cancellation (₱0 retained, omitted from monetary remittance)
                $volCancelled += $paxCount;
                continue;
            }

            $isPaid = false;
            if ($b->transaction && in_array($b->transaction->payment_status, ['paid', 'refunded'])) {
                $isPaid = true;
            } elseif (in_array($b->status, ['confirmed', 'rebooked'])) {
                $isPaid = true;
            } elseif ($isRefunded) {
                $isPaid = true;
            }

            if (! $isPaid) {
                continue;
            }

            $refundedPaxCount = $passengers->filter(fn($p) => (float)$p->refund_amount > 0 || in_array($p->status, ['cancelled', 'refunded', 'operator_cancelled']))->count();
            $rebookedPaxCount = $passengers->filter(fn($p) => in_array($p->status, ['rebooked', 'rescheduled']))->count();
            $activePaxCount   = max(0, $paxCount - $refundedPaxCount - $rebookedPaxCount);

            if ($isRefunded || $refundedPaxCount > 0) {
                $refAmount = (float) ($b->refund_amount > 0 ? $b->refund_amount : $passengers->sum('refund_amount'));
                $retained = max(0, (float) $b->total_price - $refAmount);
                $overallRefundRetained += $retained;
                $volRefund += ($refundedPaxCount > 0 ? $refundedPaxCount : 1);
                $volSales  += $activePaxCount;
            } else {
                $overallSales += (float) $b->total_price;
                $volSales += ($activePaxCount > 0 ? $activePaxCount : $paxCount);
            }

            $rFee = 0;
            if ($b->transaction && (float) $b->transaction->rebooking_fee > 0) {
                $notes = $b->disruption_notes ? json_decode($b->disruption_notes, true) : [];
                $surcharge = (float) ($notes['surcharge'] ?? 0);
                $reval = (float) ($notes['revalidation_fee'] ?? 0);
                $rateDiff = (float) ($notes['rate_diff'] ?? 0);
                if ($surcharge > 0 || $reval > 0 || $rateDiff > 0) {
                    $rFee = $surcharge + $reval + $rateDiff;
                } else {
                    $rFee = (float) $b->transaction->rebooking_fee;
                }
            }
            if ($rFee > 0 || $rebookedPaxCount > 0) {
                $overallRebookingFee += $rFee;
                $volRevalidation += ($rebookedPaxCount > 0 ? $rebookedPaxCount : 1);
            }
        }

        $toBeRemittedAmount = $overallSales + $overallRebookingFee + $overallRefundRetained;
        $netSalesVolume = $volSales + $volRevalidation + $volRefund;
    @endphp

    @foreach($sections as $section)
        @php
            $sectionTitle = $section['title'];
            $validSectionRows = collect();

            foreach ($section['items'] as $booking) {
                $rawPassengers = $booking->passengers->sortBy('item_number');

                if ($rawPassengers->isEmpty()) {
                    $matches = false;
                    if ($sectionTitle === 'Verified Bookings' && $booking->status === 'confirmed' && ! $booking->is_rebooked) $matches = true;
                    elseif ($sectionTitle === 'Refunded Bookings' && in_array($booking->status, ['cancelled', 'operator_cancelled']) && $booking->refund_amount > 0) $matches = true;
                    elseif ($sectionTitle === 'Rebooked Bookings' && ($booking->is_rebooked || filled($booking->rebooking_status) || in_array($booking->status, ['rebooked', 'operator_rebooking']))) $matches = true;
                    elseif ($sectionTitle === 'Pending Bookings' && $booking->status === 'pending') $matches = true;
                    elseif ($sectionTitle === 'Cancelled Bookings' && in_array($booking->status, ['cancelled', 'operator_cancelled']) && $booking->refund_amount <= 0) $matches = true;

                    if ($matches) {
                        $validSectionRows->push((object)[
                            'booking' => $booking,
                            'passenger' => null,
                            'pIndex' => 0,
                        ]);
                    }
                } else {
                    foreach ($rawPassengers as $pIndex => $p) {
                        $matches = false;
                        if ($sectionTitle === 'Verified Bookings') {
                            $matches = $p->isActiveBookingItem() && ! $p->isRebookingHistoryItem() && in_array($p->status, ['confirmed']);
                        } elseif ($sectionTitle === 'Refunded Bookings') {
                            $matches = $p->isRefundItem();
                        } elseif ($sectionTitle === 'Rebooked Bookings') {
                            $matches = $p->isRebookingHistoryItem();
                        } elseif ($sectionTitle === 'Pending Bookings') {
                            $matches = $p->status === 'pending' && ! $p->isRebookingHistoryItem() && ! $p->isRefundItem();
                        } elseif ($sectionTitle === 'Cancelled Bookings') {
                            $matches = $p->isCancelled() && (float) $p->refund_amount <= 0;
                        }

                        if ($matches) {
                            $validSectionRows->push((object)[
                                'booking' => $booking,
                                'passenger' => $p,
                                'pIndex' => $pIndex,
                            ]);
                        }
                    }
                }
            }
        @endphp
        <h2 class="{{ !$loop->first ? 'page-break' : '' }}">{{ $section['title'] }} ({{ $validSectionRows->count() }} items)</h2>

        @if($validSectionRows->count() > 0)
            @php
                $secTotal          = 0;
                $secVoucherTotal   = 0;
                $secPointsTotal    = 0;
                
                $secBaseFare = 0; $secAccFee = 0; $secVehicleFee = 0; $secBaggageFee = 0;
                $secAdminFee = 0; $secTransactionFee = 0; $secHotelFee = 0; 
                $secRebookingFee = 0; $secCancellationFee = 0; $secPassengerDiscount = 0;
            @endphp
            <table>
                <thead>
                    <tr>
                        <th>Transaction #</th>
                        <th>Item #</th>
                        <th>Passenger Name</th>
                        <th>Contact #</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Dep. Date</th>
                        <th>Ret. Date</th>
                        <th>Mode</th>
                        <th>Operator</th>
                        <th>Status</th>
                        <th>Ref #</th>
                        <th>Base Fare</th>
                        <th>Acc. Fee</th>
                        <th>Veh. Fee</th>
                        <th>Bag. Fee</th>
                        <th>Admin Fee</th>
                        <th>Txn Fee</th>
                        <th>Hotel Fee</th>
                        <th>Rebook Fee</th>
                        <th>Cancel Fee</th>
                        <th>Disc. Type</th>
                        <th>Pax Disc.</th>
                        <th>Voucher</th>
                        <th>V. Disc.</th>
                        <th>Pts Used</th>
                        <th>Pts Disc.</th>
                        <th>TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($validSectionRows as $row)
                        @php
                            $booking = $row->booking;
                            $p = $row->passenger;
                            $pIndex = $row->pIndex;
                            $ferryRoute = $booking->schedule?->ferryRoute;

                            $rebookingFee = 0;
                            if ($booking->transaction && (float) $booking->transaction->rebooking_fee > 0) {
                                $notes = $booking->disruption_notes ? json_decode($booking->disruption_notes, true) : [];
                                $surcharge = (float) ($notes['surcharge'] ?? 0);
                                $reval = (float) ($notes['revalidation_fee'] ?? 0);
                                $rateDiff = (float) ($notes['rate_diff'] ?? 0);
                                if ($surcharge > 0 || $reval > 0 || $rateDiff > 0) {
                                    $rebookingFee = $surcharge + $reval + $rateDiff;
                                } else {
                                    $rebookingFee = (float) $booking->transaction->rebooking_fee;
                                }
                            }

                            $settings = \App\Models\PaymentSetting::current();
                            $calcHotelFee = $booking->accommodations->count() > 0 ? (float) $settings->fee_per_accommodation : 0;
                        @endphp

                        @if(! $p)
                            @php
                                $bTotal = (float) ($booking->transaction?->amount_paid ?: $booking->total_price);
                                if (in_array($booking->status, ['cancelled', 'operator_cancelled']) && $booking->refund_amount > 0) {
                                    $bTotal = (float) $booking->refund_amount;
                                }

                                $statusStr = ucfirst(str_replace('_', ' ', $booking->status));
                                if (in_array($booking->status, ['cancelled', 'operator_cancelled']) && $booking->refund_amount > 0) {
                                    $statusStr = 'Refunded';
                                }

                                $secTotal += $bTotal;
                            @endphp
                            <tr>
                                <td>{{ $booking->transaction_number }}</td>
                                <td>Item 1</td>
                                <td>{{ $booking->client_name }}</td>
                                <td>{{ $booking->client_phone }}</td>
                                <td>{{ $booking->origin }}</td>
                                <td>{{ $booking->destination }}</td>
                                <td>{{ $booking->departure_date?->format('M d, y') }}</td>
                                <td>{{ $booking->return_date?->format('M d, y') ?? '-' }}</td>
                                <td>{{ $ferryRoute?->mode ?? $booking->schedule_service ?? '-' }}</td>
                                <td>{{ $ferryRoute?->operator ?? '-' }}</td>
                                <td>
                                    <span class="status status-{{ strtolower(str_replace(' ', '-', $statusStr)) }}">
                                        {{ $statusStr }}
                                    </span>
                                </td>
                                <td>{{ $booking->transaction?->payment_reference ?? '-' }}</td>
                                <td>0.00</td>
                                <td>0.00</td>
                                <td>{{ number_format($booking->vehicle_price ?? 0, 2) }}</td>
                                <td>0.00</td>
                                <td>0.00</td>
                                <td>0.00</td>
                                <td>0.00</td>
                                <td>{{ number_format($rebookingFee, 2) }}</td>
                                <td>{{ number_format((float) $booking->cancellation_fee, 2) }}</td>
                                <td>-</td>
                                <td>0.00</td>
                                <td>{{ $booking->voucher_code ?? '-' }}</td>
                                <td>{{ number_format((float) $booking->voucher_discount_amount, 2) }}</td>
                                <td>{{ $booking->points_redeemed ?? '-' }}</td>
                                <td>{{ number_format((float) $booking->points_discount, 2) }}</td>
                                <td>{{ number_format($bTotal, 2) }}</td>
                            </tr>
                        @else
                            @php
                                $pBaseFare = $p->getEffectiveFareAmount();
                                $pAccFee = $p->getEffectiveAccommodationAmount();
                                $pVehFee = ($pIndex === 0 && $booking->has_vehicle ? (float) $booking->vehicle_price : 0.0);
                                $pBagFee = (float) $p->extra_baggage_price;
                                $pAdminFee = $p->getEffectiveWebAdminFee();
                                $pTxnFee = $p->getEffectiveTransactionFee();
                                $pHotelFee = ($pIndex === 0 ? $calcHotelFee : 0.0);
                                $pRebookFee = ($pIndex === 0 ? $rebookingFee : 0.0);
                                $pCancelFee = ((float) $p->cancellation_fee > 0 ? (float) $p->cancellation_fee : ($pIndex === 0 ? (float) $booking->cancellation_fee : 0.0));
                                $pPaxDisc = (float) $p->discount_amount;
                                $pVoucherDisc = (float) $p->voucher_discount_share;
                                $pPointsDisc = (float) $p->points_discount_share;
                                
                                $pItemTotal = $p->getEffectiveItemTotal() + $pVehFee + $pHotelFee + $pRebookFee;
                                if ((float) $p->refund_amount > 0) {
                                    $pItemTotal = (float) $p->refund_amount;
                                } elseif (in_array($booking->status, ['cancelled', 'operator_cancelled']) && (float) $booking->refund_amount > 0 && (float) $p->refund_amount <= 0) {
                                    $pItemTotal = (float) ($booking->refund_amount / max(1, $booking->passengers->count()));
                                }

                                $statusStr = $p->getStatusLabel();

                                $secBaseFare += $pBaseFare;
                                $secAccFee += $pAccFee;
                                $secVehicleFee += $pVehFee;
                                $secBaggageFee += $pBagFee;
                                $secAdminFee += $pAdminFee;
                                $secTransactionFee += $pTxnFee;
                                $secHotelFee += $pHotelFee;
                                $secRebookingFee += $pRebookFee;
                                $secCancellationFee += $pCancelFee;
                                $secPassengerDiscount += $pPaxDisc;
                                $secVoucherTotal += $pVoucherDisc;
                                $secPointsTotal += $pPointsDisc;
                                $secTotal += $pItemTotal;
                            @endphp
                            <tr>
                                <td>{{ $booking->transaction_number }}</td>
                                <td>Item {{ $p->item_number ?? ($pIndex + 1) }}</td>
                                <td>{{ $p->name ?? $booking->client_name }}</td>
                                <td>{{ $booking->client_phone }}</td>
                                <td>{{ $booking->origin }}</td>
                                <td>{{ $booking->destination }}</td>
                                <td>{{ $booking->departure_date?->format('M d, y') }}</td>
                                <td>{{ $booking->return_date?->format('M d, y') ?? '-' }}</td>
                                <td>{{ $ferryRoute?->mode ?? $booking->schedule_service ?? '-' }}</td>
                                <td>{{ $ferryRoute?->operator ?? '-' }}</td>
                                <td>
                                    <span class="status status-{{ strtolower(str_replace(' ', '-', $statusStr)) }}">
                                        {{ $statusStr }}
                                    </span>
                                </td>
                                <td>{{ $booking->transaction?->payment_reference ?? '-' }}</td>
                                <td>{{ number_format($pBaseFare, 2) }}</td>
                                <td>{{ number_format($pAccFee, 2) }}</td>
                                <td>{{ number_format($pVehFee, 2) }}</td>
                                <td>{{ number_format($pBagFee, 2) }}</td>
                                <td>{{ number_format($pAdminFee, 2) }}</td>
                                <td>{{ number_format($pTxnFee, 2) }}</td>
                                <td>{{ number_format($pHotelFee, 2) }}</td>
                                <td>{{ number_format($pRebookFee, 2) }}</td>
                                <td>{{ number_format($pCancelFee, 2) }}</td>
                                <td>{{ $p->discount?->name ?? '-' }}</td>
                                <td>{{ number_format($pPaxDisc, 2) }}</td>
                                <td>{{ $pIndex === 0 ? ($booking->voucher_code ?? '-') : '-' }}</td>
                                <td>{{ number_format($pVoucherDisc, 2) }}</td>
                                <td>{{ $pIndex === 0 ? ($booking->points_redeemed ?? '-') : '-' }}</td>
                                <td>{{ number_format($pPointsDisc, 2) }}</td>
                                <td>{{ number_format($pItemTotal, 2) }}</td>
                            </tr>
                        @endif
                    @endforeach

                    <tr class="totals-row">
                        <td colspan="12" style="text-align: right; font-weight: bold;">SECTION TOTAL</td>
                        <td>{{ number_format($secBaseFare, 2) }}</td>
                        <td>{{ number_format($secAccFee, 2) }}</td>
                        <td>{{ number_format($secVehicleFee, 2) }}</td>
                        <td>{{ number_format($secBaggageFee, 2) }}</td>
                        <td>{{ number_format($secAdminFee, 2) }}</td>
                        <td>{{ number_format($secTransactionFee, 2) }}</td>
                        <td>{{ number_format($secHotelFee, 2) }}</td>
                        <td>{{ number_format($secRebookingFee, 2) }}</td>
                        <td>{{ number_format($secCancellationFee, 2) }}</td>
                        <td>-</td>
                        <td>{{ number_format($secPassengerDiscount, 2) }}</td>
                        <td>-</td>
                        <td>{{ number_format($secVoucherTotal, 2) }}</td>
                        <td>-</td>
                        <td>{{ number_format($secPointsTotal, 2) }}</td>
                        <td>{{ number_format($secTotal, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @else
            <p style="text-align: center; color: #7f8c8d;">No {{ strtolower($section['title']) }} found.</p>
        @endif
    @endforeach

    {{-- ═══ Remittance Summary & Volume Breakdown (Separated Blocks) ═══ --}}
    <div style="margin-top: 25px; page-break-inside: avoid; width: 340px;">
        {{-- Remittance Summary Block --}}
        <div style="border: 1px solid #bdc3c7; background: #fafafa; padding: 8px 12px; border-radius: 4px; margin-bottom: 15px;">
            <div style="font-size: 10px; font-weight: bold; color: #2c3e50; border-bottom: 1.5px solid #2c3e50; padding-bottom: 3px; margin-bottom: 6px;">
                Remitance Summary
            </div>
            <table style="width: 100%; border: none; margin: 0; font-size: 8px;">
                <tr>
                    <td style="border: none; padding: 2px 0;">SALES</td>
                    <td style="border: none; padding: 2px 0; text-align: right;">{{ number_format($overallSales, 2) }}</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px 0;">REVALIDATION / REBOOK</td>
                    <td style="border: none; padding: 2px 0; text-align: right;">{{ number_format($overallRebookingFee, 2) }}</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px 0;">REFUND RETAINED (FEES &amp; SURCHARGE)</td>
                    <td style="border: none; padding: 2px 0; text-align: right;">{{ number_format($overallRefundRetained, 2) }}</td>
                </tr>
                <tr style="border-top: 1.5px solid #2c3e50; font-weight: bold; font-size: 9px; background: #edf2f7;">
                    <td style="border: none; padding: 4px 2px;">TO BE REMITTED AMOUNT</td>
                    <td style="border: none; padding: 4px 2px; text-align: right; color: #1e3a8a;">&#8369;{{ number_format($toBeRemittedAmount, 2) }}</td>
                </tr>
            </table>
        </div>

        {{-- Volume Block --}}
        <div style="border: 1px solid #bdc3c7; background: #fafafa; padding: 8px 12px; border-radius: 4px;">
            <div style="font-size: 10px; font-weight: bold; color: #2c3e50; border-bottom: 1.5px solid #2c3e50; padding-bottom: 3px; margin-bottom: 6px;">
                VOLUME
            </div>
            <table style="width: 100%; border: none; margin: 0; font-size: 8px;">
                <tr>
                    <td style="border: none; padding: 2px 0;">SALES (VERIFIED)</td>
                    <td style="border: none; padding: 2px 0; text-align: right; font-weight: bold;">{{ $volSales }}</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px 0;">REFUND (RETAINED)</td>
                    <td style="border: none; padding: 2px 0; text-align: right; font-weight: bold;">{{ $volRefund }}</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px 0;">REVALIDATION / REBOOK</td>
                    <td style="border: none; padding: 2px 0; text-align: right; font-weight: bold;">{{ $volRevalidation }}</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px 0;">CANCELLED (100% REFUND / VOID)</td>
                    <td style="border: none; padding: 2px 0; text-align: right; font-weight: bold; color: #7f8c8d;">{{ $volCancelled }}</td>
                </tr>
                <tr style="border-top: 1.5px solid #2c3e50; font-weight: bold; font-size: 9px; background: #edf2f7;">
                    <td style="border: none; padding: 4px 2px;">NET SALES VOLUME</td>
                    <td style="border: none; padding: 4px 2px; text-align: right; color: #1e3a8a;">{{ $netSalesVolume }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        <p>Generated on {{ ($generatedAt ?? now())->format('F d, Y \a\t h:i:s A') }} | Amiga Gracia Travel Services</p>
    </div>
</body>
</html>
