<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Payment Verification — Action Required - Amiga Travel</title>
    </head>
    <body style="font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 24px; background-color: #f8fafc;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 650px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">

            <!-- Header with Brand -->
            <tr>
                <td style="padding: 28px 32px 20px 32px; background: linear-gradient(135deg, #be123c 0%, #e11d48 100%); text-align: center;">
                    <img src="{{ isset($message) && file_exists(public_path('images/amiga_logo_white_outline.png')) ? $message->embed(public_path('images/amiga_logo_white_outline.png')) : (file_exists(public_path('images/amiga-logo-transparent.png')) && isset($message) ? $message->embed(public_path('images/amiga-logo-transparent.png')) : 'https://www.amigagracia.com/images/amiga-logo-transparent.png') }}" alt="Amiga Gracia Travel Services" style="max-width: 200px; height: auto; margin-bottom: 12px;" />
                    <div style="font-size: 14px; font-weight: 700; letter-spacing: 1.5px; color: #fecdd3; text-transform: uppercase;">
                        Payment Verification Notice
                    </div>
                </td>
            </tr>

            <!-- Status Banner -->
            <tr>
                <td style="padding: 28px 32px 12px 32px; text-align: center;">
                    <div style="display: inline-block; padding: 8px 18px; background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 9999px; margin-bottom: 16px;">
                        <span style="color: #be123c; font-weight: 700; font-size: 13px; letter-spacing: 0.5px; text-transform: uppercase;">
                            ⚠ Action Required — Payment Could Not Be Verified
                        </span>
                    </div>
                    <h1 style="margin: 0 0 12px 0; font-size: 24px; font-weight: 800; color: #0f172a;">
                        We Were Unable to Verify Your Payment
                    </h1>
                    <p style="margin: 0; font-size: 15px; color: #475569;">
                        Dear <strong>{{ $booking->client_name }}</strong>,<br /><br />
                        Thank you for choosing <strong>Amiga Gracia Travel Services</strong>. We sincerely appreciate your trust in us and your patience throughout the booking process.
                    </p>
                </td>
            </tr>

            <!-- Message Body -->
            <tr>
                <td style="padding: 12px 32px 8px 32px; font-size: 15px; color: #334155;">
                    <p style="margin: 0 0 14px 0;">
                        Unfortunately, after carefully reviewing the proof of payment submitted for your booking, our reservations team was unable to proceed with the verification. We want to be fully transparent with you about the reason for this decision:
                    </p>
                </td>
            </tr>

            <!-- Reason Box -->
            <tr>
                <td style="padding: 4px 32px 16px 32px;">
                    <div style="background-color: #fff1f2; border: 1px solid #fecdd3; border-left: 4px solid #e11d48; border-radius: 10px; padding: 18px 20px;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #9f1239; margin-bottom: 8px;">
                            Reason for Non-Verification
                        </div>
                        <div style="font-size: 15px; font-weight: 600; color: #0f172a; margin-bottom: {{ $notes ? '10px' : '0' }};">
                            {{ $reason }}
                        </div>
                        @if($notes)
                        <div style="font-size: 13px; color: #475569; border-top: 1px solid #fecdd3; padding-top: 10px; margin-top: 4px;">
                            <strong>Additional Note from our team:</strong><br />{{ $notes }}
                        </div>
                        @endif
                    </div>
                </td>
            </tr>

            <!-- Booking Details -->
            <tr>
                <td style="padding: 4px 32px 16px 32px;">
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                        <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px;">
                            📋 Booking Reference
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
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 0; color: #64748b;">Travel Date:</td>
                                <td style="padding: 8px 0; text-align: right; font-weight: 700; color: #0369a1;">
                                    {{ \Carbon\Carbon::parse($booking->departure_date)->format('M d, Y') }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 0; color: #64748b;">Booking Status:</td>
                                <td style="padding: 8px 0; text-align: right;">
                                    <span style="background-color: #fff1f2; color: #be123c; font-weight: 700; font-size: 12px; padding: 3px 10px; border-radius: 9999px; border: 1px solid #fecdd3;">Payment Not Verified</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>

            <!-- Next Steps -->
            <tr>
                <td style="padding: 4px 32px 20px 32px; font-size: 14px; color: #334155;">
                    <p style="margin: 0 0 10px 0; font-weight: 700; color: #0f172a;">What to do next?</p>
                    <p style="margin: 0 0 10px 0;">
                        We understand this may be inconvenient, and we sincerely apologize for any disruption this may cause. We encourage you to:
                    </p>
                    <ul style="margin: 0 0 10px 0; padding-left: 20px; color: #475569;">
                        <li style="margin-bottom: 6px;">Review the reason stated above and ensure your payment proof clearly shows the correct amount, reference number, and date.</li>
                        <li style="margin-bottom: 6px;">Submit a new, valid proof of payment by visiting your booking page.</li>
                        <li style="margin-bottom: 6px;">Contact our support team if you believe this was an error or if you need further assistance.</li>
                    </ul>
                </td>
            </tr>

            <!-- CTA Button -->
            <tr>
                <td style="padding: 4px 32px 28px 32px; text-align: center;">
                    <a href="{{ $bookingUrl }}" style="display: inline-block; background: linear-gradient(135deg, #ee018d 0%, #b1015d 100%); color: #ffffff; font-weight: 700; font-size: 15px; text-decoration: none; padding: 14px 32px; border-radius: 9999px; letter-spacing: 0.3px;">
                        View My Booking &rarr;
                    </a>
                    <p style="margin: 12px 0 0 0; font-size: 12px; color: #94a3b8;">
                        Or copy and paste this link: <span style="color: #0369a1; word-break: break-all;">{{ $bookingUrl }}</span>
                    </p>
                </td>
            </tr>

            <!-- Support Note -->
            <tr>
                <td style="padding: 4px 32px 20px 32px;">
                    <div style="background-color: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px; padding: 14px 18px; font-size: 13px; color: #0369a1;">
                        <strong>Need help?</strong> Our team is ready to assist you. Please reply to this email or message us with your transaction number <strong>{{ $booking->transaction_number }}</strong> and we will get back to you as soon as possible.
                    </div>
                </td>
            </tr>

            <!-- Footer -->
            <tr>
                <td style="padding: 24px 32px; background-color: #f1f5f9; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; text-align: center;">
                    <p style="margin: 0 0 6px 0; font-weight: 700; color: #334155;">
                        Amiga Gracia Travel Services
                    </p>
                    <p style="margin: 0 0 10px 0;">
                        Kay Amiga, Hassle Free Ka! Accredited Ticketing &amp; Travel Agency
                    </p>
                    <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                        This email was sent regarding Booking #{{ $booking->transaction_number }}. Please do not reply if this was not your booking.
                    </p>
                </td>
            </tr>
        </table>
    </body>
</html>
