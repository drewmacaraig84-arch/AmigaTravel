<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Ticket Item {{ $passenger->item_number ?? 1 }} - {{ $passenger->ticket_number ?? $booking->transaction_number }}</title>
    <style>
        @page {
            margin: 10mm 12mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            border-bottom: 2.5px solid #216417;
            padding-bottom: 10px;
        }
        .brand-logo-wrap {
            display: inline-block;
            text-align: left;
        }
        .brand-logo {
            display: block;
            max-width: 170px;
            height: auto;
            margin-bottom: 4px;
        }
        .brand-sub {
            font-size: 10px;
            color: #216417;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .receipt-title-box {
            text-align: right;
        }
        .receipt-badge {
            display: inline-block;
            background-color: #dcfce7;
            border: 1px solid #86efac;
            border-radius: 4px;
            padding: 3px 8px;
            font-size: 9.5px;
            font-weight: bold;
            color: #166534;
            text-transform: uppercase;
        }
        .tx-number {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 3px;
        }
        .tx-date {
            font-size: 9px;
            color: #64748b;
        }
        
        .section-header {
            background-color: #f8fafc;
            border-left: 4px solid #216417;
            padding: 5px 8px;
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 12px;
            margin-bottom: 6px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 7px;
            border: 1px solid #e2e8f0;
            text-align: left;
        }
        .data-table td {
            padding: 5px 7px;
            border: 1px solid #e2e8f0;
            font-size: 9.5px;
            color: #1e293b;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
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
            font-size: 8.5px;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 1px;
        }
        .field-value {
            font-size: 10.5px;
            font-weight: 600;
            color: #0f172a;
        }

        .notice-box {
            background-color: #fffbeb;
            border: 1px solid #fef3c7;
            border-left: 3px solid #d97706;
            border-radius: 4px;
            padding: 8px;
            font-size: 8.5px;
            color: #92400e;
            line-height: 1.35;
            margin-top: 10px;
        }
        .footer {
            margin-top: 16px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            text-align: center;
            font-size: 8.5px;
            color: #64748b;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: bold;
        }
        .highlight {
            color: #ee018d;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/amiga-logo.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('storage/images/amiga-logo.png');
        }
        $isFerry = stripos($booking->schedule_service ?? '', 'airline') === false;
        $isRoundTrip = filled($booking->return_date);
        
        $itemNum = (int) ($passenger->item_number ?? 1);
        $ticketNum = $passenger->ticket_number ?: ($booking->transaction_number . '-' . $itemNum);
        
        $fareAndClass = $passenger->getEffectiveFareAndClass();
        $webFee = $passenger->getEffectiveWebAdminFee();
        $txFee = $passenger->getEffectiveTransactionFee();
        $itemTotal = $passenger->getEffectiveItemTotal();

        $depDate = $passenger->rebooking_departure_date ? \Carbon\Carbon::parse($passenger->rebooking_departure_date) : $booking->departure_date;
        $retDate = $passenger->rebooking_return_date ? \Carbon\Carbon::parse($passenger->rebooking_return_date) : $booking->return_date;
    @endphp

    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <div class="brand-logo-wrap">
                    @if(file_exists($logoPath))
                        <img src="{{ $logoPath }}" alt="Amiga Travel" class="brand-logo">
                    @else
                        <h2 style="margin: 0; color: #216417; font-size: 18px; font-weight: 900;">AMIGA TRAVEL</h2>
                    @endif
                    <div class="brand-sub">Confirmed Booking &bull; Item {{ $itemNum }}</div>
                </div>
            </td>
            <td class="receipt-title-box" style="vertical-align: middle;">
                <div class="receipt-badge">
                    {{ $passenger->is_rebooked ? 'REBOOKED & CONFIRMED' : 'OFFICIAL TICKET' }}
                </div>
                <div class="tx-number">Ticket: {{ $ticketNum }}</div>
                <div class="tx-date">Booking Ref: {{ $booking->transaction_number }}</div>
                <div class="tx-date">Issued: {{ now()->format('M d, Y h:i A') }}</div>
            </td>
        </tr>
    </table>

    {{-- PASSENGER IDENTIFIER --}}
    <div class="section-header">Passenger Information (Item {{ $itemNum }})</div>
    <table class="data-table">
        <tr>
            <td style="width: 35%;">
                <div class="field-label">Passenger Full Name</div>
                <div class="field-value" style="font-size: 12px; color: #216417;">{{ $passenger->name ?? 'Passenger' }}</div>
            </td>
            <td style="width: 20%;">
                <div class="field-label">Passenger Type</div>
                <div class="field-value">{{ ucfirst($passenger->type ?? 'adult') }}</div>
            </td>
            <td style="width: 25%;">
                <div class="field-label">Birthdate / ID</div>
                <div class="field-value">
                    {{ $passenger->birthdate ? $passenger->birthdate->format('M d, Y') : '—' }}
                    @if($passenger->id_number) (ID: {{ $passenger->id_number }}) @endif
                </div>
            </td>
            <td style="width: 20%;">
                <div class="field-label">Discount Applied</div>
                <div class="field-value">{{ $passenger->discount?->name ?? 'Standard / None' }}</div>
            </td>
        </tr>
        @if($passenger->hasPassportInfo() || $passenger->hasExtraBaggage())
            <tr>
                @if($passenger->hasPassportInfo())
                    <td colspan="2">
                        <div class="field-label">Passport Details</div>
                        <div class="field-value">{{ $passenger->passport_number }} ({{ $passenger->passport_country ?? 'PH' }}) &bull; Exp: {{ $passenger->passport_expiry_date ? $passenger->passport_expiry_date->format('M d, Y') : '—' }}</div>
                    </td>
                @endif
                @if($passenger->hasExtraBaggage())
                    <td colspan="{{ $passenger->hasPassportInfo() ? '2' : '4' }}">
                        <div class="field-label">Extra Baggage Allowance</div>
                        <div class="field-value">{{ $passenger->extra_baggage_weight }} (+₱{{ number_format($passenger->extra_baggage_price, 2) }})</div>
                    </td>
                @endif
            </tr>
        @endif
    </table>

    {{-- TRAVEL ITINERARY --}}
    <div class="section-header">Travel Itinerary &amp; Schedule</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Leg</th>
                <th>Route</th>
                <th>Travel Date</th>
                <th>Departure - Arrival</th>
                <th>Carrier / Vessel</th>
                <th>Class / Accommodation</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold">Departure</td>
                <td>{{ $booking->origin }} &rarr; {{ $booking->destination }}</td>
                <td class="font-bold" style="color: #216417;">{{ $depDate ? $depDate->format('M d, Y') : '—' }}</td>
                <td>{{ $booking->schedule_departure_time ?? '—' }} - {{ $booking->schedule_arrival_time ?? '—' }}</td>
                <td>{{ $booking->schedule?->ferryRoute?->operatorRecord?->name ?? $booking->schedule_service ?? 'Operator' }}</td>
                <td>{{ $booking->schedule_accommodation_name ?? 'Standard Class' }}</td>
            </tr>
            @if($isRoundTrip && $retDate)
                <tr>
                    <td class="font-bold">Return</td>
                    <td>{{ $booking->destination }} &rarr; {{ $booking->origin }}</td>
                    <td class="font-bold" style="color: #216417;">{{ $retDate->format('M d, Y') }}</td>
                    <td>{{ $booking->return_schedule_departure_time ?? '—' }} - {{ $booking->return_schedule_arrival_time ?? '—' }}</td>
                    <td>{{ $booking->returnSchedule?->ferryRoute?->operatorRecord?->name ?? $booking->return_schedule_service ?? 'Operator' }}</td>
                    <td>{{ $booking->return_schedule_accommodation_name ?? 'Standard Class' }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- PASSENGER ITEM FINANCIAL SUMMARY --}}
    <div class="section-header">Passenger Fare Breakdown</div>
    <table class="data-table">
        <tr>
            <td style="width: 70%;" class="font-bold">Base Ticket &amp; Accommodation Fare</td>
            <td class="text-right font-bold">₱{{ number_format($fareAndClass, 2) }}</td>
        </tr>
        @if($passenger->discount_amount > 0)
            <tr>
                <td>Passenger Discount ({{ $passenger->discount?->name ?? 'Discount' }})</td>
                <td class="text-right" style="color: #16a34a;">-₱{{ number_format($passenger->discount_amount, 2) }}</td>
            </tr>
        @endif
        @if($passenger->voucher_discount_share > 0)
            <tr>
                <td>Voucher Discount Share</td>
                <td class="text-right" style="color: #16a34a;">-₱{{ number_format($passenger->voucher_discount_share, 2) }}</td>
            </tr>
        @endif
        @if($passenger->points_discount_share > 0)
            <tr>
                <td>Gracia Points Share</td>
                <td class="text-right" style="color: #16a34a;">-₱{{ number_format($passenger->points_discount_share, 2) }}</td>
            </tr>
        @endif
        @if($passenger->hasExtraBaggage())
            <tr>
                <td>Extra Baggage ({{ $passenger->extra_baggage_weight }})</td>
                <td class="text-right">₱{{ number_format($passenger->extra_baggage_price, 2) }}</td>
            </tr>
        @endif
        @if($webFee > 0)
            <tr>
                <td>Web Admin Fee Share</td>
                <td class="text-right">₱{{ number_format($webFee, 2) }}</td>
            </tr>
        @endif
        @if($txFee > 0)
            <tr>
                <td>Transaction Fee Share</td>
                <td class="text-right">₱{{ number_format($txFee, 2) }}</td>
            </tr>
        @endif
        <tr style="background-color: #f8fafc;">
            <td class="font-bold" style="font-size: 11px;">Item Total (Paid &amp; Verified)</td>
            <td class="text-right font-bold highlight" style="font-size: 12px;">₱{{ number_format($itemTotal, 2) }}</td>
        </tr>
    </table>

    {{-- BOARDING INSTRUCTIONS --}}
    <div class="notice-box">
        <strong>Important Boarding &amp; Travel Guidelines:</strong><br>
        &bull; Please print the Itinerary voucher / Tickets along with a valid government or school-issued ID matching the passenger name at terminal check-in.<br>
        &bull; For Ferry voyages, check-in commences 2 hours prior to scheduled departure. Boarding gates close 30 minutes prior to departure.<br>
        &bull; For Flights, check-in counters open 3 hours prior for domestic flights and close strictly 45 minutes before departure.<br>
        &bull; Terminal and port fees, where applicable, may be collected separately by the respective port authorities.
    </div>

    <div class="footer">
        <strong>Amiga Gracia Travel and Tours Services</strong> &bull; support@amigatravel.ph &bull; +63 (02) 8123-4567<br>
        This document is an electronic passenger ticket. Valid for travel upon terminal verification.
    </div>
</body>
</html>
