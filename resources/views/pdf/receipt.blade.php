<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ ($isTicket ?? false) ? 'E-Ticket Itinerary' : 'E-Acknowledgement' }} - {{ $booking->transaction_number }}</title>
    <style>
        @page {
            margin: 12mm 15mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10.5px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            border-bottom: 2px solid #216417;
            padding-bottom: 12px;
        }
        .brand-logo-wrap {
            display: inline-block;
            text-align: left;
        }
        .brand-logo {
            display: block;
            max-width: 180px;
            height: auto;
            margin-bottom: 6px;
        }
        .brand-sub {
            font-size: 11px;
            color: #475569;
            font-weight: bold;
            margin-top: 2px;
        }
        .brand-sub--ack {
            color: #216417;
            font-size: 12px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-top: 0;
        }
        .receipt-title-box {
            text-align: right;
        }
        .receipt-badge {
            display: inline-block;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 4px 10px;
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .badge-paid {
            background-color: #dcfce7;
            border-color: #86efac;
            color: #166534;
        }
        .tx-number {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 4px;
        }
        .tx-date {
            font-size: 10px;
            color: #64748b;
        }
        
        .section-header {
            background-color: #f8fafc;
            border-left: 4px solid #216417;
            padding: 6px 10px;
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 16px;
            margin-bottom: 8px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }
        .data-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
            color: #1e293b;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .info-card {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 12px;
            background-color: #ffffff;
        }
        
        .grid-2 {
            width: 100%;
            border-collapse: collapse;
        }
        .grid-2 td {
            width: 50%;
            vertical-align: top;
            padding: 0 4px;
        }
        .grid-2 td:first-child {
            padding-left: 0;
        }
        .grid-2 td:last-child {
            padding-right: 0;
        }

        .field-label {
            font-size: 9px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .field-value {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
        }

        .summary-box {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        .summary-box td {
            padding: 4px 8px;
            font-size: 10.5px;
        }
        .summary-label {
            text-align: right;
            color: #475569;
            font-weight: 500;
        }
        .summary-amount {
            text-align: right;
            font-weight: bold;
            color: #0f172a;
            width: 130px;
        }
        .total-row td {
            border-top: 2px solid #0f172a;
            border-bottom: 2px solid #0f172a;
            padding: 8px 8px;
            font-size: 13px;
            font-weight: bold;
            background-color: #f8fafc;
        }
        .total-row .summary-amount {
            color: #216417;
            font-size: 14px;
        }

        .notice-box {
            border: 1px dashed #cbd5e1;
            background-color: #f8fafc;
            border-radius: 6px;
            padding: 10px 12px;
            margin-top: 20px;
            font-size: 9.5px;
            color: #475569;
        }
        .notice-title {
            font-weight: bold;
            color: #0f172a;
            font-size: 10px;
            margin-bottom: 4px;
        }

        .footer {
            margin-top: 24px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .peso {
            font-family: 'DejaVu Sans', sans-serif;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <table class="header-table">
        <tr>
            <td>
                <div class="brand-logo-wrap">
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/amiga-logo-transparent.png'))) }}" alt="Amiga Gracia" class="brand-logo" />
                    <div class="brand-sub brand-sub--ack">{{ ($isTicket ?? false) ? 'OFFICIAL E-TICKET & ITINERARY' : 'E-ACKNOWLEDGEMENT' }}</div>
                </div>
            </td>
            <td class="receipt-title-box">
                @php
                    $payStatus = strtolower($booking->transaction?->payment_status ?? $booking->status ?? 'confirmed');
                    $isPaid = in_array($payStatus, ['paid', 'confirmed', 'completed', 'approved']);
                @endphp
                <div class="receipt-badge {{ $isPaid ? 'badge-paid' : '' }}">
                    {{ $isPaid ? 'CONFIRMED / PAID' : strtoupper($payStatus) }}
                </div>
                <div class="tx-number">REF: {{ $booking->transaction_number }}</div>
                <div class="tx-date">Issued: {{ optional($booking->created_at)->format('M d, Y h:i A') ?? date('M d, Y') }}</div>
            </td>
        </tr>
    </table>

    <!-- Client & Contact Information -->
    <div class="section-header">Customer & Contact Information</div>
    <div class="info-card">
        <table class="grid-2">
            <tr>
                <td>
                    <div class="field-label">Primary Customer / Booker</div>
                    <div class="field-value">{{ $booking->client_name }}</div>
                </td>
                <td>
                    <div class="field-label">Email Address</div>
                    <div class="field-value">{{ $booking->client_email }}</div>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 8px;">
                    <div class="field-label">Contact Phone Number</div>
                    <div class="field-value">{{ $booking->client_phone ?: 'N/A' }}</div>
                </td>
                <td style="padding-top: 8px;">
                    <div class="field-label">Trip Route</div>
                    <div class="field-value">{{ $booking->origin }} &rarr; {{ $booking->destination }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Travel Schedule & Itinerary -->
    <div class="section-header">Travel Itinerary & Schedule</div>
    
    <!-- Outbound Journey -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="15%">Journey</th>
                <th width="25%">Departure Date & Time</th>
                <th width="30%">Operator / Service</th>
                <th width="30%">Accommodation / Class</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>OUTBOUND</strong><br><span style="font-size: 8.5px; color: #64748b;">{{ $booking->origin }} &rarr; {{ $booking->destination }}</span></td>
                <td>
                    <strong>{{ $booking->departure_date instanceof \Carbon\Carbon ? $booking->departure_date->format('F d, Y') : \Carbon\Carbon::parse($booking->departure_date)->format('F d, Y') }}</strong>
                    @if($booking->schedule_departure_time)
                        <br><span style="color: #475569;">Time: {{ $booking->schedule_departure_time }}</span>
                    @endif
                    @if($booking->schedule_arrival_time)
                        <br><span style="font-size: 8.5px; color: #64748b;">ETA: {{ $booking->schedule_arrival_time }}</span>
                    @endif
                </td>
                <td>
                    <strong>{{ $booking->schedule_service ?: 'Standard Ferry/Flight' }}</strong>
                </td>
                <td>
                    <strong>{{ $booking->schedule_accommodation_name ?: 'Standard Class' }}</strong>
                    @if($booking->schedule_accommodation_price > 0)
                        <br><span style="font-size: 8.5px; color: #64748b;">Rate: &#8369;{{ number_format($booking->schedule_accommodation_price, 2) }}</span>
                    @endif
                </td>
            </tr>

            <!-- Return Journey if Round Trip -->
            @if($booking->return_date)
            <tr>
                <td><strong>RETURN</strong><br><span style="font-size: 8.5px; color: #64748b;">{{ $booking->destination }} &rarr; {{ $booking->origin }}</span></td>
                <td>
                    <strong>{{ $booking->return_date instanceof \Carbon\Carbon ? $booking->return_date->format('F d, Y') : \Carbon\Carbon::parse($booking->return_date)->format('F d, Y') }}</strong>
                    @if($booking->return_schedule_departure_time)
                        <br><span style="color: #475569;">Time: {{ $booking->return_schedule_departure_time }}</span>
                    @endif
                    @if($booking->return_schedule_arrival_time)
                        <br><span style="font-size: 8.5px; color: #64748b;">ETA: {{ $booking->return_schedule_arrival_time }}</span>
                    @endif
                </td>
                <td>
                    <strong>{{ $booking->return_schedule_service ?: ($booking->schedule_service ?: 'Standard Ferry/Flight') }}</strong>
                </td>
                <td>
                    <strong>{{ $booking->return_schedule_accommodation_name ?: ($booking->schedule_accommodation_name ?: 'Standard Class') }}</strong>
                    @if($booking->return_schedule_accommodation_price > 0)
                        <br><span style="font-size: 8.5px; color: #64748b;">Rate: &#8369;{{ number_format($booking->return_schedule_accommodation_price, 2) }}</span>
                    @endif
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Passengers Manifest -->
    @if($booking->passengers && $booking->passengers->count() > 0)
    <div class="section-header">Passenger Manifest Details</div>
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="28%">Passenger Name</th>
                <th width="15%">Passenger Type</th>
                <th width="22%">ID / Discount Info</th>
                <th width="15%">Outbound Seat</th>
                @if($booking->return_date)
                <th width="15%">Return Seat</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($booking->passengers as $idx => $passenger)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td><strong>{{ $passenger->name }}</strong></td>
                <td>
                    <span style="text-transform: capitalize;">{{ $passenger->type ?: 'Adult' }}</span>
                </td>
                <td>
                    @if($passenger->discount)
                        <span style="color: #15803d; font-weight: bold;">{{ $passenger->discount->name }}</span>
                    @endif
                    @if($passenger->id_number)
                        <br><span style="font-size: 8.5px; color: #64748b;">ID: {{ $passenger->id_number }}</span>
                    @endif
                    @if($passenger->school_name)
                        <br><span style="font-size: 8.5px; color: #64748b;">School: {{ $passenger->school_name }}</span>
                    @endif
                    @if(!$passenger->discount && !$passenger->id_number)
                        <span style="color: #94a3b8;">Standard</span>
                    @endif
                </td>
                <td>
                    @if($passenger->seat_number)
                        <strong>{{ $passenger->seat_section ?: '' }} {{ $passenger->seat_row ? 'Row '.$passenger->seat_row : '' }} #{{ $passenger->seat_number }}</strong>
                    @else
                        <span style="color: #64748b;">Unassigned</span>
                    @endif
                </td>
                @if($booking->return_date)
                <td>
                    @if($passenger->return_seat_number)
                        <strong>{{ $passenger->return_seat_section ?: '' }} {{ $passenger->return_seat_row ? 'Row '.$passenger->return_seat_row : '' }} #{{ $passenger->return_seat_number }}</strong>
                    @else
                        <span style="color: #64748b;">Unassigned</span>
                    @endif
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Vehicle Cargo Cargo Section -->
    @if($booking->has_vehicle)
    <div class="section-header">Vehicle Freight Details</div>
    <div class="info-card">
        <table class="grid-2">
            <tr>
                <td>
                    <div class="field-label">Vehicle Type / Model</div>
                    <div class="field-value">{{ $booking->vehicle_type }}</div>
                </td>
                <td>
                    <div class="field-label">Plate Number</div>
                    <div class="field-value">{{ $booking->vehicle_plate_number }}</div>
                </td>
            </tr>
            <tr>
                <td style="padding-top: 8px;">
                    <div class="field-label">Driver Name</div>
                    <div class="field-value">{{ $booking->driver_name ?: 'N/A' }}</div>
                </td>
                <td style="padding-top: 8px;">
                    <div class="field-label">Vehicle Freight Rate</div>
                    <div class="field-value">&#8369;{{ number_format($booking->vehicle_price, 2) }}</div>
                </td>
            </tr>
        </table>
    </div>
    @endif

    <!-- Financial Breakdown -->
    <div class="section-header">Payment & Financial Summary</div>
    <table class="summary-box">
        @if($booking->subtotal_before_voucher > 0 && $booking->voucher_code)
        <tr>
            <td class="summary-label">Subtotal Before Discount:</td>
            <td class="summary-amount">&#8369;{{ number_format($booking->subtotal_before_voucher, 2) }}</td>
        </tr>
        <tr>
            <td class="summary-label" style="color: #15803d; font-weight: bold;">
                Promo Voucher ({{ $booking->voucher_code }}):
            </td>
            <td class="summary-amount" style="color: #15803d; font-weight: bold;">
                -&#8369;{{ number_format($booking->voucher_discount_amount, 2) }}
            </td>
        </tr>
        @endif

        @if($booking->cancellation_fee > 0)
        <tr>
            <td class="summary-label" style="color: #b91c1c;">Cancellation / Adjustment Fee:</td>
            <td class="summary-amount" style="color: #b91c1c;">&#8369;{{ number_format($booking->cancellation_fee, 2) }}</td>
        </tr>
        @endif

        <tr class="total-row">
            <td class="summary-label">TOTAL AMOUNT PAID:</td>
            <td class="summary-amount">&#8369;{{ number_format($booking->total_price, 2) }}</td>
        </tr>
    </table>

    <!-- Travel Instructions & Reminders -->
    <div class="notice-box">
        <div class="notice-title">IMPORTANT TRAVEL INSTRUCTIONS & REMINDERS</div>
        <ol style="margin: 4px 0 0 16px; padding: 0;">
            <li>Please present a printed copy or digital screenshot of this Acknowledgement at the terminal/check-in counter.</li>
            <li>All passengers must present a valid government-issued ID (or Student/Senior ID for discounted tickets).</li>
            <li>Please arrive at the terminal at least <strong>1.5 hours</strong> before scheduled departure for check-in and security screening.</li>
            <li>Tickets are non-transferable. Unused or expired tickets are subject to carrier revalidation and refund policies.</li>
        </ol>
    </div>

    <!-- Official Footer -->
    <div class="footer">
        Amiga Gracia Travel Service &bull; Official E-Acknowledgement &bull; Ref #{{ $booking->transaction_number }}<br>
        Thank you for choosing Amiga Gracia Travel Service. Have a safe and pleasant trip!
        <div style="color: #ef4444; font-weight: bold; font-size: 10px; margin-top: 6px;">Note: This document is not valid for claiming input taxes</div>
    </div>

</body>
</html>
