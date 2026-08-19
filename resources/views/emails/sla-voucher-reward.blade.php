<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Verification Guarantee Voucher</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937; background-color: #f9fafb; padding: 24px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 32px; border: 1px solid #e5e7eb;">
        <div style="text-align: center; margin-bottom: 24px;">
            <h2 style="color: #ee018d; margin: 0; font-size: 22px;">Amiga Gracia Travel Services</h2>
            <p style="color: #6b7280; font-size: 13px; margin-top: 4px;">Service Quality & Verification Guarantee</p>
        </div>

        <p style="font-size: 15px;">Dear <strong>{{ $booking->client_name }}</strong>,</p>

        <p style="font-size: 14px; color: #374151;">
            Thank you for booking with us. We hold ourselves to high service standards and aim to verify all bookings promptly. 
            Because your booking (Transaction <strong>{{ $booking->transaction_number }}</strong>) took longer than our guaranteed verification window to process, we are pleased to award you a special non-expiring travel voucher as a token of our appreciation for your patience.
        </p>

        <div style="background: #fdf2f8; border: 2px dashed #f472b6; border-radius: 12px; padding: 20px; text-align: center; margin: 24px 0;">
            <p style="font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: #9d174d; margin: 0 0 6px 0; font-weight: bold;">Your Voucher Code</p>
            <p style="font-size: 24px; font-weight: 800; color: #ee018d; margin: 0; letter-spacing: 2px;">{{ $voucher->code }}</p>
            <p style="font-size: 16px; font-weight: 600; color: #166534; margin: 8px 0 0 0;">Value: ₱{{ number_format($voucher->discount_value, 2) }} Discount</p>
            <p style="font-size: 12px; color: #6b7280; margin: 4px 0 0 0;">No expiration date • Valid on all ferry and flight bookings</p>
        </div>

        <p style="font-size: 14px; color: #374151;">
            You can apply this code during checkout on your next trip via our website or through the <strong>Amiga Gracia Mobile App</strong>.
        </p>

        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #9ca3af; text-align: center;">
            <p style="margin: 0;">Our team is currently finalizing your tickets and will send confirmation shortly.</p>
            <p style="margin: 4px 0 0 0;">If you have any questions, feel free to reach out to our customer support.</p>
        </div>
    </div>
</body>
</html>
