<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Rebooking Request Update - Amiga Travel</title>
    </head>
    <body style="font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 24px; background-color: #f8fafc;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 650px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">

            <!-- Header with Brand -->
            <tr>
                <td style="padding: 28px 32px 20px 32px; background: linear-gradient(135deg, #b45309 0%, #d97706 100%); text-align: center;">
                    <img src="{{ isset($message) && file_exists(public_path('images/amiga_logo_white_outline.png')) ? $message->embed(public_path('images/amiga_logo_white_outline.png')) : (file_exists(public_path('images/amiga-logo-transparent.png')) && isset($message) ? $message->embed(public_path('images/amiga-logo-transparent.png')) : 'https://www.amigagracia.com/images/amiga-logo-transparent.png') }}" alt="Amiga Gracia Travel Services" style="max-width: 200px; height: auto; margin-bottom: 12px;" />
                    <div style="font-size: 14px; font-weight: 700; letter-spacing: 1.5px; color: #fef3c7; text-transform: uppercase;">
                        Rebooking Request Update
                    </div>
                </td>
            </tr>

            <!-- Status Banner -->
            <tr>
                <td style="padding: 28px 32px 12px 32px; text-align: center;">
                    <div style="display: inline-block; padding: 8px 18px; background-color: #fffbeb; border: 1px solid #fde68a; border-radius: 9999px; margin-bottom: 16px;">
                        <span style="color: #92400e; font-weight: 700; font-size: 13px; letter-spacing: 0.5px; text-transform: uppercase;">
                            ⚠ Rebooking Request Could Not Be Approved
                        </span>
                    </div>
                    <h1 style="margin: 0 0 12px 0; font-size: 24px; font-weight: 800; color: #0f172a;">
                        Regarding Your Rebooking Request
                    </h1>
                    <p style="margin: 0; font-size: 15px; color: #475569;">
                        Dear <strong>{{ $booking->client_name }}</strong>,<br /><br />
                        Thank you for reaching out to <strong>Amiga Gracia Travel Services</strong>. We value your continued trust in us and sincerely appreciate your patience as we reviewed your rebooking request.
                    </p>
                </td>
            </tr>

            <!-- Message Body -->
            <tr>
                <td style="padding: 12px 32px 8px 32px; font-size: 15px; color: #334155;">
                    <p style="margin: 0 0 14px 0;">
                        After a thorough review of the payment submitted for your rebooking request, we regret to inform you that we were unable to approve the rebooking at this time. The reason is as follows:
                    </p>
                </td>
            </tr>

            <!-- Reason Box -->
            <tr>
                <td style="padding: 4px 32px 16px 32px;">
                    <div style="background-color: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #d97706; border-radius: 10px; padding: 18px 20px;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #92400e; margin-bottom: 8px;">
                            Reason for Non-Approval
                        </div>
                        <div style="font-size: 15px; font-weight: 600; color: #0f172a; margin-bottom: {{ $notes ? '10px' : '0' }};">
                            {{ $reason }}
                        </div>
                        @if($notes)
                        <div style="font-size: 13px; color: #475569; border-top: 1px solid #fde68a; padding-top: 10px; margin-top: 4px;">
                            <strong>Additional Note from our team:</strong><br />{{ $notes }}
                        </div>
                        @endif
                    </div>
                </td>
            </tr>

            <!-- Reassurance Box — IMPORTANT -->
            <tr>
                <td style="padding: 4px 32px 16px 32px;">
                    <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-left: 4px solid #16a34a; border-radius: 10px; padding: 18px 20px;">
                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #15803d; margin-bottom: 8px;">
                            ✓ Your Original Booking Remains Active
                        </div>
                        <div style="font-size: 14px; color: #14532d;">
                            Please be assured that your <strong>original booking is fully preserved and remains confirmed</strong> for your scheduled travel. Your reservation has not been cancelled, modified, or affected by this rebooking request. You may continue to travel as originally planned.
                        </div>
                    </div>
                </td>
            </tr>

            <!-- Booking Reference -->
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
                                <td style="padding: 8px 0; color: #64748b;">Original Travel Date:</td>
                                <td style="padding: 8px 0; text-align: right; font-weight: 700; color: #0369a1;">
                                    {{ \Carbon\Carbon::parse($booking->departure_date)->format('M d, Y') }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 0; color: #64748b;">Original Booking Status:</td>
                                <td style="padding: 8px 0; text-align: right;">
                                    <span style="background-color: #dcfce7; color: #15803d; font-weight: 700; font-size: 12px; padding: 3px 10px; border-radius: 9999px; border: 1px solid #bbf7d0;">Confirmed &amp; Active</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>

            <!-- Next Steps -->
            <tr>
                <td style="padding: 4px 32px 20px 32px; font-size: 14px; color: #334155;">
                    <p style="margin: 0 0 10px 0; font-weight: 700; color: #0f172a;">What are your options?</p>
                    <ul style="margin: 0 0 10px 0; padding-left: 20px; color: #475569;">
                        <li style="margin-bottom: 6px;">You may submit a new rebooking request with a clear and valid proof of payment that addresses the concern raised above.</li>
                        <li style="margin-bottom: 6px;">You may proceed with your original confirmed booking as scheduled.</li>
                        <li style="margin-bottom: 6px;">Should you have questions or need assistance, please do not hesitate to contact our support team — we are happy to help.</li>
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
                        <strong>Need help?</strong> Our team is ready to assist you. Please reply to this email or contact us with your transaction number <strong>{{ $booking->transaction_number }}</strong> and we will be glad to help resolve your concern promptly.
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
