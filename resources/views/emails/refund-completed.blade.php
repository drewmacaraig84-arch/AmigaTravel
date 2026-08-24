<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Refund Processed & Disbursed - Amiga Travel</title>
    </head>
    <body style="font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #1e293b; margin: 0; padding: 24px; background-color: #f8fafc;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width: 650px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <!-- Header with Logo & Brand -->
            <tr>
                <td style="padding: 28px 32px 20px 32px; background: linear-gradient(135deg, #064e3b 0%, #047857 100%); text-align: center;">
                    <img src="{{ isset($message) && file_exists(public_path('images/amiga_logo_white_outline.png')) ? $message->embed(public_path('images/amiga_logo_white_outline.png')) : (file_exists(public_path('images/amiga-logo-transparent.png')) && isset($message) ? $message->embed(public_path('images/amiga-logo-transparent.png')) : 'https://www.amigagracia.com/images/amiga-logo-transparent.png') }}" alt="Amiga Gracia Travel Services" style="max-width: 200px; height: auto; margin-bottom: 12px;" />
                    <div style="font-size: 14px; font-weight: 700; letter-spacing: 1.5px; color: #a7f3d0; text-transform: uppercase;">
                        Refund & Disbursement Confirmation
                    </div>
                </td>
            </tr>

            <!-- Status Banner -->
            <tr>
                <td style="padding: 24px 32px 12px 32px; text-align: center;">
                    <div style="display: inline-block; padding: 8px 18px; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 9999px; margin-bottom: 16px;">
                        <span style="color: #166534; font-weight: 700; font-size: 13px; letter-spacing: 0.5px; text-transform: uppercase;">
                            ✓ Refund Disbursed
                        </span>
                    </div>
                    <h1 style="margin: 0 0 10px 0; font-size: 24px; font-weight: 800; color: #0f172a;">
                        Your Refund Has Been Disbursed
                    </h1>
                    <p style="margin: 0; font-size: 15px; color: #475569;">
                        Dear <strong>{{ $booking->client_name }}</strong>, we are writing to confirm that the refund for your booking (Transaction <strong>{{ $booking->transaction_number }}</strong>) has been verified and successfully disbursed to your designated account.
                    </p>
                </td>
            </tr>

            <!-- Summary Details -->
            @php
                $breakdown = $booking->getProcessedRefundBreakdown();
            @endphp
            <tr>
                <td style="padding: 16px 32px;">
                    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px;">
                        <div style="font-size: 12px; font-weight: 700; color: #047857; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px;">
                            📋 Refund Details
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
                                <td style="padding: 8px 0; color: #64748b;">Disbursed To:</td>
                                <td style="padding: 8px 0; text-align: right; font-weight: 700; color: #0f172a;">
                                    {{ $booking->refund_destination ?: 'Designated Account' }}
                                </td>
                            </tr>
                            @if(filled($booking->refund_reference))
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 8px 0; color: #64748b;">Transfer Ref No.:</td>
                                <td style="padding: 8px 0; text-align: right; font-weight: 700; color: #047857; font-family: monospace;">
                                    {{ $booking->refund_reference }}
                                </td>
                            </tr>
                            @endif
                        </table>

                        <!-- Itemized Financial Breakdown -->
                        <div style="margin-top: 18px; padding-top: 14px; border-top: 1px dashed #cbd5e1;">
                            <div style="font-size: 12px; font-weight: 700; color: #334155; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                                💵 Refund Calculation Breakdown
                            </div>
                            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="font-size: 13px; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 4px 0; color: #475569;">Original Booking Total:</td>
                                    <td style="padding: 4px 0; text-align: right; font-weight: 600; color: #0f172a;">
                                        ₱{{ number_format($breakdown['original_amount'], 2) }}
                                    </td>
                                </tr>
                                @if($breakdown['web_admin_fee'] > 0)
                                <tr>
                                    <td style="padding: 4px 0; color: #64748b; font-size: 12.5px;">Less: Non-Refundable Web Admin Fee ({{ $breakdown['pax_count'] }} pax):</td>
                                    <td style="padding: 4px 0; text-align: right; color: #64748b; font-size: 12.5px;">
                                        -₱{{ number_format($breakdown['web_admin_fee'], 2) }}
                                    </td>
                                </tr>
                                @endif
                                @if($breakdown['transaction_fee'] > 0)
                                <tr>
                                    <td style="padding: 4px 0; color: #64748b; font-size: 12.5px;">Less: Non-Refundable Transaction Fee ({{ $breakdown['pax_count'] }} pax):</td>
                                    <td style="padding: 4px 0; text-align: right; color: #64748b; font-size: 12.5px;">
                                        -₱{{ number_format($breakdown['transaction_fee'], 2) }}
                                    </td>
                                </tr>
                                @endif
                                @if($breakdown['surcharge_amount'] > 0)
                                <tr>
                                    <td style="padding: 4px 0; color: #e11d48; font-size: 12.5px;">Less: {{ number_format($breakdown['surcharge_pct'], 0) }}% Cancellation / Surcharge:</td>
                                    <td style="padding: 4px 0; text-align: right; color: #e11d48; font-size: 12.5px;">
                                        -₱{{ number_format($breakdown['surcharge_amount'], 2) }}
                                    </td>
                                </tr>
                                @elseif($breakdown['total_deductions'] > 0 && $breakdown['web_admin_fee'] == 0 && $breakdown['transaction_fee'] == 0)
                                <tr>
                                    <td style="padding: 4px 0; color: #e11d48; font-size: 12.5px;">Less: Service &amp; Processing Deductions:</td>
                                    <td style="padding: 4px 0; text-align: right; color: #e11d48; font-size: 12.5px;">
                                        -₱{{ number_format($breakdown['total_deductions'], 2) }}
                                    </td>
                                </tr>
                                @endif
                                @if($breakdown['is_service_disruption'])
                                <tr>
                                    <td style="padding: 4px 0; color: #047857; font-size: 12.5px;">Service Disruption Waiver:</td>
                                    <td style="padding: 4px 0; text-align: right; color: #047857; font-size: 12.5px; font-weight: 600;">
                                        100% Full Refund (Fees Waived)
                                    </td>
                                </tr>
                                @endif
                                <tr style="border-top: 1.5px solid #047857;">
                                    <td style="padding: 10px 0 2px 0; color: #0f172a; font-weight: 700; font-size: 14px;">Total Refund Disbursed:</td>
                                    <td style="padding: 10px 0 2px 0; text-align: right; font-weight: 800; color: #047857; font-size: 18px;">
                                        ₱{{ number_format($breakdown['net_refund_amount'], 2) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </td>
            </tr>

            <!-- Attached Documents Note -->
            <tr>
                <td style="padding: 0 32px 24px 32px;">
                    <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; padding: 14px 18px; font-size: 13px; color: #166534;">
                        📎 <strong>Attached:</strong> Your official <strong>Refund Acknowledgement PDF</strong> and proof of transfer receipt are attached to this email.
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
                        If you have questions regarding your refund, contact us at support@amigatravel.com.
                    </p>
                </td>
            </tr>
        </table>
    </body>
</html>
