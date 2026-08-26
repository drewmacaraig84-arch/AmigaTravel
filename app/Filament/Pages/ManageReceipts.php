<?php

namespace App\Filament\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Transaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class ManageReceipts extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && (
            $user->isSuperAdmin()
            || $user->hasAdminPermission('receipts')
            || $user->hasAdminPermission('bookings')
            || $user->hasAdminPermission('transactions')
        );
    }

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 40;
    protected static ?string $navigationLabel = 'Receipts';
    protected static ?string $title = 'Receipts & Acknowledgements';
    protected static string $view = 'filament.pages.manage-receipts';

    public array $selectedReceipts = [];
    public bool $selectAll = false;

    public string $typeFilter = 'all'; // 'all', 'confirmed', 'rebooked', 'refunded'
    public string $search = '';

    public ?string $dateFilter = 'all';
    public ?string $customDateStart = null;
    public ?string $customDateEnd = null;

    public function setTypeFilter(string $filter): void
    {
        $this->typeFilter = $filter;
        $this->selectedReceipts = [];
        $this->selectAll = false;
    }

    #[Computed]
    public function allItems(): Collection
    {
        // 1. Fetch bookings with related models
        $bookings = Booking::query()
            ->with([
                'transaction',
                'transactions',
                'passengers.discount',
                'schedule.ferryRoute',
                'returnSchedule.ferryRoute',
                'transportClasses',
                'accommodations',
            ])
            ->latest('created_at')
            ->get();

        $items = collect();

        foreach ($bookings as $booking) {
            $txNumber = $booking->transaction_number ?? ('BK-' . $booking->id);
            $tx = $booking->transactions->first() ?? $booking->transaction;

            // A. Confirmed Receipt (Paid / Confirmed / Active bookings)
            if (in_array($booking->status, ['confirmed', 'pending', Booking::STATUS_PENDING_REBOOKING]) || ($tx && $tx->payment_status === 'paid')) {
                $statusBadge = ($tx && $tx->payment_status === 'paid') ? 'Paid' : ucfirst($booking->status);
                $statusClass = match ($booking->status) {
                    'confirmed' => 'receipt-badge-confirmed',
                    'pending'   => 'receipt-badge-pending',
                    default     => 'receipt-badge-default',
                };

                $items->push((object) [
                    'id'                   => 'confirmed_' . $booking->id,
                    'composite_id'         => 'confirmed_' . $booking->id,
                    'booking_id'           => $booking->id,
                    'transaction_number'   => $txNumber,
                    'type'                 => 'confirmed',
                    'type_label'           => 'PAYMENT ACKNOWLEDGEMENT RECEIPT',
                    'display_name'         => $txNumber,
                    'status_badge'         => $statusBadge,
                    'status_class'         => $statusClass,
                    'client_name'          => $booking->client_name ?? '—',
                    'client_email'         => $booking->client_email ?? '—',
                    'route'                => ($booking->origin ?? '—') . ' → ' . ($booking->destination ?? '—'),
                    'passenger_count'      => max(1, $booking->passengers->count()),
                    'mode'                 => $booking->getMode(),
                    'operator_name'        => $booking->getOperatorName(),
                    'payment_reference'    => $tx?->payment_reference,
                    'payment_method'       => $tx?->payment_method ? strtoupper($tx->payment_method) : '—',
                    'amount'               => (float) ($booking->total_price ?? 0),
                    'issued_at'            => $booking->created_at,
                    'receipt_download_url' => route('admin.receipts.download', ['booking' => $booking->id, 'type' => 'confirmed']) . '?download=1',
                    'receipt_view_url'     => route('admin.receipts.download', ['booking' => $booking->id, 'type' => 'confirmed']),
                    'view_url'             => BookingResource::getUrl('view', ['record' => $booking]),
                    'booking_model'        => $booking,
                ]);
            }

            // B. Rebooked Official Receipt
            $isRebooked = (bool) ($booking->is_rebooked || filled($booking->rebooking_status) || ($tx && filled($tx->rebooking_proof_of_payment)));
            if ($isRebooked) {
                $items->push((object) [
                    'id'                   => 'rebooked_' . $booking->id,
                    'composite_id'         => 'rebooked_' . $booking->id,
                    'booking_id'           => $booking->id,
                    'transaction_number'   => $txNumber,
                    'type'                 => 'rebooked',
                    'type_label'           => 'PAYMENT ACKNOWLEDGEMENT RECEIPT',
                    'display_name'         => $txNumber . ' - Rebooked',
                    'status_badge'         => 'Rebooked',
                    'status_class'         => 'receipt-badge-rebooked',
                    'client_name'          => $booking->client_name ?? '—',
                    'client_email'         => $booking->client_email ?? '—',
                    'route'                => ($booking->origin ?? '—') . ' → ' . ($booking->destination ?? '—'),
                    'passenger_count'      => max(1, $booking->passengers->count()),
                    'mode'                 => $booking->getMode(),
                    'operator_name'        => $booking->getOperatorName(),
                    'payment_reference'    => $tx?->payment_reference,
                    'payment_method'       => $tx?->payment_method ? strtoupper($tx->payment_method) : '—',
                    'amount'               => (float) ($booking->total_price ?? 0),
                    'issued_at'            => $booking->verified_at ?? $booking->updated_at,
                    'receipt_download_url' => route('admin.receipts.download', ['booking' => $booking->id, 'type' => 'rebooked']) . '?download=1',
                    'receipt_view_url'     => route('admin.receipts.download', ['booking' => $booking->id, 'type' => 'rebooked']),
                    'view_url'             => BookingResource::getUrl('view', ['record' => $booking]),
                    'booking_model'        => $booking,
                ]);
            }

            // C. Refund Acknowledgement Receipt
            $isRefunded = in_array($booking->status, [Booking::STATUS_CANCELLED, Booking::STATUS_OPERATOR_CANCELLED])
                || (float) $booking->refund_amount > 0
                || filled($booking->refund_destination)
                || $booking->isRefundCompleted();

            if ($isRefunded) {
                $items->push((object) [
                    'id'                   => 'refunded_' . $booking->id,
                    'composite_id'         => 'refunded_' . $booking->id,
                    'booking_id'           => $booking->id,
                    'transaction_number'   => $txNumber,
                    'type'                 => 'refunded',
                    'type_label'           => 'REFUND ACKNOWLEDGEMENT RECEIPT',
                    'display_name'         => $txNumber . ' - Refund',
                    'status_badge'         => $booking->isRefundCompleted() ? 'Refunded' : 'Refund Pending',
                    'status_class'         => 'receipt-badge-refunded',
                    'client_name'          => $booking->client_name ?? '—',
                    'client_email'         => $booking->client_email ?? '—',
                    'route'                => ($booking->origin ?? '—') . ' → ' . ($booking->destination ?? '—'),
                    'passenger_count'      => max(1, $booking->passengers->count()),
                    'mode'                 => $booking->getMode(),
                    'operator_name'        => $booking->getOperatorName(),
                    'payment_reference'    => $booking->refund_reference ?? $tx?->payment_reference,
                    'payment_method'       => $booking->refund_destination ? strtoupper($booking->refund_destination) : '—',
                    'amount'               => (float) ($booking->refund_amount ?? $booking->total_price ?? 0),
                    'issued_at'            => $booking->refund_processed_at ?? $booking->updated_at,
                    'receipt_download_url' => route('admin.receipts.download', ['booking' => $booking->id, 'type' => 'refunded']) . '?download=1',
                    'receipt_view_url'     => route('admin.receipts.download', ['booking' => $booking->id, 'type' => 'refunded']),
                    'view_url'             => BookingResource::getUrl('view', ['record' => $booking]),
                    'booking_model'        => $booking,
                ]);
            }
        }

        return $items->sortByDesc(fn ($item) => $item->issued_at ? $item->issued_at->timestamp : 0)->values();
    }

    #[Computed]
    public function counts(): array
    {
        $all = $this->allItems;

        return [
            'all'       => $all->count(),
            'confirmed' => $all->where('type', 'confirmed')->count(),
            'rebooked'  => $all->where('type', 'rebooked')->count(),
            'refunded'  => $all->where('type', 'refunded')->count(),
        ];
    }

    #[Computed]
    public function receipts(): Collection
    {
        $items = $this->allItems;

        // 1. Type filter
        if ($this->typeFilter !== 'all') {
            $items = $items->where('type', $this->typeFilter);
        }

        // 2. Date filter
        if ($this->dateFilter === 'today') {
            $items = $items->filter(fn ($i) => $i->issued_at && $i->issued_at->isToday());
        } elseif ($this->dateFilter === 'week') {
            $start = now()->startOfWeek();
            $end = now()->endOfWeek();
            $items = $items->filter(fn ($i) => $i->issued_at && $i->issued_at->between($start, $end));
        } elseif ($this->dateFilter === 'month') {
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
            $items = $items->filter(fn ($i) => $i->issued_at && $i->issued_at->between($start, $end));
        } elseif ($this->dateFilter === 'year') {
            $start = now()->startOfYear();
            $end = now()->endOfYear();
            $items = $items->filter(fn ($i) => $i->issued_at && $i->issued_at->between($start, $end));
        } elseif ($this->dateFilter === 'custom' && $this->customDateStart && $this->customDateEnd) {
            $start = \Carbon\Carbon::parse($this->customDateStart)->startOfDay();
            $end = \Carbon\Carbon::parse($this->customDateEnd)->endOfDay();
            $items = $items->filter(fn ($i) => $i->issued_at && $i->issued_at->between($start, $end));
        }

        // 3. Search query
        if (filled($this->search)) {
            $q = mb_strtolower(trim($this->search));
            $items = $items->filter(function ($i) use ($q) {
                return str_contains(mb_strtolower($i->transaction_number ?? ''), $q)
                    || str_contains(mb_strtolower($i->display_name ?? ''), $q)
                    || str_contains(mb_strtolower($i->client_name ?? ''), $q)
                    || str_contains(mb_strtolower($i->client_email ?? ''), $q)
                    || str_contains(mb_strtolower($i->route ?? ''), $q)
                    || str_contains(mb_strtolower($i->payment_reference ?? ''), $q);
            });
        }

        return $items->values();
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedReceipts = $this->receipts
                ->pluck('composite_id')
                ->toArray();
        } else {
            $this->selectedReceipts = [];
        }
    }

    public function updatedSelectedReceipts(): void
    {
        $allIds = $this->receipts
            ->pluck('composite_id')
            ->toArray();

        $this->selectAll = ! empty($allIds) && count(array_intersect($allIds, $this->selectedReceipts)) === count($allIds);
    }

    public function downloadZip(bool $onlySelected = false)
    {
        $items = $this->receipts;

        if ($onlySelected) {
            $items = $items->whereIn('composite_id', $this->selectedReceipts);
        }

        if ($items->isEmpty()) {
            Notification::make()
                ->title('No receipts selected or available to download')
                ->warning()
                ->send();

            return null;
        }

        $zipFileName = 'official-receipts-' . now()->format('Y-m-d-His') . '.zip';
        $zipFilePath = storage_path('app/' . $zipFileName);

        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            Notification::make()
                ->title('Failed to create ZIP archive')
                ->danger()
                ->send();

            return null;
        }

        $filesAdded = 0;

        foreach ($items as $item) {
            $booking = $item->booking_model ?? Booking::find($item->booking_id);
            if (! $booking) {
                continue;
            }

            $booking->loadMissing([
                'transaction',
                'passengers.discount',
                'transportClasses',
                'schedule.ferryRoute',
                'returnSchedule.ferryRoute',
                'accommodations',
            ]);

            try {
                $pdf = Pdf::loadView('pdf.receipt', [
                    'booking'     => $booking,
                    'receiptType' => $item->type,
                    'isTicket'    => false,
                ])->setPaper('a4');

                $pdfContent = $pdf->output();
                $cleanName = preg_replace('/[^A-Za-z0-9_-]/', '_', $item->display_name);
                $zipEntryName = "{$cleanName}.pdf";

                $counter = 1;
                while ($zip->statName($zipEntryName) !== false) {
                    $zipEntryName = "{$cleanName}_{$counter}.pdf";
                    $counter++;
                }

                $zip->addFromString($zipEntryName, $pdfContent);
                $filesAdded++;
            } catch (\Exception $e) {
                continue;
            }
        }

        $zip->close();

        if ($filesAdded === 0 || ! file_exists($zipFilePath)) {
            if (file_exists($zipFilePath)) {
                @unlink($zipFilePath);
            }

            Notification::make()
                ->title('Failed to generate receipt PDF files')
                ->warning()
                ->send();

            return null;
        }

        return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function downloadAllZipAction(): Action
    {
        return Action::make('downloadAllZip')
            ->label('Download all ZIP')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('success')
            ->action(fn () => $this->downloadZip(false))
            ->disabled(fn (): bool => $this->receipts->isEmpty());
    }

    public function downloadSelectedZipAction(): Action
    {
        return Action::make('downloadSelectedZip')
            ->label(fn (): string => 'Download ZIP (' . count($this->selectedReceipts) . ')')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('primary')
            ->action(fn () => $this->downloadZip(true))
            ->disabled(fn (): bool => empty($this->selectedReceipts));
    }
}
