<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Booking Confirmation</title>
    </head>
    <body style="font-family:Arial,sans-serif;line-height:1.6;color:#1f2937;">
        <h1>Booking Confirmed</h1>
        <p>Thank you, {{ $booking->client_name }}.</p>
        <p>Your booking has been created successfully.</p>
        <ul>
            <li><strong>Transaction:</strong> {{ $booking->transaction_number }}</li>
            <li><strong>Origin:</strong> {{ $booking->origin }}</li>
            <li><strong>Destination:</strong> {{ $booking->destination }}</li>
            <li><strong>Departure:</strong> {{ $booking->departure_date }}</li>
            <li><strong>Contact Number:</strong> {{ $booking->client_phone }}</li>
            <li><strong>Return:</strong> {{ $booking->return_date ?? 'One-way' }}</li>
            <li><strong>Adults:</strong> {{ $booking->passengers->where('type', 'adult')->count() }}</li>
            <li><strong>Children:</strong> {{ $booking->passengers->where('type', 'child')->count() }}</li>
            <li><strong>Infants:</strong> {{ $booking->passengers->where('type', 'infant')->count() }}</li>
        </ul>
        <p>
            Your booking has been confirmed. Please find the attached confirmation document or use the link below:
        </p>
        @if(! empty($ticketUrl))
            <p style="text-align:center; margin: 24px 0;">
                <a href="{{ $ticketUrl }}"
                   style="display:inline-block; padding:14px 32px; background:#216417; color:#ffffff;
                          text-decoration:none; border-radius:12px; font-weight:bold; font-size:16px;">
                    View / Download Your Ticket
                </a>
            </p>
            <p style="font-size:12px; color:#64748b; text-align:center;">
                Or copy this link: <span style="word-break: break-all;">{{ $ticketUrl }}</span>
            </p>
        @endif
        @if(! empty($hasTicketAttachment))
            <p style="color:#1e293b; font-weight:600;">
                📎 Ticket_Confirmation.pdf is attached to this email.
            </p>
        @endif
    </body>
</html>
