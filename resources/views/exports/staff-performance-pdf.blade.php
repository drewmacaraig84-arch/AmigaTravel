<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="UTF-8">
    <title>Staff Performance & Audit Report</title>
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
            border-bottom: 2px solid #b45309;
            padding-bottom: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th {
            background-color: #b45309;
            color: white;
            padding: 5px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #92400e;
            font-size: 7.5px;
        }
        td {
            padding: 4px;
            border: 1px solid #e2e8f0;
            font-size: 7.5px;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .kpi-box {
            display: inline-block;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 6px 12px;
            margin-right: 10px;
            vertical-align: top;
        }
        .kpi-val {
            font-size: 13px;
            font-weight: bold;
            color: #92400e;
        }
        .kpi-lbl {
            font-size: 6.5px;
            color: #78350f;
            text-transform: uppercase;
        }
        .totals-row td {
            background-color: #fef3c7;
            font-weight: bold;
            font-size: 8px;
            border-top: 2px solid #b45309;
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
                    <div style="font-size: 11px; font-weight: bold; color: #b45309; margin-top: 2px;">STAFF PERFORMANCE &amp; VERIFICATION AUDIT REPORT</div>
                </td>
                <td style="border: none; padding: 0; text-align: right; vertical-align: middle;">
                    <div style="font-size: 8px; color: #334155; background: #f8fafc; border: 1px solid #cbd5e1; padding: 5px 9px; border-radius: 4px; display: inline-block;">
                        <strong>Filter Period:</strong> {{ $periodLabel }}<br>
                        <strong>Total Staff:</strong> {{ count($staffStats) }} registered<br>
                        <strong>Generated On:</strong> {{ now()->format('M d, Y h:i A') }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Summary KPIs --}}
    <div style="margin-bottom: 12px;">
        <div class="kpi-box">
            <div class="kpi-lbl">Total Bookings Handled</div>
            <div class="kpi-val">{{ number_format($summaryKpis['total_bookings'] ?? 0) }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-lbl">Total Revenue Verified</div>
            <div class="kpi-val">₱{{ number_format($summaryKpis['total_revenue'] ?? 0, 2) }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-lbl">Completed Bookings</div>
            <div class="kpi-val">{{ number_format($summaryKpis['total_completed'] ?? 0) }}</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-lbl">Top Performer</div>
            <div class="kpi-val">{{ $summaryKpis['top_staff_name'] ?? 'None' }} ({{ $summaryKpis['top_staff_count'] ?? 0 }})</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25px;">#</th>
                <th>Staff Member</th>
                <th>Email</th>
                <th style="width: 70px;">Role</th>
                <th style="width: 70px; text-align: center;">Total Handled</th>
                <th style="width: 65px; text-align: center;">Completed</th>
                <th style="width: 55px; text-align: center;">Pending</th>
                <th style="width: 55px; text-align: center;">Cancelled</th>
                <th style="width: 55px; text-align: center;">Refunded</th>
                <th style="width: 90px; text-align: right;">Revenue Handled</th>
                <th style="width: 65px; text-align: center;">Success Rate</th>
                <th style="width: 95px;">Last Active Action</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $sumTotal = 0;
                $sumCompleted = 0;
                $sumPending = 0;
                $sumCancelled = 0;
                $sumRefunded = 0;
                $sumRevenue = 0;
            @endphp
            @forelse($staffStats as $idx => $s)
                @php
                    $sumTotal += (int) $s['total_bookings_handled'];
                    $sumCompleted += (int) $s['completed_bookings'];
                    $sumPending += (int) $s['pending_bookings'];
                    $sumCancelled += (int) $s['cancelled_bookings'];
                    $sumRefunded += (int) $s['refunded_bookings'];
                    $sumRevenue += (float) $s['total_revenue_handled'];
                    $role = $s['is_admin'] ? 'Administrator' : ($s['is_staff'] ? 'Staff' : 'User');
                @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td><strong>{{ $s['name'] }}</strong></td>
                    <td>{{ $s['email'] }}</td>
                    <td>{{ $role }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $s['total_bookings_handled'] }}</td>
                    <td style="text-align: center; color: #059669; font-weight: bold;">{{ $s['completed_bookings'] }}</td>
                    <td style="text-align: center; color: #d97706;">{{ $s['pending_bookings'] }}</td>
                    <td style="text-align: center; color: #dc2626;">{{ $s['cancelled_bookings'] }}</td>
                    <td style="text-align: center; color: #7c3aed;">{{ $s['refunded_bookings'] }}</td>
                    <td style="text-align: right; font-weight: bold; color: #047857;">₱{{ number_format($s['total_revenue_handled'], 2) }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $s['completion_rate'] }}%</td>
                    <td>{{ $s['latest_action_at'] ? \Carbon\Carbon::parse($s['latest_action_at'])->format('M d, Y h:i A') : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center; padding: 15px; color: #64748b;">
                        No staff performance records found.
                    </td>
                </tr>
            @endforelse
            <tr class="totals-row">
                <td colspan="4" style="text-align: right; font-weight: bold;">OVERALL TOTALS:</td>
                <td style="text-align: center; font-weight: bold;">{{ $sumTotal }}</td>
                <td style="text-align: center; font-weight: bold; color: #059669;">{{ $sumCompleted }}</td>
                <td style="text-align: center; font-weight: bold; color: #d97706;">{{ $sumPending }}</td>
                <td style="text-align: center; font-weight: bold; color: #dc2626;">{{ $sumCancelled }}</td>
                <td style="text-align: center; font-weight: bold; color: #7c3aed;">{{ $sumRefunded }}</td>
                <td style="text-align: right; font-weight: bold; color: #047857;">₱{{ number_format($sumRevenue, 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Amiga Gracia Travel Services &bull; Official Staff Performance &amp; Verification Audit Report
    </div>
</body>
</html>
