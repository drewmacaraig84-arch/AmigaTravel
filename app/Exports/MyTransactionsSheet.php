<?php

namespace App\Exports;

use App\Models\Booking;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MyTransactionsSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected string $title;
    protected Collection $bookings;
    protected int $rowCount = 0;

    public function __construct(string $title, Collection $bookings)
    {
        $this->title = $title;
        $this->bookings = $bookings;
    }

    public function collection()
    {
        $this->rowCount = $this->bookings->count();
        return $this->bookings;
    }

    public function title(): string
    {
        return substr($this->title, 0, 31);
    }

    public function headings(): array
    {
        $amountHeader = $this->title === 'Refunded Bookings' ? 'Amount Retained (PHP)' : 'Total Amount (PHP)';

        return [
            'ID',
            'Transaction #',
            'Client Name',
            'Client Email',
            'Contact Number',
            'Origin',
            'Destination',
            'Departure Date',
            'Return Date',
            'Travel Mode',
            'Operator',
            'Status',
            $amountHeader,
            'Payment Reference',
            'Voucher Code',
            'Voucher Discount (PHP)',
            'Points Used',
            'Created At',
            'Processed / Verified At',
        ];
    }

    public function map($booking): array
    {
        $ferryRoute = $booking->schedule?->ferryRoute;
        $processedAt = $booking->verified_at ?? $booking->refund_processed_at ?? $booking->updated_at ?? $booking->created_at;

        if ($this->title === 'Refunded Bookings') {
            $origTotal = (float) ($booking->transaction?->amount_paid ?: $booking->total_price);
            $refundPaid = (float) $booking->refund_amount;
            $cancelFee = (float) $booking->cancellation_fee;
            $displayAmount = max(0.0, $cancelFee > 0 ? $cancelFee : ($origTotal - $refundPaid));
            $statusLabel = 'Refunded';
        } else {
            $displayAmount = (float) $booking->total_price;
            $statusLabel = ucfirst(str_replace('_', ' ', $booking->status ?: 'pending'));
        }

        return [
            $booking->id,
            $booking->transaction_number ?: "BK-{$booking->id}",
            $booking->client_name ?: 'N/A',
            $booking->client_email ?: 'N/A',
            $booking->client_phone ?: 'N/A',
            $booking->origin ?: 'N/A',
            $booking->destination ?: 'N/A',
            $booking->departure_date ? $booking->departure_date->format('Y-m-d') : '',
            $booking->return_date ? $booking->return_date->format('Y-m-d') : '',
            $ferryRoute?->mode ?: ($booking->schedule_service ?: 'Ferry'),
            $ferryRoute?->operator ?: 'N/A',
            $statusLabel,
            $displayAmount,
            $booking->transaction?->payment_reference ?: 'N/A',
            $booking->voucher_code ?: '',
            $booking->voucher_discount_amount > 0 ? (float) $booking->voucher_discount_amount : 0,
            $booking->points_used > 0 ? (int) $booking->points_used : 0,
            $booking->created_at ? $booking->created_at->format('Y-m-d H:i:s') : '',
            $processedAt ? $processedAt->format('Y-m-d H:i:s') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:S1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D97706'], // Amber-600
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(26);

        // Number format for Amount and Discount columns
        if ($this->rowCount > 0) {
            $sheet->getStyle('M2:M' . ($this->rowCount + 1))
                ->getNumberFormat()
                ->setFormatCode('₱#,##0.00');

            $sheet->getStyle('P2:P' . ($this->rowCount + 1))
                ->getNumberFormat()
                ->setFormatCode('₱#,##0.00');

            // Add Total Row at bottom
            $totalRow = $this->rowCount + 2;
            $sheet->setCellValue("L{$totalRow}", 'TOTAL AMOUNT:');
            $sheet->setCellValue("M{$totalRow}", "=SUM(M2:M" . ($this->rowCount + 1) . ")");

            $sheet->getStyle("A{$totalRow}:S{$totalRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '92400E']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FEF3C7'],
                ],
            ]);

            $sheet->getStyle("M{$totalRow}")
                ->getNumberFormat()
                ->setFormatCode('₱#,##0.00');
        }

        return [];
    }
}
