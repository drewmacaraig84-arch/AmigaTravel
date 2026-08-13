<?php

namespace App\Http\Controllers;

use App\Exports\BookingsExport;
use App\Models\Booking;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class BookingExportController extends Controller
{
    public function exportPdf()
    {
        return $this->generatePdfResponse('bookings.pdf', false);
    }

    public function exportCsv()
    {
        $grouped = $this->getGroupedBookings();

        return Excel::download(new BookingsExport($grouped), 'bookings.xlsx');
    }

    public function exportPrint()
    {
        return $this->generatePdfResponse('bookings.pdf', true);
    }

    protected function getGroupedBookings(): array
    {
        $bookings = Booking::with(['transaction', 'schedule.ferryRoute', 'passengers.discount'])->get();

        $refundedBookings = $bookings->filter(function ($booking) {
            return in_array($booking->status, [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED]) 
                && $booking->refund_amount > 0;
        });

        $rebookedBookings = $bookings->filter(function ($booking) use ($refundedBookings) {
            return ($booking->is_rebooked || filled($booking->rebooking_status))
                && ! $refundedBookings->contains('id', $booking->id);
        });

        $verifiedBookings = $bookings->filter(function ($booking) use ($refundedBookings, $rebookedBookings) {
            return $booking->status === Booking::STATUS_CONFIRMED 
                && ! $refundedBookings->contains('id', $booking->id)
                && ! $rebookedBookings->contains('id', $booking->id);
        });

        $pendingBookings = $bookings->filter(function ($booking) use ($refundedBookings, $rebookedBookings) {
            return $booking->status === Booking::STATUS_PENDING 
                && ! $refundedBookings->contains('id', $booking->id)
                && ! $rebookedBookings->contains('id', $booking->id);
        });

        $cancelledBookings = $bookings->filter(function ($booking) use ($refundedBookings, $rebookedBookings) {
            return in_array($booking->status, [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED]) 
                && ! $refundedBookings->contains('id', $booking->id)
                && ! $rebookedBookings->contains('id', $booking->id);
        });

        return [
            'Refunded Bookings'  => $refundedBookings,
            'Verified Bookings'  => $verifiedBookings,
            'Rebooked Bookings'  => $rebookedBookings,
            'Pending Bookings'   => $pendingBookings,
            'Cancelled Bookings' => $cancelledBookings,
        ];
    }

    protected function generatePdfResponse(string $filename, bool $inline = false): Response
    {
        $grouped = $this->getGroupedBookings();

        $html = view('exports.bookings-pdf', [
            'groupedBookings' => $grouped,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $output = $dompdf->output();

        $headers = [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => ($inline ? 'inline' : 'attachment') . '; filename="' . $filename . '"',
        ];

        return new Response($output, 200, $headers);
    }
}
