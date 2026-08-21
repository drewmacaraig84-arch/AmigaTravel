<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Payment Proof Received - Amiga Travel</title>
    </head>
    <body style="font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 24px; background-color: #f8fafc;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 650px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <!-- Header with Logo & Brand -->
            <tr>
                <td style="padding: 28px 32px 20px 32px; background: linear-gradient(135deg, #064e3b 0%, #047857 100%); text-align: center;">
                    <img src="{{ isset($message) && file_exists(public_path('images/amiga_logo_white_outline.png')) ? $message->embed(public_path('images/amiga_logo_white_outline.png')) : (file_exists(public_path('images/amiga-logo-transparent.png')) && isset($message) ? $message->embed(public_path('images/amiga-logo-transparent.png')) : 'https://www.amigagracia.com/images/amiga-logo-transparent.png') }}" alt="Amiga Gracia Travel Services" style="max-width: 200px; height: auto; margin-bottom: 12px;" />
                    <div style="font-size: 14px; font-weight: 700; letter-spacing: 1.5px; color: #a7f3d0; text-transform: uppercase;">
                        Payment Proof Acknowledgment
                    </div>
                </td>
            </tr>

            <!-- Status Banner -->
            <tr>
                <td style="padding: 24px 32px 12px 32px; text-align: center;">
                    <div style="display: inline-block; padding: 8px 18px; background-color: #fef3c7; border: 1px solid #fde68a; border-radius: 9999px; margin-bottom: 16px;">
                        <span style="color: #92400e; font-weight: 700; font-size: 13px; letter-spacing: 0.5px; text-transform: uppercase;">
                            ⏳ Status: Under Verification
                        </span>
                    </div>
                    <h1 style="margin: 0 0 10px 0; font-size: 24px; font-weight: 800; color: #0f172a;">
                        We've Received Your Payment Proof!
                    </h1>
                    <p style="margin: 0; font-size: 15px; color: #475569;">
                        Hi <strong>{{ $transaction->booking->client_name }}</strong>, thank you for submitting your payment proof. Our reservations team is currently verifying your payment details.
                    </p>
                </td>
            </tr>

            <!-- Transaction & Booking Summary Card -->
            <tr>
                <td style="padding: 16px 32px;">
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                        <div style="font-size: 12px; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px;">
                            📋 Booking & Payment Details
                        </div>
                        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: 14px; border-collapse: collapse;">
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 0; color: #64748b;">Transaction No.:</td>
                                <td style="padding: 8px 0; text-align: right; font-weight: 700; color: #0f172a; font-family: monospace; font-size: 15px;">
                                    {{ $transaction->booking->transaction_number }}
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 0; color: #64748b;">Route:</td>
                                <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">
                                    {{ $transaction->booking->origin }} &rarr; {{ $transaction->booking->destination }}
                                </td>
                            </tr>
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 0; color: #64748b;">Departure Date:</td>
                                <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">
                                    {{ \Carbon\Carbon::parse($transaction->booking->departure_date)->format('M d, Y') }}
                                </td>
                            </tr>
                            @if($transaction->booking->return_date)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 0; color: #64748b;">Return Date:</td>
                                <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">
                                    {{ \Carbon\Carbon::parse($transaction->booking->return_date)->format('M d, Y') }}
                                </td>
                            </tr>
                            @endif
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 0; color: #64748b;">Passengers:</td>
                                <td style="padding: 8px 0; text-align: right; font-weight: 600; color: #0f172a;">
                                    {{ $transaction->booking->passengers->count() }} Pax
                                </td>
                            </tr>
                            @if($transaction->payment_reference)
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 0; color: #64748b;">Payment Reference:</td>
                                <td style="padding: 8px 0; text-align: right; font-weight: 700; color: #047857; font-family: monospace;">
                                    {{ $transaction->payment_reference }}
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td style="padding: 10px 0 2px 0; color: #0f172a; font-weight: 700; font-size: 15px;">Total Amount:</td>
                                <td style="padding: 10px 0 2px 0; text-align: right; font-weight: 800; color: #047857; font-size: 18px;">
                                    ₱{{ number_format($transaction->booking->total_price, 2) }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>

            <!-- What Happens Next Section -->
            <tr>
                <td style="padding: 12px 32px 28px 32px;">
                    <div style="font-size: 13px; font-weight: 700; color: #334155; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 12px;">
                        What happens next?
                    </div>
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: 13px; color: #475569;">
                        <tr>
                            <td width="28" valign="top" style="padding-bottom: 10px; font-weight: bold; color: #047857;">1.</td>
                            <td style="padding-bottom: 10px;">
                                <strong>Admin Verification:</strong> Our agents cross-check your payment reference and receipt with our accounts.
                            </td>
                        </tr>
                        <tr>
                            <td width="28" valign="top" style="padding-bottom: 10px; font-weight: bold; color: #047857;">2.</td>
                            <td style="padding-bottom: 10px;">
                                <strong>Official E-Ticket Issuance:</strong> Once verified, we will email you your official E-Ticket and travel itinerary.
                            </td>
                        </tr>
                        <tr>
                            <td width="28" valign="top" style="font-weight: bold; color: #047857;">3.</td>
                            <td>
                                <strong>Notification:</strong> We will notify you immediately once your booking has been confirmed.
                            </td>
                        </tr>
                    </table>
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
                        Need urgent assistance? Contact us at support@amigatravel.com or through our Facebook page.
                    </p>
                </td>
            </tr>
        </table>
    </body>
</html>
