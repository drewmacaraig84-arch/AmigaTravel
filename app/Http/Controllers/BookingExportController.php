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
        $query = Booking::with([
            'transaction',
            'schedule.ferryRoute',
            'returnSchedule.ferryRoute',
            'passengers.discount',
            'accommodations',
        ]);

        $fromDate = request()->input('from_date') ?? request()->input('start') ?? request()->input('startDate') ?? request()->input('from');
        $toDate   = request()->input('to_date') ?? request()->input('end') ?? request()->input('endDate') ?? request()->input('to');

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $bookings = $query->get();

        $refundedBookings = $bookings->filter(function ($booking) {
            return (in_array($booking->status, [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED]) && (float) $booking->refund_amount > 0)
                || $booking->passengers->contains(fn ($p) => (float) $p->refund_amount > 0 || in_array($p->status, ['refund_pending', 'refunded']));
        });

        $rebookedBookings = $bookings->filter(function ($booking) use ($refundedBookings) {
            return ($booking->is_rebooked || filled($booking->rebooking_status) || $booking->passengers->contains(fn ($p) => $p->is_rebooked || in_array($p->status, ['rebooked', 'rebooking_pending'])))
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
        $fromDate = request()->input('from_date') ?? request()->input('start') ?? request()->input('startDate') ?? request()->input('from');
        $toDate   = request()->input('to_date') ?? request()->input('end') ?? request()->input('endDate') ?? request()->input('to');

        $grouped = $this->getGroupedBookings();

        $html = view('exports.bookings-pdf', [
            'groupedBookings' => $grouped,
            'fromDate'        => $fromDate,
            'toDate'          => $toDate,
            'generatedAt'     => now(),
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('legal', 'landscape');
        $dompdf->render();

        $output = $dompdf->output();

        $headers = [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => ($inline ? 'inline' : 'attachment') . '; filename="' . $filename . '"',
        ];

        return new Response($output, 200, $headers);
    }
}
