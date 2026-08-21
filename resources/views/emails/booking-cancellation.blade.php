<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Booking Cancellation - Amiga Travel</title>
    </head>
    <body style="font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 24px; background-color: #f8fafc;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 650px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <!-- Header with Logo & Brand -->
            <tr>
                <td style="padding: 28px 32px 20px 32px; background: linear-gradient(135deg, #881337 0%, #be123c 100%); text-align: center;">
                    <img src="{{ isset($message) && file_exists(public_path('images/amiga_logo_white_outline.png')) ? $message->embed(public_path('images/amiga_logo_white_outline.png')) : (file_exists(public_path('images/amiga-logo-transparent.png')) && isset($message) ? $message->embed(public_path('images/amiga-logo-transparent.png')) : 'https://www.amigagracia.com/images/amiga-logo-transparent.png') }}" alt="Amiga Gracia Travel Services" style="max-width: 200px; height: auto; margin-bottom: 12px;" />
                    <div style="font-size: 14px; font-weight: 700; letter-spacing: 1.5px; color: #fecdd3; text-transform: uppercase;">
                        Cancellation Notice
                    </div>
                </td>
            </tr>

            <!-- Status Banner -->
            <tr>
                <td style="padding: 24px 32px 12px 32px; text-align: center;">
                    <div style="display: inline-block; padding: 8px 18px; background-color: #ffe4e6; border: 1px solid #fecdd3; border-radius: 9999px; margin-bottom: 16px;">
                        <span style="color: #9f1239; font-weight: 700; font-size: 13px; letter-spacing: 0.5px; text-transform: uppercase;">
                            ✕ Booking Cancelled
                        </span>
                    </div>
                    <h1 style="margin: 0 0 10px 0; font-size: 24px; font-weight: 800; color: #0f172a;">
                        Your Booking Has Been Cancelled
                    </h1>
                    <p style="margin: 0; font-size: 15px; color: #475569;">
                        Hi <strong>{{ $booking->client_name }}</strong>, your booking with transaction number <strong>{{ $booking->transaction_number }}</strong> has been cancelled upon request.
                    </p>
                </td>
            </tr>

            <!-- Summary Details -->
            <tr>
                <td style="padding: 16px 32px;">
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                        <div style="font-size: 12px; font-weight: 700; color: #be123c; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px;">
                            📋 Cancellation Summary
                        </div>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: 14px; border-collapse: collapse;">
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 0; color: #64748b;">Transaction No.:</td>
                                <td style="padding: 8px 0; text-align: right; font-weight: 700; color: #0f172a; font-family: monospace; font-size: 15px;">
                                    {{ $booking->transaction_number }}
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 0; color: #64748b;">Route:</td>
                                <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">
                                    {{ $booking->origin }} &rarr; {{ $booking->destination }}
                                </td>
                            </tr>
                            @if(filled($refundDestination))
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 0; color: #64748b;">Refund Destination:</td>
                                <td style="padding: 8px 0; text-align: right; font-weight: 700; color: #0f172a;">
                                    {{ $refundDestination }}
                                </td>
                            </tr>
                            @endif
                            @if((float) $booking->refund_amount > 0)
                            <tr>
                                <td style="padding: 10px 0 2px 0; color: #0f172a; font-weight: 700; font-size: 15px;">Estimated Refund:</td>
                                <td style="padding: 10px 0 2px 0; text-align: right; font-weight: 800; color: #047857; font-size: 18px;">
                                    ₱{{ number_format((float) $booking->refund_amount, 2) }}
                                </td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </td>
            </tr>

            <!-- Notice & Support -->
            <tr>
                <td style="padding: 12px 32px 28px 32px; font-size: 13px; color: #475569;">
                    <p style="margin: 0 0 10px 0;">
                        Our finance team will process your refund according to the standard cancellation policy and disburse it to your specified account.
                    </p>
                    <p style="margin: 0; color: #64748b;">
                        If you did not request this cancellation or have questions, please contact our support team immediately.
                    </p>
                </td>
            </tr>

            <!-- Footer Section -->
            <tr>
                <td style="padding: 24px 32px; background-color: #f1f5f9; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; text-align: center;">
                    <p style="margin: 0 0 6px 0; font-weight: 700; color: #334155;">
                        Amiga Gracia Travel Services
                    </p>
                    <p style="margin: 0 0 10px 0;">
                        Kay Amiga, Hassle Free Ka! Accredited Ticketing & Travel Agency
                    </p>
                    <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                        Need assistance? Contact us at support@amigatravel.com.
                    </p>
                </td>
            </tr>
        </table>
    </body>
</html>
