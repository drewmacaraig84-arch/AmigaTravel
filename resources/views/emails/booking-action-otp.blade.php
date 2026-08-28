<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Verification Code - Amiga Gracia</title>
    </head>
    <body style="font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 24px; background-color: #f8fafc;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <!-- Header with Brand -->
            <tr>
                <td style="padding: 28px 32px 20px 32px; background: linear-gradient(135deg, #064e3b 0%, #047857 100%); text-align: center;">
                    <img src="{{ isset($message) && file_exists(public_path('images/amiga_logo_white_outline.png')) ? $message->embed(public_path('images/amiga_logo_white_outline.png')) : (file_exists(public_path('images/amiga-logo-transparent.png')) && isset($message) ? $message->embed(public_path('images/amiga-logo-transparent.png')) : 'https://www.amigagracia.com/images/amiga-logo-transparent.png') }}" alt="Amiga Gracia Travel Services" style="max-width: 190px; height: auto; margin-bottom: 10px;" />
                    <div style="font-size: 13px; font-weight: 700; letter-spacing: 1.5px; color: #a7f3d0; text-transform: uppercase;">
                        Security Verification Code
                    </div>
                </td>
            </tr>

            <!-- Content Area -->
            <tr>
                <td style="padding: 32px 32px 20px 32px; text-align: center;">
                    <div style="display: inline-block; padding: 6px 14px; background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 9999px; margin-bottom: 14px;">
                        <span style="color: #047857; font-weight: 700; font-size: 12px; letter-spacing: 0.5px; text-transform: uppercase;">
                            🔒 {{ $actionTitle }}
                        </span>
                    </div>
                    <h1 style="margin: 0 0 10px 0; font-size: 22px; font-weight: 800; color: #0f172a;">
                        Your One-Time Password (OTP)
                    </h1>
                    <p style="margin: 0 0 24px 0; font-size: 15px; color: #475569;">
                        Hi <strong>{{ $booking->client_name }}</strong>, please use the 6-digit code below to authorize your <strong>{{ strtolower($actionTitle) }}</strong> for Booking <strong>#{{ $booking->transaction_number }}</strong>.
                    </p>

                    <!-- OTP Code Box -->
                    <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 2px dashed #047857; border-radius: 12px; padding: 20px; margin: 0 auto 24px auto; max-width: 320px;">
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">
                            One-Time Verification Code
                        </div>
                        <div style="font-size: 36px; font-weight: 900; letter-spacing: 8px; color: #047857; font-family: 'Courier New', Courier, monospace;">
                            {{ $otp }}
                        </div>
                        <div style="font-size: 12px; color: #b91c1c; font-weight: 600; margin-top: 8px;">
                            ⏱️ Valid for 10 minutes only
                        </div>
                    </div>

                    <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.5;">
                        If you did not initiate this request, please ignore this email or contact support immediately. Never share your OTP code with anyone.
                    </p>
                </td>
            </tr>

            <!-- Booking Reference Details -->
            <tr>
                <td style="padding: 12px 32px 24px 32px;">
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; font-size: 13px;">
                        <table width="100%" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="color: #64748b; padding: 4px 0;">Transaction No:</td>
                                <td style="text-align: right; font-weight: 700; color: #0f172a; font-family: monospace;">{{ $booking->transaction_number }}</td>
                            </tr>
                            <tr>
                                <td style="color: #64748b; padding: 4px 0;">Route:</td>
                                <td style="text-align: right; font-weight: 600; color: #0f172a;">{{ $booking->origin }} &rarr; {{ $booking->destination }}</td>
                            </tr>
                            <tr>
                                <td style="color: #64748b; padding: 4px 0;">Travel Date:</td>
                                <td style="text-align: right; font-weight: 600; color: #0f172a;">{{ \Carbon\Carbon::parse($booking->departure_date)->format('M d, Y') }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td style="padding: 20px 32px; background-color: #f1f5f9; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; text-align: center;">
                    <p style="margin: 0 0 4px 0; font-weight: 700; color: #334155;">
                        Amiga Gracia Travel Services
                    </p>
                    <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                        Kay Amiga, Hassle Free Ka! Accredited Ticketing & Travel Agency
                    </p>
                </td>
            </tr>
        </table>
    </body>
</html>
