<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use App\Models\FerryRoute;
use App\Models\Schedule;
use App\Models\ScheduleAccommodation;
use App\Models\Booking;

class MyPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'My Account';
    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'My Page & Reports';

    protected static ?string $title = 'My Page & Reports';

    protected static ?string $slug = 'my-page';

    protected static string $view = 'filament.pages.my-page';

    public array $stats = [];

    public array $recentBookings = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $user = auth()->user();
        $userId = $user?->id;

        // Staff performance connection: bookings/transactions handled or verified strictly by this user
        $baseQuery = Booking::query()->where(function ($q) use ($userId) {
            $q->where('verified_by_user_id', $userId)
              ->orWhere('refund_processed_by_user_id', $userId)
              ->orWhereHas('transactions', fn ($tq) => $tq->where('verified_by_user_id', $userId))
              ->orWhereHas('passengers', fn ($pq) => $pq->where('verified_by_user_id', $userId)->orWhere('refund_processed_by_user_id', $userId));
        });

        $total = (clone $baseQuery)->count();
        $completed = (clone $baseQuery)->where('status', 'confirmed')->count();
        $pending = (clone $baseQuery)->whereIn('status', ['pending', 'pending_rebooking', 'refund_pending'])->count();
        $cancelled = (clone $baseQuery)->whereIn('status', ['cancelled', 'operator_cancelled'])->count();
        $revenue = (clone $baseQuery)->where('status', 'confirmed')->sum('total_price') ?: 0;

        $completionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 100;

        $this->stats = [
            'total_transactions' => $total,
            'completed' => $completed,
            'pending' => $pending,
            'cancelled' => $cancelled,
            'revenue_handled' => $revenue,
            'completion_rate' => $completionRate,
        ];

        $this->recentBookings = (clone $baseQuery)
            ->with(['schedule.ferryRoute'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function (Booking $booking) {
                $route = $booking->schedule && $booking->schedule->ferryRoute
                    ? "{$booking->schedule->ferryRoute->origin} - {$booking->schedule->ferryRoute->destination}"
                    : ($booking->origin && $booking->destination ? "{$booking->origin} - {$booking->destination}" : 'Trip Reservation');

                $status = strtolower($booking->status ?: 'pending');

                return [
                    'id' => $booking->id,
                    'reference' => $booking->reference_number ?: "BK-{$booking->id}",
                    'client' => $booking->client_name ?: ($booking->client_email ?: 'Client #' . $booking->id),
                    'route' => $route,
                    'status' => $status,
                    'payment_status' => $status === 'confirmed' ? 'paid' : 'pending',
                    'total_amount' => (float) $booking->total_price,
                    'date' => $booking->created_at ? $booking->created_at->format('M d, Y h:i A') : 'N/A',
                ];
            })
            ->all();
    }

    public function getPersonalGroupedBookings(int $userId): array
    {
        $myBookings = Booking::with([
            'transaction',
            'schedule.ferryRoute',
            'returnSchedule.ferryRoute',
            'passengers.discount',
            'accommodations',
        ])
        ->where(function ($q) use ($userId) {
            $q->where('verified_by_user_id', $userId)
              ->orWhere('refund_processed_by_user_id', $userId)
              ->orWhereHas('transactions', fn ($tq) => $tq->where('verified_by_user_id', $userId))
              ->orWhereHas('passengers', fn ($pq) => $pq->where('verified_by_user_id', $userId)->orWhere('refund_processed_by_user_id', $userId));
        })
        ->latest()
        ->get();

        $confirmed = $myBookings->filter(function ($b) {
            $isRebooked = $b->is_rebooked || in_array($b->status, ['rebooked', 'operator_rebooking']) || $b->rebooking_status === 'approved';
            $isCancelled = in_array($b->status, [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED]);
            $isRefunded = $b->status === 'refunded' || (float) $b->refund_amount > 0;
            return $b->status === Booking::STATUS_CONFIRMED && ! $isRebooked && ! $isCancelled && ! $isRefunded;
        });

        $rebooked = $myBookings->filter(function ($b) {
            $isPendingRebook = $b->rebooking_status === 'pending' || $b->status === Booking::STATUS_PENDING_REBOOKING;
            if ($isPendingRebook) return false;
            return (bool) $b->is_rebooked 
                || in_array($b->rebooking_status, ['verified', 'approved', 'completed'], true)
                || in_array($b->status, ['rebooked', 'operator_rebooking']);
        });

        $refunded = $myBookings->filter(function ($b) {
            $isPendingRefund = $b->status === 'refund_pending' || $b->refund_status === 'pending';
            return ! $isPendingRefund && ($b->status === 'refunded' || ((float) $b->refund_amount > 0 && in_array($b->status, [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED, 'refunded'])));
        });

        $pendingRebook = $myBookings->filter(function ($b) {
            return $b->rebooking_status === 'pending' || $b->status === Booking::STATUS_PENDING_REBOOKING;
        });

        $pending = $myBookings->filter(function ($b) {
            $isPendingRebook = $b->rebooking_status === 'pending' || $b->status === Booking::STATUS_PENDING_REBOOKING;
            return $b->status === Booking::STATUS_PENDING && ! $isPendingRebook;
        });

        $cancelled = $myBookings->filter(function ($b) {
            return in_array($b->status, [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED]) 
                && (float) $b->refund_amount <= 0;
        });

        return [
            'Confirmed Bookings'  => $confirmed,
            'Rebooked Bookings'   => $rebooked,
            'Refunded Bookings'   => $refunded,
            'Pending Rebook'      => $pendingRebook,
            'Pending Bookings'    => $pending,
            'Cancelled Bookings'  => $cancelled,
        ];
    }

    public function downloadReport(string $type)
    {
        if ($type === 'my_transactions') {
            $user = auth()->user();
            $userId = $user?->id ?? 0;
            $grouped = $this->getPersonalGroupedBookings($userId);
            $filename = "my_handled_transactions_" . now()->format('Y-m-d_H-i-s') . ".xlsx";

            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MyTransactionsExport($grouped), $filename);
        }

        $filename = "{$type}_report_" . now()->format('Y-m-d_H-i-s') . ".csv";

        return response()->streamDownload(function () use ($type) {
            $handle = fopen('php://output', 'w');

            if ($type === 'ferry_routes') {
                fputcsv($handle, ['ID', 'Origin', 'Destination', 'Operator', 'Mode', 'Is Active', 'Created At']);
                foreach (FerryRoute::all() as $row) {
                    fputcsv($handle, [
                        $row->id,
                        $row->origin,
                        $row->destination,
                        $row->operator,
                        $row->mode,
                        $row->is_active ? 'Active' : 'Inactive',
                        $row->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }

            } elseif ($type === 'schedules') {
                fputcsv($handle, ['ID', 'Route', 'Operator', 'Mode', 'Departure Time', 'Arrival Time', 'Return Date', 'Vehicle', 'Amount', 'Availability']);
                $totalAmount = 0;
                foreach (Schedule::with('ferryRoute')->get() as $row) {
                    $totalAmount += (float) $row->price;
                    fputcsv($handle, [
                        $row->id,
                        $row->ferryRoute ? "{$row->ferryRoute->origin} - {$row->ferryRoute->destination}" : 'N/A',
                        $row->ferryRoute?->operator ?? '',
                        $row->ferryRoute?->mode ?? '',
                        $row->formatted_departure,
                        $row->formatted_arrival,
                        '',
                        $row->vehicle_name,
                        number_format($row->price, 2),
                        $row->availability_label ?? 'Available',
                    ]);
                }
                fputcsv($handle, []);
                fputcsv($handle, ['', '', '', '', '', '', '', 'TOTAL AMOUNT', number_format($totalAmount, 2), '']);

            } elseif ($type === 'bookings') {
                fputcsv($handle, [
                    'ID', 'Transaction #', 'Client Name', 'Client Email', 'Contact Number',
                    'Origin', 'Destination', 'Departure Date', 'Return Date',
                    'Mode', 'Operator', 'Payment Status', 'Booking Status',
                    'Amount', 'Payment Reference #',
                    'Voucher Code', 'Voucher Discount (₱)',
                    'Gracia Points Used', 'Created At',
                ]);

                $totalAmount = 0;
                $totalVoucherDiscount = 0;

                foreach (Booking::with(['transaction', 'schedule.ferryRoute'])->latest()->take(500)->get() as $row) {
                    $totalAmount += (float) $row->total_price;
                    $totalVoucherDiscount += (float) ($row->voucher_discount_amount ?? 0);
                    $ferryRoute = $row->schedule?->ferryRoute;
                    fputcsv($handle, [
                        $row->id,
                        $row->transaction_number ?? "BK-{$row->id}",
                        $row->client_name,
                        $row->client_email,
                        $row->client_phone,
                        $row->origin,
                        $row->destination,
                        $row->departure_date?->format('Y-m-d'),
                        $row->return_date?->format('Y-m-d') ?? '',
                        $ferryRoute?->mode ?? $row->schedule_service ?? '',
                        $ferryRoute?->operator ?? '',
                        $row->status === 'confirmed' ? 'Paid' : 'Pending',
                        $row->status,
                        number_format($row->total_price, 2),
                        $row->transaction?->payment_reference ?? '',
                        $row->voucher_code ?? '',
                        $row->voucher_discount_amount > 0 ? number_format($row->voucher_discount_amount, 2) : '',
                        $row->points_used > 0 ? $row->points_used : '',
                        $row->created_at?->format('Y-m-d H:i:s'),
                    ]);
                }

                fputcsv($handle, []);
                fputcsv($handle, [
                    '', '', '', '', '', '', '', '', '', '', '', '',
                    'TOTAL AMOUNT', number_format($totalAmount, 2),
                    '', 'TOTAL VOUCHER DISCOUNT', number_format($totalVoucherDiscount, 2),
                    '', '',
                ]);

            } else {
                fputcsv($handle, ['Report Type Not Found']);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function downloadPdf(string $type)
    {
        $filename = "{$type}_report_" . now()->format('Y-m-d_H-i-s') . ".pdf";

        if ($type === 'my_transactions') {
            $user = auth()->user();
            $userId = $user?->id ?? 0;
            $grouped = $this->getPersonalGroupedBookings($userId);
            $totalCount = collect($grouped)->sum(fn ($col) => $col->count());

            $html = view('exports.my-transactions-pdf', [
                'groupedBookings'   => $grouped,
                'totalHandledCount' => $totalCount,
                'staffName'         => $user?->name ?? 'Staff',
                'staffEmail'        => $user?->email ?? 'N/A',
                'generatedAt'       => now(),
            ])->render();

            return $this->renderPdfResponse($html, $filename, 'legal', 'landscape');

        } elseif ($type === 'bookings') {
            return redirect()->route('bookings.export.pdf');

        } elseif ($type === 'ferry_routes') {
            $routes = FerryRoute::orderBy('origin')->get();
            $html = view('exports.routes-pdf', [
                'routes'     => $routes,
                'exportedBy' => auth()->user()?->name ?? 'Admin',
            ])->render();

            return $this->renderPdfResponse($html, $filename, 'letter', 'portrait');

        } elseif ($type === 'schedules') {
            $schedules = Schedule::with('ferryRoute')->orderBy('departure_time')->get();
            $html = view('exports.schedules-pdf', [
                'schedules'  => $schedules,
                'exportedBy' => auth()->user()?->name ?? 'Admin',
            ])->render();

            return $this->renderPdfResponse($html, $filename, 'legal', 'landscape');
        }

        return redirect()->back();
    }

    protected function renderPdfResponse(string $html, string $filename, string $paper = 'letter', string $orientation = 'portrait')
    {
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        return response()->streamDownload(function () use ($dompdf) {
            echo $dompdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}


