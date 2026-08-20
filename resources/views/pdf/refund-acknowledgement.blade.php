<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Refund Acknowledgement - {{ $booking->transaction_number }}</title>
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
            border-bottom: 2px solid #ee018d;
            padding-bottom: 12px;
        }
        .brand-logo {
            display: block;
            max-width: 180px;
            height: auto;
            margin-bottom: 6px;
        }
        .brand-sub {
            color: #ee018d;
            font-size: 12px;
            letter-spacing: 0.5px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 0;
        }
        .receipt-title-box {
            text-align: right;
        }
        .badge-refunded {
            display: inline-block;
            background-color: #fdf2f8;
            border: 1px solid #f472b6;
            border-radius: 4px;
            padding: 4px 10px;
            font-size: 10px;
            font-weight: bold;
            color: #9d174d;
            text-transform: uppercase;
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
            background-color: #fdf2f8;
            border-left: 4px solid #ee018d;
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
            background-color: #f8fafc;
            color: #334155;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        .data-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 10px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .totals-table td {
            padding: 5px 8px;
            font-size: 10px;
        }
        .totals-table .label {
            text-align: right;
            color: #475569;
            width: 75%;
        }
        .totals-table .amount {
            text-align: right;
            font-weight: bold;
            color: #0f172a;
            width: 25%;
        }
        .totals-table .grand-total {
            border-top: 2px solid #ee018d;
            border-bottom: 2px solid #ee018d;
            background-color: #fdf2f8;
        }
        .totals-table .grand-total td {
            padding: 8px;
            font-size: 12px;
            font-weight: bold;
            color: #9d174d;
        }
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 12px;
        }
        .notice-box {
            background-color: #fdf2f8;
            border: 1px solid #fbcfe8;
            border-radius: 6px;
            padding: 10px;
            margin-top: 20px;
            font-size: 9.5px;
            color: #831843;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('images/amiga-logo.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('storage/images/amiga-logo.png');
        }
        $logoBase64 = file_exists($logoPath) ? 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($logoPath)) : null;
    @endphp

    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: middle;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="brand-logo" alt="Amiga Gracia Travel Logo" />
                @else
                    <div style="font-size: 18px; font-weight: bold; color: #ee018d;">AMIGA GRACIA TRAVEL SERVICES</div>
                @endif
                <div class="brand-sub">Official Refund Acknowledgement</div>
            </td>
            <td class="receipt-title-box" style="width: 45%; vertical-align: middle;">
                <span class="badge-refunded">
                    {{ $booking->refund_status === 'completed' ? 'Refunded & Disbursed' : 'Refund In Processing' }}
                </span>
                <div class="tx-number">{{ $booking->transaction_number }}</div>
                <div class="tx-date">Refund Date: {{ $booking->refund_processed_at ? $booking->refund_processed_at->format('M d, Y h:i A') : now()->format('M d, Y') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-header">Client & Booking Information</div>
    <table class="data-table">
        <tr>
            <td style="width: 25%; font-weight: bold; background-color: #f8fafc;">Client Name</td>
            <td style="width: 25%;">{{ $booking->client_name }}</td>
            <td style="width: 25%; font-weight: bold; background-color: #f8fafc;">Email Address</td>
            <td style="width: 25%;">{{ $booking->client_email }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background-color: #f8fafc;">Travel Route</td>
            <td>{{ $booking->origin }} → {{ $booking->destination }}</td>
            <td style="font-weight: bold; background-color: #f8fafc;">Operator</td>
            <td>{{ $booking->getOperatorName() ?: 'N/A' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; background-color: #f8fafc;">Departure Date</td>
            <td>{{ $booking->departure_date ? $booking->departure_date->format('M d, Y') : 'N/A' }}</td>
            <td style="font-weight: bold; background-color: #f8fafc;">Return Date</td>
            <td>{{ $booking->return_date ? $booking->return_date->format('M d, Y') : 'One-Way' }}</td>
        </tr>
    </table>

    <div class="section-header">Disbursement & Transfer Account</div>
    <div class="info-card">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 30%; color: #64748b; font-size: 10px; font-weight: bold;">Refund Sent To:</td>
                <td style="width: 70%; font-size: 11px; font-weight: bold; color: #0f172a;">{{ $booking->refund_destination ?: 'Direct Customer Account' }}</td>
            </tr>
            @if(filled($booking->refund_reference))
                <tr>
                    <td style="color: #64748b; font-size: 10px; font-weight: bold; padding-top: 4px;">Transfer Reference No.:</td>
                    <td style="font-size: 11px; font-weight: bold; color: #ee018d; padding-top: 4px;">{{ $booking->refund_reference }}</td>
                </tr>
            @endif
            @if($booking->refund_processed_at)
                <tr>
                    <td style="color: #64748b; font-size: 10px; font-weight: bold; padding-top: 4px;">Disbursement Timestamp:</td>
                    <td style="font-size: 10px; color: #334155; padding-top: 4px;">{{ $booking->refund_processed_at->format('F d, Y \a\t h:i A') }}</td>
                </tr>
            @endif
        </table>
    </div>

    <div class="section-header">Refund Breakdown & Totals</div>
    <table class="totals-table">
        <tr>
            <td class="label">Original Total Booking Amount:</td>
            <td class="amount">₱{{ number_format((float) $booking->total_price, 2) }}</td>
        </tr>
        @if((float) $booking->cancellation_fee > 0)
            <tr>
                <td class="label" style="color: #e11d48;">Less Cancellation / Service Deductions:</td>
                <td class="amount" style="color: #e11d48;">-₱{{ number_format((float) $booking->cancellation_fee, 2) }}</td>
            </tr>
        @endif
        <tr class="grand-total">
            <td class="label" style="color: #9d174d; font-size: 11px;">NET REFUND AMOUNT DISBURSED:</td>
            <td class="amount" style="color: #9d174d; font-size: 13px;">₱{{ number_format((float) $booking->refund_amount, 2) }}</td>
        </tr>
    </table>

    <div class="notice-box">
        <strong>Customer Notice:</strong> This document serves as official acknowledgement from Amiga Gracia Travel Services that the refund for Transaction <strong>{{ $booking->transaction_number }}</strong> has been verified and processed. Please keep this document and the attached transaction proof for your financial records. If you have any inquiries, contact support at <strong>support@amigatravel.com</strong>.
    </div>

    <div style="margin-top: 24px; text-align: center; font-size: 9px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px;">
        Amiga Gracia Travel Services • Authorized Ticketing & Tour Operations • All Rights Reserved.
    </div>
</body>
</html>
