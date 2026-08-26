<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    /**
     * Download or stream official receipt PDF for a booking.
     */
    public function download(Request $request, Booking $booking, string $type = 'confirmed')
    {
        $user = Auth::user();
        
        // Ensure user is authorized (admin/staff or the booking owner)
        $isStaff = $user instanceof User && ($user->hasAdminPermission('bookings') || $user->hasAdminPermission('proofs') || $user->role === 'admin');
        $isOwner = $user && $booking->user_id && (int) $user->id === (int) $booking->user_id;

        if (! $isStaff && ! $isOwner) {
            abort(403, 'Unauthorized to view this receipt.');
        }

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

        if ($request->has('preview') || $request->has('inline')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }
}
