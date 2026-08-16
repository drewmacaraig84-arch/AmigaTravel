<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Rebooking Verified</title>
    </head>
    <body style="font-family:Arial,sans-serif;line-height:1.6;color:#1f2937;">
        <h1>Rebooking Verified</h1>
        <p>Hi {{ $booking->client_name }},</p>
        <p>Your rebooking request has been verified successfully.</p>
        <p>
            Transaction: <strong>{{ $booking->transaction_number }}</strong><br>
            New departure: <strong>{{ $booking->departure_date }}</strong><br>
            New return: <strong>{{ $booking->return_date ?? 'One-way' }}</strong>
        </p>
        @if(! empty($ticketUrl))
            <p style="text-align:center; margin: 24px 0;">
                <a href="{{ $ticketUrl }}"
                   style="display:inline-block; padding:14px 32px; background:#216417; color:#ffffff;
                          text-decoration:none; border-radius:12px; font-weight:bold; font-size:16px;">
                    View / Download Your Rebooking Ticket
                </a>
            </p>
            <p style="font-size:12px; color:#64748b; text-align:center;">
                Or copy this link: <span style="word-break: break-all;">{{ $ticketUrl }}</span>
            </p>
        @endif
        @if(! empty($hasTicketAttachment))
            <p style="color:#1e293b; font-weight:600;">
                📎 rebooking-confirmation.pdf is attached to this email.
            </p>
        @endif
        <p>Thank you for choosing Amiga Gracia Travel.</p>
    </body>
</html>
