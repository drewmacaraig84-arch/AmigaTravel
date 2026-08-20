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
        $totals = [
            'baseFare' => 0, 'accFee' => 0, 'vehicleFee' => 0, 'baggageFee' => 0,
            'adminFee' => 0, 'transactionFee' => 0, 'hotelFee' => 0, 'rebookingFee' => 0,
            'cancellationFee' => 0, 'passengerDiscount' => 0, 'voucherDiscount' => 0,
            'pointsDiscount' => 0, 'totalAmount' => 0,
        ];

        foreach ($this->bookings as $booking) {
            $fin = $this->extractFinancials($booking);
            foreach ($totals as $k => $v) {
                $totals[$k] += $fin[$k];
            }
        }

        $collection = collect($this->bookings);

        if ($collection->count() > 0) {
            $collection->push((object) array_merge(['is_total_row' => true], $totals));
        }

        return $collection;
    }

    private function extractFinancials($booking) {
        $baseFare = 0;
        $accFee = 0;
        $passengerDiscount = 0;

        foreach ($booking->passengers as $p) {
            if ($p->is_promo) {
                $baseFare += (float) $p->promo_price;
            } else {
                $pDepTicket = (float) ($booking->schedule_price ?? 0);
                $pDepAcc = (float) ($booking->schedule_accommodation_price ?? 0);
                $pRetTicket = (float) ($booking->return_schedule_price ?? 0);
                $pRetAcc = (float) ($booking->return_schedule_accommodation_price ?? 0);
                
                $grossTicket = $pDepTicket + $pRetTicket;
                $grossAcc = $pDepAcc + $pRetAcc;

                $baseFare += $grossTicket;
                $accFee += $grossAcc;

                if ($p->discount) {
                    $multiplier = ((float) $p->discount->percentage / 100);
                    $passengerDiscount += ($grossTicket + $grossAcc) * $multiplier;
                }
            }
        }

        foreach ($booking->transportClasses as $index => $tc) {
            $baseFare += (float) $tc->pivot->price;
        }
        
        foreach ($booking->accommodations as $acc) {
            $accFee += (float) $acc->pivot->price;
        }

        $vehicleFee = $booking->has_vehicle ? (float) $booking->vehicle_price : 0;
        $baggageFee = $booking->has_extra_baggage ? (float) $booking->extra_baggage_price : 0;
        $cancellationFee = (float) $booking->cancellation_fee;
        
        $rebookingFee = 0;
        if ($booking->transaction && (float) $booking->transaction->rebooking_fee > 0) {
            $notes = $booking->disruption_notes ? json_decode($booking->disruption_notes, true) : [];
            $surcharge = (float) ($notes['surcharge'] ?? 0);
            $reval = (float) ($notes['revalidation_fee'] ?? 0);
            $rateDiff = (float) ($notes['rate_diff'] ?? 0);
            if ($surcharge > 0 || $reval > 0 || $rateDiff > 0) {
                $rebookingFee = $surcharge + $reval + $rateDiff;
            } else {
                $rebookingFee = (float) $booking->transaction->rebooking_fee;
            }
        }

        $voucherDiscount = (float) $booking->voucher_discount_amount;
        $pointsDiscount = (float) $booking->points_discount;

        $knownSum = $baseFare + $accFee + $vehicleFee + $baggageFee - $passengerDiscount - $voucherDiscount - $pointsDiscount;
        $fees = max(0, (float) $booking->total_price - $knownSum);
        
        $transactionFee = 0;
        $hotelFee = 0;
        $adminFee = 0;

        if ($fees > 0.01) {
            $settings = \App\Models\PaymentSetting::current();
            $isFerry = stripos($booking->schedule_service ?? '', 'airline') === false;
            $paxCount = max(1, $booking->passengers->count());
            $multiplier = $paxCount + ($isFerry ? $paxCount : 0);
            
            $isShortHaul = $booking->isShortHaul();
            $calcTxFee = $multiplier * $settings->getTransactionFee($isShortHaul);
            $calcHotelFee = $booking->accommodations->count() > 0 ? (float) $settings->fee_per_accommodation : 0;
            
            if ($fees >= $calcTxFee && $calcTxFee > 0) {
                $transactionFee = $calcTxFee;
                $fees -= $calcTxFee;
            }
            if ($fees >= $calcHotelFee && $calcHotelFee > 0) {
                $hotelFee = $calcHotelFee;
                $fees -= $calcHotelFee;
            }
            if ($fees > 0.01) {
                $adminFee = $fees;
            }
        }

        $isRefunded = in_array($booking->status, [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED]) && $booking->refund_amount > 0;
        
        $totalAmount = (float) $booking->total_price;
        if ($isRefunded) {
            // Net revenue retained by Amiga after refund (un-refunded fees + surcharges)
            $totalAmount = max(0, (float) $booking->total_price - (float) $booking->refund_amount);
        } elseif ($rebookingFee > 0) {
            // Include additional rebooking fee collected from customer on reschedule
            $totalAmount = (float) $booking->total_price + $rebookingFee;
        }

        return [
            'baseFare' => $baseFare, 'accFee' => $accFee, 'vehicleFee' => $vehicleFee,
            'baggageFee' => $baggageFee, 'adminFee' => $adminFee, 'transactionFee' => $transactionFee,
            'hotelFee' => $hotelFee, 'rebookingFee' => $rebookingFee, 'cancellationFee' => $cancellationFee,
            'passengerDiscount' => $passengerDiscount, 'voucherDiscount' => $voucherDiscount,
            'pointsDiscount' => $pointsDiscount, 'totalAmount' => $totalAmount,
        ];
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return [
            'Transaction #', 'Client Name', 'Contact #', 'Origin', 'Destination', 'Departure Date', 'Return Date',
            'Mode', 'Operator', 'Booking Status', 'Ref # (Payment)',
            'Base Fare (₱)', 'Accommodation / Class Fee (₱)', 'Vehicle Freight Fee (₱)', 'Extra Baggage Fee (₱)',
            'Web Admin Fee (₱)', 'Transaction Fee (₱)', 'Hotel Service Fee (₱)', 'Rebooking Fee (₱)', 'Cancellation Deduction (₱)',
            'Pass. Discount Type', 'Passenger Discount (₱)', 'Voucher Code', 'Voucher Discount (₱)',
            'Gracia Points Used', 'Points Discount (₱)', 'TOTAL AMOUNT (₱)',
        ];
    }

    public function map($booking): array
    {
        if (isset($booking->is_total_row)) {
            return [
                '', '', '', '', '', '', '', '', '', '', 'GRAND TOTAL',
                number_format($booking->baseFare, 2),
                number_format($booking->accFee, 2),
                number_format($booking->vehicleFee, 2),
                number_format($booking->baggageFee, 2),
                number_format($booking->adminFee, 2),
                number_format($booking->transactionFee, 2),
                number_format($booking->hotelFee, 2),
                number_format($booking->rebookingFee, 2),
                number_format($booking->cancellationFee, 2),
                '', // Pass discount type
                number_format($booking->passengerDiscount, 2),
                '', // Voucher code
                number_format($booking->voucherDiscount, 2),
                '', // Points used
                number_format($booking->pointsDiscount, 2),
                number_format($booking->totalAmount, 2),
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

        $fin = $this->extractFinancials($booking);

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
            $booking->transaction?->payment_reference ?? '-',
            
            number_format($fin['baseFare'], 2),
            number_format($fin['accFee'], 2),
            number_format($fin['vehicleFee'], 2),
            number_format($fin['baggageFee'], 2),
            number_format($fin['adminFee'], 2),
            number_format($fin['transactionFee'], 2),
            number_format($fin['hotelFee'], 2),
            number_format($fin['rebookingFee'], 2),
            number_format($fin['cancellationFee'], 2),
            
            filled($discountTypes) ? $discountTypes : '-',
            number_format($fin['passengerDiscount'], 2),
            filled($booking->voucher_code) ? $booking->voucher_code : '-',
            number_format($fin['voucherDiscount'], 2),
            $booking->points_used > 0 ? number_format($booking->points_used) . ' pts' : '-',
            number_format($fin['pointsDiscount'], 2),
            number_format($fin['totalAmount'], 2),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, 'B' => 25, 'C' => 15, 'D' => 15, 'E' => 15, 'F' => 15, 'G' => 15, 'H' => 10, 'I' => 15, 'J' => 20, 'K' => 15,
            'L' => 15, 'M' => 15, 'N' => 15, 'O' => 15, 'P' => 15, 'Q' => 15, 'R' => 15, 'S' => 15, 'T' => 15, 'U' => 15, 'V' => 15,
            'W' => 15, 'X' => 15, 'Y' => 15, 'Z' => 15, 'AA' => 15,
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

