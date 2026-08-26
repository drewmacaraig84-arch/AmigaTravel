<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    /**
     * Stream official receipt PDF inline in the browser for a booking.
     */
    public function view(Request $request, Booking $booking, string $type = 'confirmed')
    {
        $booking->loadMissing([
            'transaction',
            'passengers.discount',
            'transportClasses',
            'schedule.ferryRoute',
            'returnSchedule.ferryRoute',
            'accommodations',
        ]);

        $suffix = match (strtolower($type)) {
            'rebooked' => '-Rebooked',
            'refunded', 'cancelled' => '-Refunded',
            default => '',
        };

        $filename = 'Receipt-' . $booking->transaction_number . $suffix . '.pdf';

        $pdf = Pdf::loadView('pdf.receipt', [
            'booking' => $booking,
            'receiptType' => strtolower($type),
            'isTicket' => false,
        ])->setPaper('a4');

        if ($request->has('download')) {
            return $pdf->download($filename);
        }

        // Stream inline by default so it opens directly in browser tab
        return $pdf->stream($filename);
    }

    /**
     * Alias for download/view
     */
    public function download(Request $request, Booking $booking, string $type = 'confirmed')
    {
        return $this->view($request, $booking, $type);
    }
}
