<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Verification Guarantee Voucher - Amiga Travel</title>
    </head>
    <body style="font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 24px; background-color: #f8fafc;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 650px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <!-- Header with Logo & Brand -->
            <tr>
                <td style="padding: 28px 32px 20px 32px; background: linear-gradient(135deg, #064e3b 0%, #047857 100%); text-align: center;">
                    <img src="{{ isset($message) && file_exists(public_path('images/amiga_logo_white_outline.png')) ? $message->embed(public_path('images/amiga_logo_white_outline.png')) : (file_exists(public_path('images/amiga-logo-transparent.png')) && isset($message) ? $message->embed(public_path('images/amiga-logo-transparent.png')) : 'https://www.amigagracia.com/images/amiga-logo-transparent.png') }}" alt="Amiga Gracia Travel Services" style="max-width: 200px; height: auto; margin-bottom: 12px;" />
                    <div style="font-size: 14px; font-weight: 700; letter-spacing: 1.5px; color: #a7f3d0; text-transform: uppercase;">
                        Service Quality & Verification Guarantee
                    </div>
                </td>
            </tr>

            <!-- Status Banner -->
            <tr>
                <td style="padding: 24px 32px 12px 32px; text-align: center;">
                    <div style="display: inline-block; padding: 8px 18px; background-color: #fdf2f8; border: 1px solid #fbcfe8; border-radius: 9999px; margin-bottom: 16px;">
                        <span style="color: #9d174d; font-weight: 700; font-size: 13px; letter-spacing: 0.5px; text-transform: uppercase;">
                            🎁 Special Reward Voucher
                        </span>
                    </div>
                    <h1 style="margin: 0 0 10px 0; font-size: 24px; font-weight: 800; color: #0f172a;">
                        Thank You For Your Patience
                    </h1>
                    <p style="margin: 0; font-size: 15px; color: #475569;">
                        Dear <strong>{{ $booking->client_name }}</strong>, because your booking (Transaction <strong>{{ $booking->transaction_number }}</strong>) took longer than our standard verification window, we are pleased to award you a special travel discount voucher valid for 3 months (starting from when code is sent to you).
                    </p>
                </td>
            </tr>

            <!-- Voucher Code Box -->
            <tr>
                <td style="padding: 16px 32px;">
                    <div style="background: #f0fdf4; border: 2px dashed #86efac; border-radius: 14px; padding: 24px; text-align: center;">
                        <div style="font-size: 12px; font-weight: 700; color: #166534; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">
                            Your Exclusive Voucher Code
                        </div>
                        <div style="font-size: 28px; font-weight: 800; color: #047857; letter-spacing: 3px; font-family: monospace; margin: 4px 0;">
                            {{ $voucher->code }}
                        </div>
                        <div style="font-size: 16px; font-weight: 700; color: #0f172a; margin-top: 6px;">
                            ₱{{ number_format($voucher->discount_value, 2) }} OFF
                        </div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 4px;">
                            Valid for 3 months &bull; Valid on all ferry and flight bookings
                        </div>
                    </div>
                </td>
            </tr>

            <!-- Instructions -->
            <tr>
                <td style="padding: 12px 32px 28px 32px; font-size: 13px; color: #475569; text-align: center;">
                    <p style="margin: 0;">
                        Apply this voucher code during checkout on your next trip via our website or the <strong>Amiga Gracia Mobile App</strong>.
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
