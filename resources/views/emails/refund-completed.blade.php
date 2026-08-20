<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Refund Processed & Disbursed</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937; background-color: #f9fafb; padding: 24px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 32px; border: 1px solid #e5e7eb;">
        <div style="text-align: center; margin-bottom: 24px;">
            <h2 style="color: #ee018d; margin: 0; font-size: 22px;">Amiga Gracia Travel Services</h2>
            <p style="color: #6b7280; font-size: 13px; margin-top: 4px;">Refund & Disbursement Confirmation</p>
        </div>

        <p style="font-size: 15px;">Dear <strong>{{ $booking->client_name }}</strong>,</p>

        <p style="font-size: 14px; color: #374151;">
            We are writing to confirm that the refund for your booking (Transaction <strong>{{ $booking->transaction_number }}</strong>) has been verified and successfully disbursed to your designated account.
        </p>

        <div style="background: #fdf2f8; border: 1px solid #fbcfe8; border-radius: 12px; padding: 20px; margin: 24px 0;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <tr>
                    <td style="padding: 4px 0; color: #6b7280;">Transaction Number:</td>
                    <td style="padding: 4px 0; font-weight: bold; text-align: right; color: #111827;">{{ $booking->transaction_number }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #6b7280;">Route:</td>
                    <td style="padding: 4px 0; font-weight: bold; text-align: right; color: #111827;">{{ $booking->origin }} → {{ $booking->destination }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; color: #6b7280;">Disbursed To:</td>
                    <td style="padding: 4px 0; font-weight: bold; text-align: right; color: #111827;">{{ $booking->refund_destination ?: 'Direct Account' }}</td>
                </tr>
                @if(filled($booking->refund_reference))
                    <tr>
                        <td style="padding: 4px 0; color: #6b7280;">Transfer Reference No.:</td>
                        <td style="padding: 4px 0; font-weight: bold; text-align: right; color: #ee018d;">{{ $booking->refund_reference }}</td>
                    </tr>
                @endif
                <tr style="border-top: 1px solid #f472b6; margin-top: 8px;">
                    <td style="padding: 8px 0 4px 0; font-weight: bold; color: #9d174d; font-size: 14px;">Total Refund Amount:</td>
                    <td style="padding: 8px 0 4px 0; font-weight: bold; text-align: right; color: #9d174d; font-size: 16px;">₱{{ number_format((float) $booking->refund_amount, 2) }}</td>
                </tr>
            </table>
        </div>

        <p style="font-size: 14px; color: #374151;">
            We have attached your official <strong>Refund Acknowledgement PDF</strong> and the proof of transfer receipt to this email for your records. You can also view these at any time via the <strong>My Booking</strong> section on our website or the <strong>Amiga Gracia Mobile App</strong>.
        </p>

        <p style="font-size: 14px; color: #374151;">
            We hope to welcome you on another trip in the future!
        </p>

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; text-align: center;">
            <p style="margin: 0;">Amiga Gracia Travel Services • Authorized Ticketing Operations</p>
            <p style="margin: 4px 0 0 0;">If you have any questions regarding your refund, contact us at <a href="mailto:support@amigatravel.com" style="color: #ee018d; text-decoration: none;">support@amigatravel.com</a>.</p>
        </div>
    </div>
</body>
</html>
