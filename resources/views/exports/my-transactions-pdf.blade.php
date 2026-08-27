<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <title>My Handled Transactions Report</title>
    <style>
        @page {
            size: legal landscape;
            margin: 8mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            color: #1e293b;
            font-size: 7.5px;
        }
        .header {
            margin-bottom: 12px;
            border-bottom: 2px solid #d97706;
            padding-bottom: 8px;
        }
        .section-header {
            font-size: 9.5px;
            font-weight: bold;
            padding: 4px 8px;
            margin-top: 14px;
            margin-bottom: 4px;
            border-radius: 4px;
        }
        .section-confirmed      { background-color: #ecfdf5; color: #065f46; border-left: 4px solid #10b981; }
        .section-rebooked       { background-color: #eff6ff; color: #1e40af; border-left: 4px solid #3b82f6; }
        .section-refunded       { background-color: #f5f3ff; color: #5b21b6; border-left: 4px solid #8b5cf6; }
        .section-pending-refund { background-color: #fdf2f8; color: #9d174d; border-left: 4px solid #db2777; }
        .section-pending-rebook { background-color: #fff7ed; color: #9a3412; border-left: 4px solid #ea580c; }
        .section-pending        { background-color: #fefce8; color: #854d0e; border-left: 4px solid #eab308; }
        .section-cancelled      { background-color: #fef2f2; color: #991b1b; border-left: 4px solid #ef4444; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        th {
            background-color: #334155;
            color: white;
            padding: 4px 3px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #1e293b;
            font-size: 7px;
        }
        td {
            padding: 3.5px 3px;
            border: 1px solid #cbd5e1;
            font-size: 7px;
            word-wrap: break-word;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .status-badge {
            display: inline-block;
            padding: 1.5px 3.5px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 6px;
            text-transform: uppercase;
        }
        .badge-confirmed { background-color: #10b981; color: #ffffff; }
        .badge-rebooked  { background-color: #3b82f6; color: #ffffff; }
        .badge-refunded  { background-color: #8b5cf6; color: #ffffff; }
        .badge-pending   { background-color: #f59e0b; color: #ffffff; }
        .badge-cancelled { background-color: #ef4444; color: #ffffff; }

        .subtotal-row td {
            background-color: #f1f5f9;
            font-weight: bold;
            font-size: 7.5px;
            border-top: 1.5px solid #94a3b8;
        }
        .grand-summary {
            margin-top: 14px;
            background-color: #fef3c7;
            border: 1.5px solid #d97706;
            border-radius: 6px;
            padding: 8px 12px;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 7.5px;
            color: #475569;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%; border: none; margin: 0;">
            <tr>
                <td style="border: none; padding: 0; vertical-align: middle;">
                    <h1 style="margin: 0; text-align: left; font-size: 16px; color: #1e293b; border: none; padding: 0;">Amiga Gracia Travel Services</h1>
                    <div style="font-size: 11px; font-weight: bold; color: #d97706; margin-top: 2px;">PERSONAL STAFF PROCESSED TRANSACTIONS &amp; AUDIT LOG</div>
                </td>
                <td style="border: none; padding: 0; text-align: right; vertical-align: middle;">
                    <div style="font-size: 8px; color: #334155; background: #f8fafc; border: 1px solid #cbd5e1; padding: 5px 9px; border-radius: 4px; display: inline-block;">
                        <strong>Processed By:</strong> {{ $staffName }} ({{ $staffEmail }})<br>
                        <strong>Total Records Handled:</strong> {{ $totalHandledCount }} bookings<br>
                        <strong>Generated On:</strong> {{ ($generatedAt ?? now())->format('M d, Y h:i A') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @php
        $overallTotalAmount = 0;
        $overallCount = 0;
    @endphp

    @foreach($groupedBookings as $groupTitle => $bookings)
        @php
            $count = $bookings->count();
            if ($count === 0) continue;
            $overallCount += $count;

            $groupClass = match($groupTitle) {
                'Confirmed Bookings' => 'section-confirmed',
                'Rebooked Bookings' => 'section-rebooked',
                'Refunded Bookings' => 'section-refunded',
                'Pending Refund Bookings' => 'section-pending-refund',
                'Pending Rebook' => 'section-pending-rebook',
                'Pending Bookings' => 'section-pending',
                'Cancelled Bookings' => 'section-cancelled',
                default => 'section-confirmed',
            };

            $thColor = match($groupTitle) {
                'Confirmed Bookings' => '#059669',
                'Rebooked Bookings' => '#2563eb',
                'Refunded Bookings' => '#7c3aed',
                'Pending Refund Bookings' => '#db2777',
                'Pending Rebook' => '#ea580c',
                'Pending Bookings' => '#d97706',
                'Cancelled Bookings' => '#dc2626',
                default => '#334155',
            };
            $subtotal = 0;
        @endphp

        <div class="section-header {{ $groupClass }}">
            {{ strtoupper($groupTitle) }} &mdash; {{ $count }} {{ Str::plural('Booking', $count) }}
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 20px; background-color: {{ $thColor }};">#</th>
                    <th style="width: 75px; background-color: {{ $thColor }};">Transaction #</th>
                    <th style="width: 90px; background-color: {{ $thColor }};">Client Name</th>
                    <th style="width: 90px; background-color: {{ $thColor }};">Email / Phone</th>
                    <th style="width: 110px; background-color: {{ $thColor }};">Route</th>
                    <th style="width: 60px; background-color: {{ $thColor }};">Travel Date</th>
                    <th style="width: 60px; background-color: {{ $thColor }};">Operator</th>
                    <th style="width: 55px; text-align: center; background-color: {{ $thColor }};">Status</th>
                    @if($groupTitle === 'Refunded Bookings')
                        <th style="width: 70px; text-align: right; background-color: {{ $thColor }};">Refund Amount</th>
                    @else
                        <th style="width: 70px; text-align: right; background-color: {{ $thColor }};">Total Amount</th>
                    @endif
                    <th style="width: 65px; background-color: {{ $thColor }};">Payment Ref</th>
                    <th style="width: 75px; background-color: {{ $thColor }};">Processed Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bookings as $idx => $row)
                    @php
                        $displayAmount = ($groupTitle === 'Refunded Bookings' && (float) $row->refund_amount > 0)
                            ? (float) $row->refund_amount
                            : (float) $row->total_price;
                        $subtotal += $displayAmount;
                        $overallTotalAmount += $displayAmount;
                        $ferryRoute = $row->schedule?->ferryRoute;
                        $status = strtolower($row->status ?: 'pending');
                        if ($groupTitle === 'Refunded Bookings') {
                            $status = 'refunded';
                        }
                        $badgeClass = match($status) {
                            'confirmed' => 'badge-confirmed',
                            'rebooked', 'operator_rebooking' => 'badge-rebooked',
                            'refunded' => 'badge-refunded',
                            'cancelled', 'operator_cancelled' => 'badge-cancelled',
                            default => 'badge-pending',
                        };
                        $handledAt = $row->verified_at ?? $row->refund_processed_at ?? $row->updated_at ?? $row->created_at;
                    @endphp
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td style="font-family: monospace; font-weight: bold; color: {{ $thColor }};">{{ $row->transaction_number ?: "BK-{$row->id}" }}</td>
                        <td><strong>{{ $row->client_name ?: 'N/A' }}</strong></td>
                        <td>{{ $row->client_email }}<br><span style="color: #64748b;">{{ $row->client_phone }}</span></td>
                        <td>{{ $row->origin }} → {{ $row->destination }}</td>
                        <td>{{ $row->departure_date ? $row->departure_date->format('M d, Y') : 'N/A' }}</td>
                        <td>{{ $ferryRoute?->operator ?: ($row->schedule_service ?: '—') }}</td>
                        <td style="text-align: center;">
                            <span class="status-badge {{ $badgeClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                        </td>
                        <td style="text-align: right; font-weight: bold;">₱{{ number_format($displayAmount, 2) }}</td>
                        <td>{{ $row->transaction?->payment_reference ?: '—' }}</td>
                        <td>{{ $handledAt ? $handledAt->format('M d, Y h:i A') : 'N/A' }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal-row">
                    <td colspan="8" style="text-align: right; font-weight: bold;">SUBTOTAL ({{ $groupTitle }}):</td>
                    <td style="text-align: right; font-weight: bold; color: {{ $thColor }};">₱{{ number_format($subtotal, 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    @endforeach

    @if($overallCount === 0)
        <div style="text-align: center; padding: 25px; color: #64748b; font-size: 10px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; margin-top: 15px;">
            No transactions or bookings have been processed under this account yet.
        </div>
    @else
        {{-- Grand Summary Box --}}
        <div class="grand-summary">
            <table style="width: 100%; border: none; margin: 0;">
                <tr>
                    <td style="border: none; padding: 0; font-size: 10px; font-weight: bold; color: #92400e;">
                        TOTAL BOOKINGS PROCESSED BY {{ strtoupper($staffName) }}: {{ $overallCount }}
                    </td>
                    <td style="border: none; padding: 0; text-align: right; font-size: 11px; font-weight: bold; color: #92400e;">
                        GRAND TOTAL REVENUE HANDLED: ₱{{ number_format($overallTotalAmount, 2) }}
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <div class="footer">
        Amiga Gracia Travel Services &bull; Report Generated By: <strong>{{ $staffName }}</strong> ({{ $staffEmail }}) &bull; {{ ($generatedAt ?? now())->format('M d, Y h:i A') }} &bull; Page 1 of 1
    </div>
</body>
</html>
