<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Booking Confirmation</title>
    </head>
    <body style="font-family:Arial,sans-serif;line-height:1.6;color:#1f2937; margin:0; padding:24px; background:#f8fafc;">
        <div style="max-width:700px; margin:0 auto; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:24px 28px;">
            <div style="text-align:left; margin-bottom:18px;">
                <img src="{{ isset($message) && file_exists(public_path('images/amiga-logo-transparent.png')) ? $message->embed(public_path('images/amiga-logo-transparent.png')) : 'https://www.amigagracia.com/images/amiga-logo-transparent.png' }}" alt="Amiga Gracia" style="display:block; max-width:220px; height:auto; margin:0 0 8px 0;" />
                <div style="font-size:20px; font-weight:bold; letter-spacing:0.5px; color:#216417; text-transform:uppercase;">E-Acknowledgement</div>
            </div>

            <h1 style="margin:0 0 12px 0; font-size:28px; color:#0f172a;">Booking Confirmed</h1>
            <p style="margin:0 0 8px 0;">Thank you, {{ $booking->client_name }}.</p>
            <p style="margin:0 0 18px 0;">Your booking has been created successfully.</p>
            <ul style="margin:0 0 18px 18px; padding:0;">
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
            <p style="margin:0 0 18px 0;">
                Your booking has been verified and confirmed. Please review your booking details below.
            </p>
            @if(! empty($ticketUrl))
                <div style="text-align:center; margin: 24px 0; padding: 18px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px;">
                    <p style="margin: 0 0 12px 0; font-weight: 600; color: #166534;">Your Official E-Ticket / Confirmation Link:</p>
                    <a href="{{ $ticketUrl }}"
                       style="display:inline-block; padding:14px 32px; background:#216417; color:#ffffff;
                              text-decoration:none; border-radius:12px; font-weight:bold; font-size:16px;">
                        View / Download Your Ticket
                    </a>
                    <p style="font-size:12px; color:#64748b; text-align:center; margin:12px 0 0 0;">
                        Or copy this link: <span style="word-break: break-all;">{{ $ticketUrl }}</span>
                    </p>
                </div>
            @endif

            <div style="margin: 20px 0; padding: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                <p style="margin: 0 0 6px 0; font-weight: bold; color: #334155;">Attached Documents:</p>
                <p style="color:#1e293b; font-size: 14px; margin:0 0 4px 0;">
                    📎 <strong>Payment_Acknowledgement.pdf</strong> (Proof of payment & booking summary)
                </p>
                @if(! empty($hasTicketAttachment))
                    <p style="color:#166534; font-size: 14px; font-weight:600; margin:0;">
                        📎 <strong>Ticket_Confirmation.pdf</strong> (Official E-Ticket / Itinerary from Admin)
                    </p>
                @endif
            </div>
        </div>
    </body>
</html>
