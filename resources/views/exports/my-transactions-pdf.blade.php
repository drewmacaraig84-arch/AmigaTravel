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
            color: #333;
            font-size: 8px;
        }
        .header {
            margin-bottom: 12px;
            border-bottom: 2px solid #d97706;
            padding-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th {
            background-color: #d97706;
            color: white;
            padding: 4px 3px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #b45309;
            font-size: 7.5px;
        }
        td {
            padding: 4px 3px;
            border: 1px solid #e2e8f0;
            font-size: 7.5px;
            word-wrap: break-word;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .status {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 6.5px;
            text-transform: uppercase;
        }
        .status-confirmed { background-color: #10b981; color: #ffffff; }
        .status-pending   { background-color: #f59e0b; color: #ffffff; }
        .status-cancelled { background-color: #ef4444; color: #ffffff; }
        .status-refunded  { background-color: #8b5cf6; color: #ffffff; }
        .totals-row td {
            background-color: #fef3c7;
            font-weight: bold;
            font-size: 8px;
            border-top: 2px solid #d97706;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 7.5px;
            color: #64748b;
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
                    <div style="font-size: 11px; font-weight: bold; color: #d97706; margin-top: 2px;">STAFF PERSONAL TRANSACTIONS &amp; BOOKINGS LOG</div>
                </td>
                <td style="border: none; padding: 0; text-align: right; vertical-align: middle;">
                    <div style="font-size: 8px; color: #334155; background: #f8fafc; border: 1px solid #cbd5e1; padding: 5px 9px; border-radius: 4px; display: inline-block;">
                        <strong>Staff Member:</strong> {{ $staffName }} ({{ $staffEmail }})<br>
                        <strong>Total Records:</strong> {{ count($bookings) }} bookings<br>
                        <strong>Generated On:</strong> {{ now()->format('M d, Y h:i A') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25px;">#</th>
                <th style="width: 75px;">Transaction #</th>
                <th style="width: 90px;">Client Name</th>
                <th style="width: 90px;">Email / Contact</th>
                <th style="width: 110px;">Route</th>
                <th style="width: 65px;">Travel Date</th>
                <th style="width: 65px;">Operator</th>
                <th style="width: 55px; text-align: center;">Status</th>
                <th style="width: 70px; text-align: right;">Amount</th>
                <th style="width: 70px;">Payment Ref</th>
                <th style="width: 75px;">Created Date</th>
            </tr>
        </thead>
        <tbody>
            @php $totalRevenue = 0; @endphp
            @forelse($bookings as $idx => $row)
                @php
                    $totalRevenue += (float) $row->total_price;
                    $status = strtolower($row->status ?: 'pending');
                    $statusClass = match($status) {
                        'confirmed' => 'status-confirmed',
                        'pending', 'pending_rebooking' => 'status-pending',
                        'cancelled', 'operator_cancelled' => 'status-cancelled',
                        'refunded' => 'status-refunded',
                        default => 'status-pending',
                    };
                    $ferryRoute = $row->schedule?->ferryRoute;
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold; color: #b45309;">{{ $row->transaction_number ?: "BK-{$row->id}" }}</td>
                    <td><strong>{{ $row->client_name ?: 'N/A' }}</strong></td>
                    <td>{{ $row->client_email }}<br><span style="color: #64748b;">{{ $row->client_phone }}</span></td>
                    <td>{{ $row->origin }} → {{ $row->destination }}</td>
                    <td>{{ $row->departure_date ? $row->departure_date->format('M d, Y') : 'N/A' }}</td>
                    <td>{{ $ferryRoute?->operator ?: ($row->schedule_service ?: '—') }}</td>
                    <td style="text-align: center;">
                        <span class="status {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                    </td>
                    <td style="text-align: right; font-weight: bold;">₱{{ number_format($row->total_price, 2) }}</td>
                    <td>{{ $row->transaction?->payment_reference ?: '—' }}</td>
                    <td>{{ $row->created_at ? $row->created_at->format('M d, Y h:i A') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center; padding: 15px; color: #64748b;">
                        No transactions or bookings found under this staff account.
                    </td>
                </tr>
            @endforelse
            <tr class="totals-row">
                <td colspan="8" style="text-align: right; font-weight: bold;">TOTAL REVENUE HANDLED:</td>
                <td style="text-align: right; font-weight: bold; color: #b45309;">₱{{ number_format($totalRevenue, 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Amiga Gracia Travel Services &bull; Official Staff Performance &amp; Handled Transactions Report &bull; Page 1 of 1
    </div>
</body>
</html>
