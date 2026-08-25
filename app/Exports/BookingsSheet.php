<?php

namespace App\Exports;

use App\Models\Booking;
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
    protected $rowCount = 0;

    public function __construct(string $title, Collection $bookings)
    {
        $this->title = $title;
        $this->bookings = $bookings;
    }

    public function collection()
    {
        $rows = collect();
        $totals = [
            'baseFare' => 0, 'accFee' => 0, 'vehicleFee' => 0, 'baggageFee' => 0,
            'adminFee' => 0, 'transactionFee' => 0, 'hotelFee' => 0,
            'revalFee' => 0, 'rateDiff' => 0, 'surcharge' => 0,
            'cancellationFee' => 0, 'passengerDiscount' => 0, 'voucherDiscount' => 0,
            'pointsDiscount' => 0, 'totalAmount' => 0,
        ];

        foreach ($this->bookings as $booking) {
            $passengers = $booking->passengers->sortBy('item_number');

            $rebookingFee = 0;
            $revalFee = 0.0;
            $rateDiff = 0.0;
            $surcharge = 0.0;

            if ($booking->transaction && (float) $booking->transaction->rebooking_fee > 0) {
                $notes = $booking->disruption_notes ? json_decode($booking->disruption_notes, true) : [];
                $surcharge = (float) ($notes['surcharge'] ?? 0);
                $revalFee = (float) ($notes['revalidation_fee'] ?? 0);
                $rateDiff = (float) ($notes['rate_diff'] ?? 0);
                if ($surcharge > 0 || $revalFee > 0 || $rateDiff > 0) {
                    $rebookingFee = $surcharge + $revalFee + $rateDiff;
                } else {
                    $rebookingFee = (float) $booking->transaction->rebooking_fee;
                    $revalFee = $rebookingFee;
                }
            }

            $settings = \App\Models\PaymentSetting::current();
            $calcHotelFee = $booking->accommodations->count() > 0 ? (float) $settings->fee_per_accommodation : 0;

            if ($passengers->isEmpty()) {
                $matches = false;
                $isPendingRefund = $booking->status === 'refund_pending' || $booking->refund_status === 'pending';
                $isPendingRebook = $booking->rebooking_status === 'pending' || $booking->status === 'pending_rebooking';

                if ($this->title === 'Verified Bookings' && $booking->status === 'confirmed' && ! $booking->is_rebooked) {
                    $matches = true;
                } elseif ($this->title === 'Refunded Bookings' && in_array($booking->status, ['cancelled', 'operator_cancelled', 'refunded']) && $booking->refund_amount > 0 && ! $isPendingRefund) {
                    $matches = true;
                } elseif ($this->title === 'Pending Refund Bookings' && $isPendingRefund && $booking->refund_amount > 0) {
                    $matches = true;
                } elseif ($this->title === 'Rebooked Bookings' && (($booking->is_rebooked || in_array($booking->rebooking_status, ['verified', 'approved', 'completed'], true) || in_array($booking->status, ['rebooked', 'operator_rebooking'])) && ! $isPendingRebook)) {
                    $matches = true;
                } elseif ($this->title === 'Pending Bookings' && ($booking->status === 'pending' || $isPendingRebook)) {
                    $matches = true;
                } elseif ($this->title === 'Cancelled Bookings' && in_array($booking->status, ['cancelled', 'operator_cancelled']) && $booking->refund_amount <= 0 && ! $isPendingRefund) {
                    $matches = true;
                }

                if (! $matches) {
                    continue;
                }

                $bRevalFee = ($this->title === 'Rebooked Bookings' ? $revalFee : 0.0);
                $bRateDiff = ($this->title === 'Rebooked Bookings' ? $rateDiff : 0.0);
                $bSurcharge = ($this->title === 'Rebooked Bookings' ? $surcharge : 0.0);

                if ($this->title === 'Rebooked Bookings') {
                    $bTotal = (float) ($bRevalFee + $bRateDiff + $bSurcharge);
                } elseif ($this->title === 'Refunded Bookings') {
                    $origTotal = (float) ($booking->transaction?->amount_paid ?: $booking->total_price);
                    $refAmount = (float) $booking->refund_amount;
                    $bTotal = max(0.0, (float) ($booking->cancellation_fee > 0 ? $booking->cancellation_fee : ($origTotal - $refAmount)));
                } elseif ($this->title === 'Pending Refund Bookings') {
                    $bTotal = (float) $booking->refund_amount;
                } else {
                    $bTotal = (float) ($booking->transaction?->amount_paid ?: $booking->total_price);
                    if (in_array($booking->status, ['cancelled', 'operator_cancelled']) && $booking->refund_amount > 0) {
                        $bTotal = (float) $booking->refund_amount;
                    }
                }

                $bTcPrice = (float) ($booking->transportClasses ? $booking->transportClasses->sum(fn($tc) => (float)($tc->pivot->price ?? 0)) : 0);
                $bSchedAcc = (float) ($booking->schedule_accommodation_price ?? 0) + (float) ($booking->return_schedule_accommodation_price ?? 0);
                $bAccFee = $bTcPrice + $bSchedAcc;
                $bBaseFare = max(0.0, (float)($booking->schedule_price ?? 0) + (float)($booking->return_schedule_price ?? 0));
                if ($bBaseFare <= 0 && $bTotal > 0 && $bAccFee <= 0) {
                    $bBaseFare = max(0.0, $bTotal - (float)($booking->vehicle_price ?? 0));
                }

                $fin = [
                    'baseFare' => $bBaseFare, 'accFee' => $bAccFee, 'vehicleFee' => (float) ($booking->vehicle_price ?? 0),
                    'baggageFee' => 0.0, 'adminFee' => 0.0, 'transactionFee' => 0.0, 'hotelFee' => 0.0,
                    'revalFee' => (float) $bRevalFee, 'rateDiff' => (float) $bRateDiff, 'surcharge' => (float) $bSurcharge,
                    'cancellationFee' => (float) $booking->cancellation_fee,
                    'passengerDiscount' => 0.0, 'voucherDiscount' => (float) $booking->voucher_discount_amount,
                    'pointsDiscount' => (float) $booking->points_discount, 'totalAmount' => (float) $bTotal,
                ];

                $rowObj = (object) array_merge([
                    'booking' => $booking,
                    'passenger' => null,
                    'item_label' => 'Item 1',
                    'passenger_name' => $booking->client_name,
                    'status_label' => ucfirst(str_replace('_', ' ', $booking->status)),
                    'discount_type' => '-',
                    'voucher_code' => $booking->voucher_code ?? '-',
                    'points_used' => $booking->points_redeemed ?? '-',
                ], $fin);

                $rows->push($rowObj);
                foreach ($totals as $k => $v) {
                    $totals[$k] += $fin[$k];
                }
            } else {
                foreach ($passengers as $pIndex => $p) {
                    $matches = false;
                    if ($this->title === 'Verified Bookings') {
                        $matches = $p->isActiveBookingItem() && ! $p->isRebookingHistoryItem() && in_array($p->status, ['confirmed']);
                    } elseif ($this->title === 'Refunded Bookings') {
                        $matches = ($p->status === 'refunded' || $p->refund_status === 'completed' || (in_array($p->status, ['cancelled', 'operator_cancelled']) && (float) $p->refund_amount > 0 && $p->refund_status !== 'pending')) && ! $p->isRefundPending();
                    } elseif ($this->title === 'Pending Refund Bookings') {
                        $matches = $p->status === 'refund_pending' || $p->refund_status === 'pending' || $p->isRefundPending();
                    } elseif ($this->title === 'Rebooked Bookings') {
                        $matches = $p->isRebooked() && ! $p->isRebookingPending();
                    } elseif ($this->title === 'Pending Bookings') {
                        $matches = ($p->status === 'pending' && ! $p->isRebooked() && ! $p->isRefundItem()) || $p->isRebookingPending();
                    } elseif ($this->title === 'Cancelled Bookings') {
                        $matches = $p->isCancelled() && (float) $p->refund_amount <= 0 && ! $p->isRefundPending() && $p->refund_status !== 'pending';
                    }

                    if (! $matches) {
                        continue;
                    }

                    $pBaseFare = $p->getEffectiveFareAmount();
                    $pAccFee = $p->getEffectiveAccommodationAmount();
                    $pVehFee = ($pIndex === 0 && $booking->has_vehicle ? (float) $booking->vehicle_price : 0.0);
                    $pBagFee = (float) $p->extra_baggage_price;
                    $pAdminFee = $p->getEffectiveWebAdminFee();
                    $pTxnFee = $p->getEffectiveTransactionFee();
                    $pHotelFee = ($pIndex === 0 ? $calcHotelFee : 0.0);

                    $pRevalFee = ($this->title === 'Rebooked Bookings' && $pIndex === 0 ? (float) $revalFee : 0.0);
                    $pRateDiff = ($this->title === 'Rebooked Bookings' && $pIndex === 0 ? (float) $rateDiff : 0.0);
                    $pSurcharge = ($this->title === 'Rebooked Bookings' && $pIndex === 0 ? (float) $surcharge : 0.0);
                    $pRebookFee = $pRevalFee + $pRateDiff + $pSurcharge;

                    $pCancelFee = ((float) $p->cancellation_fee > 0 ? (float) $p->cancellation_fee : ($pIndex === 0 ? (float) $booking->cancellation_fee : 0.0));
                    $pPaxDisc = (float) $p->discount_amount;
                    $pVoucherDisc = (float) $p->voucher_discount_share;
                    $pPointsDisc = (float) $p->points_discount_share;
                    
                    if ($this->title === 'Rebooked Bookings') {
                        $pItemTotal = (float) $pRebookFee;
                    } elseif ($this->title === 'Refunded Bookings') {
                        $refAmount = (float) $p->refund_amount;
                        if ($refAmount <= 0 && in_array($booking->status, ['cancelled', 'operator_cancelled']) && (float) $booking->refund_amount > 0) {
                            $refAmount = (float) ($booking->refund_amount / max(1, $passengers->count()));
                        }
                        $grossTotal = $p->getEffectiveItemTotal() + $pVehFee + $pHotelFee;
                        $pItemTotal = max(0.0, (float) ($pCancelFee > 0 ? $pCancelFee : ($grossTotal - $refAmount)));
                    } elseif ($this->title === 'Pending Refund Bookings') {
                        $pItemTotal = (float) $p->refund_amount > 0 ? (float) $p->refund_amount : (float) $p->getEffectiveItemTotal();
                    } else {
                        $pItemTotal = $p->getEffectiveItemTotal() + $pVehFee + $pHotelFee + $pRebookFee;
                        if ((float) $p->refund_amount > 0) {
                            $pItemTotal = (float) $p->refund_amount;
                        } elseif (in_array($booking->status, ['cancelled', 'operator_cancelled']) && (float) $booking->refund_amount > 0 && (float) $p->refund_amount <= 0) {
                            $pItemTotal = (float) ($booking->refund_amount / max(1, $passengers->count()));
                        }
                    }

                    $fin = [
                        'baseFare' => $pBaseFare, 'accFee' => $pAccFee, 'vehicleFee' => $pVehFee,
                        'baggageFee' => $pBagFee, 'adminFee' => $pAdminFee, 'transactionFee' => $pTxnFee,
                        'hotelFee' => $pHotelFee,
                        'revalFee' => $pRevalFee, 'rateDiff' => $pRateDiff, 'surcharge' => $pSurcharge,
                        'cancellationFee' => $pCancelFee,
                        'passengerDiscount' => $pPaxDisc, 'voucherDiscount' => $pVoucherDisc,
                        'pointsDiscount' => $pPointsDisc, 'totalAmount' => $pItemTotal,
                    ];

                    $rowObj = (object) array_merge([
                        'booking' => $booking,
                        'passenger' => $p,
                        'item_label' => 'Item ' . ($p->item_number ?? ($pIndex + 1)),
                        'passenger_name' => $p->name ?? $booking->client_name,
                        'status_label' => $p->getStatusLabel(),
                        'discount_type' => $p->discount?->name ?? '-',
                        'voucher_code' => ($pIndex === 0 ? ($booking->voucher_code ?? '-') : '-'),
                        'points_used' => ($pIndex === 0 ? ($booking->points_redeemed ?? '-') : '-'),
                    ], $fin);

                    $rows->push($rowObj);
                    foreach ($totals as $k => $v) {
                        $totals[$k] += $fin[$k];
                    }
                }
            }
        }

        if ($rows->count() > 0) {
            $rows->push((object) array_merge(['is_total_row' => true], $totals));
        }

        $this->rowCount = $rows->count();

        return $rows;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return [
            'Transaction #', 'Item No.', 'Passenger Name', 'Contact #', 'Origin', 'Destination', 'Departure Date', 'Return Date',
            'Mode', 'Operator', 'Booking Status', 'Ref # (Payment)',
            'Base Fare (₱)', 'Accommodation / Class Fee (₱)', 'Vehicle Freight Fee (₱)', 'Extra Baggage Fee (₱)',
            'Web Admin Fee (₱)', 'Transaction Fee (₱)', 'Hotel Service Fee (₱)', 'Revalidation Fee (₱)', 'Fare Difference (₱)', 'Surcharge (₱)', 'Cancellation Deduction (₱)',
            'Pass. Discount Type', 'Passenger Discount (₱)', 'Voucher Code', 'Voucher Discount (₱)',
            'Gracia Points Used', 'Points Discount (₱)', 'TOTAL AMOUNT (₱)',
        ];
    }

    public function map($row): array
    {
        if (isset($row->is_total_row)) {
            return [
                '', '', '', '', '', '', '', '', '', '', '', 'GRAND TOTAL',
                number_format($row->baseFare, 2),
                number_format($row->accFee, 2),
                number_format($row->vehicleFee, 2),
                number_format($row->baggageFee, 2),
                number_format($row->adminFee, 2),
                number_format($row->transactionFee, 2),
                number_format($row->hotelFee, 2),
                number_format($row->revalFee, 2),
                number_format($row->rateDiff, 2),
                number_format($row->surcharge, 2),
                number_format($row->cancellationFee, 2),
                '', // Pass discount type
                number_format($row->passengerDiscount, 2),
                '', // Voucher code
                number_format($row->voucherDiscount, 2),
                '', // Points used
                number_format($row->pointsDiscount, 2),
                number_format($row->totalAmount, 2),
            ];
        }

        $booking = $row->booking;
        $ferryRoute = $booking->schedule?->ferryRoute;

        return [
            $booking->transaction_number,
            $row->item_label,
            $row->passenger_name,
            $booking->client_phone,
            $booking->origin,
            $booking->destination,
            $booking->departure_date?->format('M d, Y'),
            $booking->return_date?->format('M d, Y') ?? '-',
            $ferryRoute?->mode ?? $booking->schedule_service ?? '-',
            $ferryRoute?->operator ?? '-',
            $row->status_label,
            $booking->transaction?->payment_reference ?? '-',
            
            number_format($row->baseFare, 2),
            number_format($row->accFee, 2),
            number_format($row->vehicleFee, 2),
            number_format($row->baggageFee, 2),
            number_format($row->adminFee, 2),
            number_format($row->transactionFee, 2),
            number_format($row->hotelFee, 2),
            number_format($row->revalFee, 2),
            number_format($row->rateDiff, 2),
            number_format($row->surcharge, 2),
            number_format($row->cancellationFee, 2),
            
            $row->discount_type,
            number_format($row->passengerDiscount, 2),
            $row->voucher_code,
            number_format($row->voucherDiscount, 2),
            $row->points_used,
            number_format($row->pointsDiscount, 2),
            number_format($row->totalAmount, 2),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, 'B' => 15, 'C' => 25, 'D' => 15, 'E' => 15, 'F' => 15, 'G' => 15, 'H' => 15, 'I' => 10, 'J' => 15, 'K' => 20, 'L' => 15,
            'M' => 15, 'N' => 15, 'O' => 15, 'P' => 15, 'Q' => 15, 'R' => 15, 'S' => 15, 'T' => 15, 'U' => 15, 'V' => 15, 'W' => 15,
            'X' => 15, 'Y' => 15, 'Z' => 15, 'AA' => 15, 'AB' => 15, 'AC' => 15, 'AD' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
        if ($this->rowCount > 0) {
            $lastRow = $this->rowCount + 1; 
            $sheet->getStyle($lastRow)->getFont()->setBold(true);
            $sheet->getStyle($lastRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFF2F2F2');
        }
    }
}

