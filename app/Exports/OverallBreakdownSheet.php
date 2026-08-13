<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Collection;
use App\Models\Booking;

class OverallBreakdownSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $title;
    protected $bookings;

    public function __construct(string $title, Collection $bookings)
    {
        $this->title = $title;
        $this->bookings = $bookings;
    }

    public function array(): array
    {
        $rows = [];
        $total = 0;

        foreach ($this->bookings as $booking) {
            // Check if it was paid
            $isPaid = false;
            
            if ($booking->transaction && in_array($booking->transaction->payment_status, ['paid', 'refunded'])) {
                $isPaid = true;
            } elseif (in_array($booking->status, [Booking::STATUS_CONFIRMED, 'rebooked'])) {
                $isPaid = true;
            } elseif (in_array($booking->status, [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED]) && $booking->refund_amount > 0) {
                $isPaid = true;
            }

            if (! $isPaid) {
                continue;
            }

            $txId = $booking->transaction_number ?? ('AGT-' . $booking->id);

            // 1. Add the positive Verified line
            $rows[] = [
                $txId,
                'Verified',
                (float) $booking->total_price,
            ];
            $total += (float) $booking->total_price;

            // 2. Add negative line if refunded or cancelled
            if (in_array($booking->status, [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED])) {
                if ($booking->refund_amount > 0) {
                    $rows[] = [
                        $txId,
                        'Refunded',
                        -((float) $booking->refund_amount),
                    ];
                    $total -= (float) $booking->refund_amount;
                } else {
                    $rows[] = [
                        $txId,
                        'Cancelled',
                        -((float) $booking->total_price),
                    ];
                    $total -= (float) $booking->total_price;
                }
            }
        }

        // Add empty row
        $rows[] = ['', '', ''];

        // Add total row
        $rows[] = [
            'TOTAL',
            '',
            $total
        ];

        return $rows;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return [
            'Transaction #',
            'Status',
            'Amount',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        return [
            1 => ['font' => ['bold' => true]],
            $highestRow => ['font' => ['bold' => true]],
        ];
    }
}
