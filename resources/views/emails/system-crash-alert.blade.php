<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Amiga Gracia System Incident Alert</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            margin: 0;
            padding: 24px;
        }
        .container {
            max-width: 650px;
            margin: 0 auto;
            background: #1e293b;
            border-radius: 12px;
            border: 1px solid #334155;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            padding: 20px 24px;
            color: #ffffff;
        }
        .header h1 {
            margin: 0 0 4px 0;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .header p {
            margin: 0;
            font-size: 13px;
            color: #fecaca;
        }
        .content {
            padding: 24px;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #ef4444;
            color: #ffffff;
        }
        .meta-table {
            width: 100%;
            margin-top: 16px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 8px 12px;
            font-size: 13px;
            border-bottom: 1px solid #334155;
        }
        .meta-table td.label {
            color: #94a3b8;
            width: 140px;
            font-weight: 600;
        }
        .meta-table td.value {
            color: #f8fafc;
            font-family: monospace;
        }
        .trace-box {
            background: #090d16;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 16px;
            margin-top: 20px;
            font-family: Consolas, Monaco, monospace;
            font-size: 12px;
            color: #fca5a5;
            overflow-x: auto;
            white-space: pre-wrap;
            line-height: 1.5;
        }
        .btn-action {
            display: inline-block;
            margin-top: 24px;
            background: #d97706;
            color: #ffffff;
            text-decoration: none;
            padding: 12px 20px;
            font-weight: 600;
            font-size: 14px;
            border-radius: 8px;
        }
        .footer {
            padding: 16px 24px;
            background: #0f172a;
            border-top: 1px solid #334155;
            font-size: 12px;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>AMIGA GRACIA SYSTEM CRASH ALERT</h1>
            <p>Super Administrator Emergency Incident Notification</p>
        </div>
        <div class="content">
            <span class="badge">{{ strtoupper($alertData['severity'] ?? 'CRITICAL') }}</span>
            <p style="margin-top: 12px; font-size: 15px; color: #e2e8f0; line-height: 1.5;">
                <strong>Incident Summary:</strong><br>
                {{ $alertData['message'] ?? 'An uncaught exception or system error was detected on the platform.' }}
            </p>

            <table class="meta-table">
                <tr>
                    <td class="label">Occurred At:</td>
                    <td class="value">{{ $alertData['timestamp'] ?? now()->toDayDateTimeString() }}</td>
                </tr>
                <tr>
                    <td class="label">Environment:</td>
                    <td class="value">{{ app()->environment() }}</td>
                </tr>
                <tr>
                    <td class="label">Triggered URL:</td>
                    <td class="value">{{ $alertData['url'] ?? url('/') }}</td>
                </tr>
                <tr>
                    <td class="label">Source File:</td>
                    <td class="value">{{ $alertData['file'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Line Number:</td>
                    <td class="value">{{ $alertData['line'] ?? 'N/A' }}</td>
                </tr>
            </table>

            @if(!empty($alertData['trace']))
                <div style="margin-top: 20px;">
                    <strong style="font-size: 13px; color: #94a3b8;">Stack Trace Snippet:</strong>
                    <div class="trace-box">{{ Str::limit($alertData['trace'], 2000) }}</div>
                </div>
            @endif

            <div style="text-align: center;">
                <a href="{{ url('/admin/system-dashboard') }}" class="btn-action">
                    Open Super Admin System Dashboard
                </a>
            </div>
        </div>
        <div class="footer">
            Amiga Gracia Travel and Tours &bull; System Health & Log Monitoring Engine
        </div>
    </div>
</body>
</html>
