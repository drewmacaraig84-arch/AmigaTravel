<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Booking Confirmation - Amiga Travel</title>
    </head>
    <body style="font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 24px; background-color: #f8fafc;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 650px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <!-- Header with Logo & Brand -->
            <tr>
                <td style="padding: 28px 32px 20px 32px; background: linear-gradient(135deg, #064e3b 0%, #047857 100%); text-align: center;">
                    <img src="{{ isset($message) && file_exists(public_path('images/amiga_logo_white_outline.png')) ? $message->embed(public_path('images/amiga_logo_white_outline.png')) : (file_exists(public_path('images/amiga-logo-transparent.png')) && isset($message) ? $message->embed(public_path('images/amiga-logo-transparent.png')) : 'https://www.amigagracia.com/images/amiga-logo-transparent.png') }}" alt="Amiga Gracia Travel Services" style="max-width: 200px; height: auto; margin-bottom: 12px;" />
                    <div style="font-size: 14px; font-weight: 700; letter-spacing: 1.5px; color: #a7f3d0; text-transform: uppercase;">
                        E-Acknowledgement & Confirmation
                    </div>
                </td>
            </tr>

            <!-- Status Banner -->
            <tr>
                <td style="padding: 24px 32px 12px 32px; text-align: center;">
                    <div style="display: inline-block; padding: 8px 18px; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 9999px; margin-bottom: 16px;">
                        <span style="color: #166534; font-weight: 700; font-size: 13px; letter-spacing: 0.5px; text-transform: uppercase;">
                            ✓ Booking Confirmed
                        </span>
                    </div>
                    <h1 style="margin: 0 0 10px 0; font-size: 24px; font-weight: 800; color: #0f172a;">
                        Your Booking is Confirmed!
                    </h1>
                    <p style="margin: 0; font-size: 15px; color: #475569;">
                        Thank you, <strong>{{ $booking->client_name }}</strong>. Your travel reservation has been verified and confirmed.
                    </p>
                </td>
            </tr>

            <!-- Summary Details -->
            <tr>
                <td style="padding: 16px 32px;">
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                        <div style="font-size: 12px; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px;">
                            📋 Itinerary Details
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
                                <td style="padding: 8px 0; color: #64748b;">Departure Date:</td>
                                <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">
                                    {{ \Carbon\Carbon::parse($booking->departure_date)->format('M d, Y') }}
                                </td>
                            </tr>
                            @if($booking->return_date)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 0; color: #64748b;">Return Date:</td>
                                <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">
                                    {{ \Carbon\Carbon::parse($booking->return_date)->format('M d, Y') }}
                                </td>
                            </tr>
                            @endif
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 0; color: #64748b;">Total Passengers:</td>
                                <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">
                                    {{ $booking->passengers->count() }} Pax
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 10px 0 2px 0; color: #0f172a; font-weight: 700; font-size: 15px;">Total Fare Paid:</td>
                                <td style="padding: 10px 0 2px 0; text-align: right; font-weight: 800; color: #047857; font-size: 18px;">
                                    ₱{{ number_format((float) $booking->total_price, 2) }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>

            <!-- Action / Ticket Link -->
            @if(! empty($ticketUrl))
            <tr>
                <td style="padding: 0 32px 20px 32px; text-align: center;">
                    <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 20px;">
                        <p style="margin: 0 0 12px 0; font-weight: 700; color: #166534; font-size: 14px;">
                            Your Confirmed Booking / Travel Itinerary:
                        </p>
                        <a href="{{ $ticketUrl }}" style="display: inline-block; padding: 14px 32px; background-color: #047857; color: #ffffff; text-decoration: none; border-radius: 10px; font-weight: 700; font-size: 15px; box-shadow: 0 2px 4px rgba(4, 120, 87, 0.2);">
                            View / Download Your Ticket
                        </a>
                    </div>
                </td>
            </tr>
            @endif

            <!-- Attached Documents Note -->
            <tr>
                <td style="padding: 0 32px 24px 32px;">
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; font-size: 13px; color: #334155;">
                        <div style="font-weight: 700; margin-bottom: 4px;">📎 Attached Documents:</div>
                        <div>&bull; <strong>Payment_Acknowledgement.pdf</strong> (Proof of payment & booking summary)</div>
                        @if(! empty($hasTicketAttachment))
                        <div>&bull; <strong>Ticket_Confirmation.pdf</strong> (Confirmed Booking / Travel Itinerary)</div>
                        @endif
                    </div>
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
