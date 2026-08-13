<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;

class BookingsSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    protected $title;
    protected $bookings;

    public function __construct(string $title, Collection $bookings)
    {
        $this->title = $title;
        $this->bookings = $bookings;
    }

    public function collection()
    {
        $totalAmount = $this->bookings->sum('total_price');
        $totalVoucherDiscount = $this->bookings->sum('voucher_discount_amount');
        $totalPointsDiscount = $this->bookings->sum('points_discount');

        $collection = collect($this->bookings);

        if ($collection->count() > 0) {
            $collection->push((object)[
                'is_total_row' => true,
                'total_amount' => $totalAmount,
                'total_voucher' => $totalVoucherDiscount,
                'total_points_discount' => $totalPointsDiscount,
            ]);
        }

        return $collection;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return [
            'Transaction #',
            'Client Name',
            'Contact #',
            'Origin',
            'Destination',
            'Departure Date',
            'Return Date',
            'Mode',
            'Operator',
            'Booking Status',
            'Amount (₱)',
            'Ref # (Payment)',
            'Pass. Discount Type',
            'Voucher Code',
            'Voucher Discount (₱)',
            'Gracia Points Used',
            'Points Discount (₱)',
        ];
    }

    public function map($booking): array
    {
        if (isset($booking->is_total_row)) {
            return [
                '', '', '', '', '', '', '', '', '',
                'TOTAL AMOUNT',
                number_format($booking->total_amount, 2),
                '',
                '',
                'TOTAL VOUCHER DISCOUNT',
                number_format($booking->total_voucher, 2),
                'TOTAL POINTS DISCOUNT',
                number_format($booking->total_points_discount, 2),
            ];
        }

        $ferryRoute = $booking->schedule?->ferryRoute;
        $statusStr = ucfirst(str_replace('_', ' ', $booking->status));
        if (in_array($booking->status, ['cancelled', 'operator_cancelled']) && $booking->refund_amount > 0) {
            $statusStr = 'Refunded';
        }

        $discountTypes = $booking->passengers->filter(function($p) {
            return $p->discount_id !== null && $p->discount;
        })->map(function($p) {
            return $p->discount->name;
        })->unique()->implode(', ');

        return [
            $booking->transaction_number,
            $booking->client_name,
            $booking->client_phone,
            $booking->origin,
            $booking->destination,
            $booking->departure_date?->format('M d, Y'),
            $booking->return_date?->format('M d, Y') ?? '-',
            $ferryRoute?->mode ?? $booking->schedule_service ?? '-',
            $ferryRoute?->operator ?? '-',
            $statusStr,
            number_format($booking->total_price, 2),
            $booking->transaction?->payment_reference ?? '-',
            filled($discountTypes) ? $discountTypes : '-',
            filled($booking->voucher_code) ? $booking->voucher_code : '-',
            $booking->voucher_discount_amount > 0 ? number_format($booking->voucher_discount_amount, 2) : '-',
            $booking->points_used > 0 ? number_format($booking->points_used) . ' pts' : '-',
            $booking->points_discount > 0 ? number_format($booking->points_discount, 2) : '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // Transaction #
            'B' => 25, // Client Name
            'C' => 15, // Contact #
            'D' => 15, // Origin
            'E' => 15, // Destination
            'F' => 15, // Departure Date
            'G' => 15, // Return Date
            'H' => 10, // Mode
            'I' => 15, // Operator
            'J' => 20, // Booking Status
            'K' => 15, // Amount
            'L' => 15, // Ref #
            'M' => 20, // Pass. Discount Type
            'N' => 15, // Voucher Code
            'O' => 20, // Voucher Discount
            'P' => 20, // Gracia Points
            'Q' => 20, // Points Discount (₱)
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getFont()->setBold(true);

        if ($this->bookings->count() > 0) {
            $lastRow = $this->bookings->count() + 2; 
            $sheet->getStyle($lastRow)->getFont()->setBold(true);
            $sheet->getStyle($lastRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFF2F2F2');
        }
    }
}
