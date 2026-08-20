<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: legal landscape;
            margin: 8mm;
        }
        body {
            font-family: Arial, sans-serif;
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
            $isRefunded = in_array($b->status, ['cancelled', 'operator_cancelled']) && $b->refund_amount > 0;
            $isCancelled100 = in_array($b->status, ['cancelled', 'operator_cancelled']) && $b->refund_amount <= 0;

            if ($isCancelled100) {
                // 100% cancellation (₱0 retained, omitted from monetary remittance)
                $volCancelled++;
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

            if ($isRefunded) {
                // Partial refund - only the remaining amount kept by Amiga
                $retained = max(0, (float) $b->total_price - (float) $b->refund_amount);
                $overallRefundRetained += $retained;
                $volRefund++;
            } else {
                $overallSales += (float) $b->total_price;
                $volSales++;

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
                if ($rFee > 0) {
                    $overallRebookingFee += $rFee;
                    $volRevalidation++;
                }
            }
        }

        $toBeRemittedAmount = $overallSales + $overallRebookingFee + $overallRefundRetained;
        $netSalesVolume = $volSales + $volRevalidation + $volRefund;
    @endphp

    @foreach($sections as $section)
        <h2 class="{{ !$loop->first ? 'page-break' : '' }}">{{ $section['title'] }} ({{ $section['items']->count() }})</h2>

        @if($section['items']->count() > 0)
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
                        <th>Client Name</th>
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
                    @foreach($section['items'] as $booking)
                        @php
                            $ferryRoute = $booking->schedule?->ferryRoute;
                            $discountTypes = $booking->passengers->filter(function($p) {
                                return $p->discount_id !== null && $p->discount;
                            })->map(function($p) {
                                return $p->discount->name;
                            })->unique()->implode(', ');

                            $baseFare = 0; $accFee = 0; $passengerDiscount = 0;
                            foreach ($booking->passengers as $p) {
                                if ($p->is_promo) {
                                    $baseFare += (float) $p->promo_price;
                                } else {
                                    $pDepTicket = (float) ($booking->schedule_price ?? 0);
                                    $pDepAcc = (float) ($booking->schedule_accommodation_price ?? 0);
                                    $pRetTicket = (float) ($booking->return_schedule_price ?? 0);
                                    $pRetAcc = (float) ($booking->return_schedule_accommodation_price ?? 0);
                                    
                                    $grossTicket = $pDepTicket + $pRetTicket;
                                    $grossAcc = $pDepAcc + $pRetAcc;

                                    $baseFare += $grossTicket;
                                    $accFee += $grossAcc;

                                    if ($p->discount) {
                                        $multiplier = ((float) $p->discount->percentage / 100);
                                        $passengerDiscount += ($grossTicket + $grossAcc) * $multiplier;
                                    }
                                }
                            }

                            foreach ($booking->transportClasses as $index => $tc) {
                                $baseFare += (float) $tc->pivot->price;
                            }
                            
                            foreach ($booking->accommodations as $acc) {
                                $accFee += (float) $acc->pivot->price;
                            }

                            $vehicleFee = $booking->has_vehicle ? (float) $booking->vehicle_price : 0;
                            $baggageFee = $booking->has_extra_baggage ? (float) $booking->extra_baggage_price : 0;
                            $cancellationFee = (float) $booking->cancellation_fee;
                            
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

                            $voucherDiscount = (float) $booking->voucher_discount_amount;
                            $pointsDiscount = (float) $booking->points_discount;

                            $knownSum = $baseFare + $accFee + $vehicleFee + $baggageFee - $passengerDiscount - $voucherDiscount - $pointsDiscount;
                            $fees = max(0, (float) $booking->total_price - $knownSum);
                            
                            $transactionFee = 0; $hotelFee = 0; $adminFee = 0;

                            if ($fees > 0.01) {
                                $settings = \App\Models\PaymentSetting::current();
                                $isFerry = stripos($booking->schedule_service ?? '', 'airline') === false;
                                $paxCount = max(1, $booking->passengers->count());
                                $multiplier = $paxCount + ($isFerry ? $paxCount : 0);
                                
                                $isShortHaul = $booking->isShortHaul();
                                $calcTxFee = $multiplier * $settings->getTransactionFee($isShortHaul);
                                $calcHotelFee = $booking->accommodations->count() > 0 ? (float) $settings->fee_per_accommodation : 0;
                                
                                if ($fees >= $calcTxFee && $calcTxFee > 0) {
                                    $transactionFee = $calcTxFee;
                                    $fees -= $calcTxFee;
                                }
                                if ($fees >= $calcHotelFee && $calcHotelFee > 0) {
                                    $hotelFee = $calcHotelFee;
                                    $fees -= $calcHotelFee;
                                }
                                if ($fees > 0.01) {
                                    $adminFee = $fees;
                                }
                            }
                            $isRefunded = in_array($booking->status, ['cancelled', 'operator_cancelled']) && $booking->refund_amount > 0;
                            $totalAmount = (float) $booking->total_price;
                            if ($isRefunded) {
                                $totalAmount = max(0, (float) $booking->total_price - (float) $booking->refund_amount);
                            } elseif ($rebookingFee > 0) {
                                $totalAmount = (float) $booking->total_price + $rebookingFee;
                            }
                            
                            $secBaseFare += $baseFare;
                            $secAccFee += $accFee;
                            $secVehicleFee += $vehicleFee;
                            $secBaggageFee += $baggageFee;
                            $secAdminFee += $adminFee;
                            $secTransactionFee += $transactionFee;
                            $secHotelFee += $hotelFee;
                            $secRebookingFee += $rebookingFee;
                            $secCancellationFee += $cancellationFee;
                            $secPassengerDiscount += $passengerDiscount;
                            $secVoucherTotal += $voucherDiscount;
                            $secPointsTotal += $pointsDiscount;
                            $secTotal += $totalAmount;
                        @endphp
                        <tr>
                            <td>{{ $booking->transaction_number }}</td>
                            <td>{{ $booking->client_name }}</td>
                            <td>{{ $booking->client_phone }}</td>
                            <td>{{ $booking->origin }}</td>
                            <td>{{ $booking->destination }}</td>
                            <td>{{ $booking->departure_date?->format('M d, y') }}</td>
                            <td>{{ $booking->return_date?->format('M d, y') ?? '-' }}</td>
                            <td>{{ $ferryRoute?->mode ?? $booking->schedule_service ?? '-' }}</td>
                            <td>{{ $ferryRoute?->operator ?? '-' }}</td>
                            <td>
                                @php
                                    $statusStr = ucfirst(str_replace('_', ' ', $booking->status));
                                    if (in_array($booking->status, ['cancelled', 'operator_cancelled']) && $booking->refund_amount > 0) {
                                        $statusStr = 'Refunded';
                                    }
                                @endphp
                                <span class="status status-{{ strtolower(str_replace(' ', '-', $statusStr)) }}">
                                    {{ $statusStr }}
                                </span>
                            </td>
                            <td>{{ $booking->transaction?->payment_reference ?? '-' }}</td>
                            <td>{{ number_format($baseFare, 2) }}</td>
                            <td>{{ number_format($accFee, 2) }}</td>
                            <td>{{ number_format($vehicleFee, 2) }}</td>
                            <td>{{ number_format($baggageFee, 2) }}</td>
                            <td>{{ number_format($adminFee, 2) }}</td>
                            <td>{{ number_format($transactionFee, 2) }}</td>
                            <td>{{ number_format($hotelFee, 2) }}</td>
                            <td>{{ number_format($rebookingFee, 2) }}</td>
                            <td>{{ number_format($cancellationFee, 2) }}</td>
                            <td>{{ filled($discountTypes) ? $discountTypes : '-' }}</td>
                            <td>{{ number_format($passengerDiscount, 2) }}</td>
                            <td>{{ filled($booking->voucher_code) ? $booking->voucher_code : '-' }}</td>
                            <td>{{ number_format($voucherDiscount, 2) }}</td>
                            <td>{{ $booking->points_used > 0 ? number_format($booking->points_used) . ' pts' : '-' }}</td>
                            <td>{{ number_format($pointsDiscount, 2) }}</td>
                            <td>{{ number_format($totalAmount, 2) }}</td>
                        </tr>
                    @endforeach

                    {{-- Totals row --}}
                    <tr class="totals-row">
                        <td colspan="11" style="text-align:right;">GRAND TOTAL</td>
                        <td>{{ number_format($secBaseFare, 2) }}</td>
                        <td>{{ number_format($secAccFee, 2) }}</td>
                        <td>{{ number_format($secVehicleFee, 2) }}</td>
                        <td>{{ number_format($secBaggageFee, 2) }}</td>
                        <td>{{ number_format($secAdminFee, 2) }}</td>
                        <td>{{ number_format($secTransactionFee, 2) }}</td>
                        <td>{{ number_format($secHotelFee, 2) }}</td>
                        <td>{{ number_format($secRebookingFee, 2) }}</td>
                        <td>{{ number_format($secCancellationFee, 2) }}</td>
                        <td></td>
                        <td>{{ number_format($secPassengerDiscount, 2) }}</td>
                        <td></td>
                        <td>{{ number_format($secVoucherTotal, 2) }}</td>
                        <td></td>
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
                    <td style="border: none; padding: 2px 0; text-align: right; font-family: monospace;">{{ number_format($overallSales, 2) }}</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px 0;">REVALIDATION / REBOOK</td>
                    <td style="border: none; padding: 2px 0; text-align: right; font-family: monospace;">{{ number_format($overallRebookingFee, 2) }}</td>
                </tr>
                <tr>
                    <td style="border: none; padding: 2px 0;">REFUND RETAINED (FEES &amp; SURCHARGE)</td>
                    <td style="border: none; padding: 2px 0; text-align: right; font-family: monospace;">{{ number_format($overallRefundRetained, 2) }}</td>
                </tr>
                <tr style="border-top: 1.5px solid #2c3e50; font-weight: bold; font-size: 9px; background: #edf2f7;">
                    <td style="border: none; padding: 4px 2px;">TO BE REMITTED AMOUNT</td>
                    <td style="border: none; padding: 4px 2px; text-align: right; font-family: monospace; color: #1e3a8a;">₱{{ number_format($toBeRemittedAmount, 2) }}</td>
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
                    <td style="border: none; padding: 4px 2px; text-align: right; font-family: monospace; color: #1e3a8a;">{{ $netSalesVolume }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">
        <p>Generated on {{ ($generatedAt ?? now())->format('F d, Y \a\t h:i:s A') }} | Amiga Gracia Travel Services</p>
    </div>
</body>
</html>
