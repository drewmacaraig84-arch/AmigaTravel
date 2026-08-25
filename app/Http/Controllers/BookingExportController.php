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

        $verifiedBookings = $bookings->filter(function ($booking) {
            return ($booking->status === Booking::STATUS_CONFIRMED && ! $booking->is_rebooked && ! in_array($booking->status, [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED]))
                || $booking->passengers->contains(fn ($p) => $p->isActiveBookingItem() && ! $p->isRebookingHistoryItem() && in_array($p->status, ['confirmed']));
        });

        $rebookedBookings = $bookings->filter(function ($booking) {
            $isPendingRebook = $booking->rebooking_status === 'pending' || $booking->status === Booking::STATUS_PENDING_REBOOKING;
            if ($isPendingRebook) {
                return false;
            }
            return (bool) $booking->is_rebooked 
                || in_array($booking->rebooking_status, ['verified', 'approved', 'completed'], true)
                || in_array($booking->status, ['rebooked', 'operator_rebooking'])
                || $booking->passengers->contains(fn ($p) => $p->isRebooked() && ! $p->isRebookingPending());
        });

        $refundedBookings = $bookings->filter(function ($booking) {
            $isPendingRefund = $booking->status === 'refund_pending' || $booking->refund_status === 'pending';
            return (! $isPendingRefund && in_array($booking->status, [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED, 'refunded']) && (float) $booking->refund_amount > 0)
                || $booking->passengers->contains(fn ($p) => ($p->status === 'refunded' || $p->refund_status === 'completed' || (in_array($p->status, ['cancelled', 'operator_cancelled']) && (float) $p->refund_amount > 0 && $p->refund_status !== 'pending')) && ! $p->isRefundPending());
        });

        $pendingRefundBookings = $bookings->filter(function ($booking) {
            $isPendingRefund = $booking->status === 'refund_pending' || $booking->refund_status === 'pending';
            return ($isPendingRefund && (float) $booking->refund_amount > 0)
                || $booking->passengers->contains(fn ($p) => $p->status === 'refund_pending' || $p->refund_status === 'pending' || $p->isRefundPending());
        });

        $pendingBookings = $bookings->filter(function ($booking) {
            $isPendingRebook = $booking->rebooking_status === 'pending' || $booking->status === Booking::STATUS_PENDING_REBOOKING;
            return $booking->status === Booking::STATUS_PENDING 
                || $isPendingRebook
                || $booking->passengers->contains(fn ($p) => $p->status === 'pending' || $p->isRebookingPending());
        });

        $cancelledBookings = $bookings->filter(function ($booking) {
            return in_array($booking->status, [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED]) 
                && (float) $booking->refund_amount <= 0 
                && ($booking->passengers->isEmpty() || $booking->passengers->every(fn($p) => $p->isCancelled() && (float)$p->refund_amount <= 0));
        });

        return [
            'Verified Bookings'       => $verifiedBookings,
            'Rebooked Bookings'       => $rebookedBookings,
            'Refunded Bookings'       => $refundedBookings,
            'Pending Refund Bookings' => $pendingRefundBookings,
            'Pending Bookings'        => $pendingBookings,
            'Cancelled Bookings'      => $cancelledBookings,
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
