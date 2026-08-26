<?php

namespace App\Filament\Pages;

use App\Filament\Resources\BookingResource;
use App\Filament\Resources\TransactionResource;
use App\Models\Booking;
use App\Models\PaymentSetting;
use App\Models\Transaction;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;

class ManageProofs extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->hasAdminPermission('proofs');
    }

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 30;
    protected static ?string $navigationLabel = 'Proofs';
    protected static ?string $title = 'Payment Proofs';
    protected static string $view = 'filament.pages.manage-proofs';

    public ?array $settingsData = [];
    public array $selectedTransactions = [];
    public bool $selectAll = false;

    public string $typeFilter = 'all'; // 'all', 'confirmed', 'rebooked', 'refunded'
    public string $search = '';

    public ?string $dateFilter = 'all';
    public ?string $customDateStart = null;
    public ?string $customDateEnd = null;

    public function mount(): void
    {
        $settings = PaymentSetting::current();

        $this->form->fill([
            'proof_retention_days' => $settings->proof_retention_days ?? 30,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Proof retention')
                    ->description('Proof images are automatically deleted after this many days. Set to 0 to keep proofs indefinitely.')
                    ->schema([
                        TextInput::make('proof_retention_days')
                            ->label('Delete proofs after (days)')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                    ]),
            ])
            ->statePath('settingsData');
    }

    public function setTypeFilter(string $filter): void
    {
        $this->typeFilter = $filter;
        $this->selectedTransactions = [];
        $this->selectAll = false;
    }

    #[Computed]
    public function allItems(): Collection
    {
        // 1. Fetch transactions with bookings
        $transactions = Transaction::query()
            ->with(['booking.passengers', 'booking.schedule.ferryRoute'])
            ->latest('updated_at')
            ->get();

        // 2. Fetch standalone bookings that might have refund proof images
        $bookings = Booking::query()
            ->with(['transaction', 'passengers', 'schedule.ferryRoute'])
            ->whereNotNull('refund_proof')
            ->latest('updated_at')
            ->get();

        $items = collect();

        // Map transactions -> Confirmed Proof & Rebooked Proof
        foreach ($transactions as $tx) {
            $booking = $tx->booking;
            $txNumber = $booking?->transaction_number ?? ('TX-' . $tx->id);

            // A. Confirmed Proof (Must have uploaded proof file)
            if (filled($tx->proof_of_payment)) {
                $statusClass = match ($tx->payment_status) {
                    'paid' => 'proofs-status-paid',
                    'pending' => 'proofs-status-pending',
                    'cancelled' => 'proofs-status-cancelled',
                    default => 'proofs-status-default',
                };

                $items->push((object) [
                    'id' => 'confirmed_' . $tx->id,
                    'composite_id' => 'confirmed_' . $tx->id,
                    'transaction_id' => $tx->id,
                    'booking_id' => $booking?->id,
                    'type' => 'confirmed',
                    'type_label' => 'Confirmed',
                    'display_name' => $txNumber,
                    'status_badge' => $tx->payment_status === 'paid' ? 'Paid' : ucfirst($tx->payment_status ?? 'Pending'),
                    'status_class' => $statusClass,
                    'proof_url' => $tx->proof_url,
                    'has_proof' => true,
                    'proof_disk_path' => $tx->proof_of_payment,
                    'client_name' => $booking?->client_name ?? '—',
                    'client_email' => $booking?->client_email ?? '—',
                    'route' => ($booking?->origin ?? '—') . ' → ' . ($booking?->destination ?? '—'),
                    'payment_reference' => $tx->payment_reference,
                    'amount' => (float) ($booking?->total_price ?? 0),
                    'updated_at' => $tx->updated_at ?? $tx->created_at,
                    'view_url' => $booking ? BookingResource::getUrl('view', ['record' => $booking]) : null,
                ]);
            }

            // B. Rebooked Proof (Must have uploaded rebooking proof file)
            if (filled($tx->rebooking_proof_of_payment)) {
                $rebookProofUrl = storage_asset_path($tx->rebooking_proof_of_payment);

                $items->push((object) [
                    'id' => 'rebooked_' . $tx->id,
                    'composite_id' => 'rebooked_' . $tx->id,
                    'transaction_id' => $tx->id,
                    'booking_id' => $booking?->id,
                    'type' => 'rebooked',
                    'type_label' => 'Rebooked',
                    'display_name' => $txNumber . ' - Rebooked',
                    'status_badge' => 'Rebooked',
                    'status_class' => 'proofs-status-rebooked',
                    'proof_url' => $rebookProofUrl,
                    'has_proof' => true,
                    'proof_disk_path' => $tx->rebooking_proof_of_payment,
                    'client_name' => $booking?->client_name ?? '—',
                    'client_email' => $booking?->client_email ?? '—',
                    'route' => ($booking?->origin ?? '—') . ' → ' . ($booking?->destination ?? '—'),
                    'payment_reference' => $tx->payment_reference,
                    'amount' => (float) ($booking?->total_price ?? 0),
                    'updated_at' => $booking?->verified_at ?? $tx->updated_at,
                    'view_url' => $booking ? BookingResource::getUrl('view', ['record' => $booking]) : null,
                ]);
            }
        }

        // C. Refunded / Cancelled Proof
        foreach ($bookings as $b) {
            if (filled($b->refund_proof)) {
                $refundProofUrl = storage_asset_path($b->refund_proof);
                $txNumber = $b->transaction_number ?? ('BK-' . $b->id);

                $items->push((object) [
                    'id' => 'refunded_' . $b->id,
                    'composite_id' => 'refunded_' . $b->id,
                    'transaction_id' => $b->transaction?->id,
                    'booking_id' => $b->id,
                    'type' => 'refunded',
                    'type_label' => 'Refunded/Cancelled',
                    'display_name' => $txNumber . ' - Refunded/Cancelled',
                    'status_badge' => $b->isRefundCompleted() ? 'Refunded' : 'Refund Pending',
                    'status_class' => 'proofs-status-refunded',
                    'proof_url' => $refundProofUrl,
                    'has_proof' => true,
                    'proof_disk_path' => $b->refund_proof,
                    'client_name' => $b->client_name ?? '—',
                    'client_email' => $b->client_email ?? '—',
                    'route' => ($b->origin ?? '—') . ' → ' . ($b->destination ?? '—'),
                    'payment_reference' => $b->refund_reference ?? $b->transaction?->payment_reference,
                    'amount' => (float) ($b->refund_amount ?? $b->total_price ?? 0),
                    'updated_at' => $b->refund_processed_at ?? $b->updated_at,
                    'view_url' => BookingResource::getUrl('view', ['record' => $b]),
                ]);
            }
        }

        // Sort latest first
        return $items->sortByDesc(fn ($item) => $item->updated_at ? $item->updated_at->timestamp : 0)->values();
    }

    #[Computed]
    public function counts(): array
    {
        $all = $this->allItems;

        return [
            'all' => $all->count(),
            'confirmed' => $all->where('type', 'confirmed')->count(),
            'rebooked' => $all->where('type', 'rebooked')->count(),
            'refunded' => $all->where('type', 'refunded')->count(),
        ];
    }

    #[Computed]
    public function proofs(): Collection
    {
        $items = $this->allItems;

        // 1. Type filter
        if ($this->typeFilter !== 'all') {
            $items = $items->where('type', $this->typeFilter);
        }

        // 2. Date filter
        if ($this->dateFilter === 'today') {
            $items = $items->filter(fn ($i) => $i->updated_at && $i->updated_at->isToday());
        } elseif ($this->dateFilter === 'week') {
            $start = now()->startOfWeek();
            $end = now()->endOfWeek();
            $items = $items->filter(fn ($i) => $i->updated_at && $i->updated_at->between($start, $end));
        } elseif ($this->dateFilter === 'month') {
            $start = now()->startOfMonth();
            $end = now()->endOfMonth();
            $items = $items->filter(fn ($i) => $i->updated_at && $i->updated_at->between($start, $end));
        } elseif ($this->dateFilter === 'year') {
            $start = now()->startOfYear();
            $end = now()->endOfYear();
            $items = $items->filter(fn ($i) => $i->updated_at && $i->updated_at->between($start, $end));
        } elseif ($this->dateFilter === 'custom' && $this->customDateStart && $this->customDateEnd) {
            $start = \Carbon\Carbon::parse($this->customDateStart)->startOfDay();
            $end = \Carbon\Carbon::parse($this->customDateEnd)->endOfDay();
            $items = $items->filter(fn ($i) => $i->updated_at && $i->updated_at->between($start, $end));
        }

        // 3. Search query
        if (filled($this->search)) {
            $search = strtolower(trim($this->search));
            $items = $items->filter(function ($i) use ($search) {
                return str_contains(strtolower($i->display_name), $search)
                    || str_contains(strtolower((string) $i->client_name), $search)
                    || str_contains(strtolower((string) $i->client_email), $search)
                    || str_contains(strtolower((string) $i->payment_reference), $search)
                    || str_contains(strtolower((string) $i->route), $search);
            });
        }

        return $items->values();
    }

    public function saveSettings(): void
    {
        $state = $this->form->getState();

        PaymentSetting::current()->update([
            'proof_retention_days' => (int) $state['proof_retention_days'],
        ]);
        PaymentSetting::bust(); // Clear cached payment settings

        Notification::make()
            ->title('Proof settings saved')
            ->success()
            ->send();
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedTransactions = $this->proofs
                ->pluck('composite_id')
                ->all();
        } else {
            $this->selectedTransactions = [];
        }
    }

    public function updatedDateFilter(): void
    {
        $this->selectedTransactions = [];
        $this->selectAll = false;
    }

    public function updatedSearch(): void
    {
        $this->selectedTransactions = [];
        $this->selectAll = false;
    }

    public function updatedCustomDateStart(): void
    {
        $this->selectedTransactions = [];
        $this->selectAll = false;
    }

    public function updatedCustomDateEnd(): void
    {
        $this->selectedTransactions = [];
        $this->selectAll = false;
    }

    public function updatedSelectedTransactions(): void
    {
        $allIds = $this->proofs
            ->pluck('composite_id')
            ->all();

        $this->selectAll = ! empty($allIds)
            && count($this->selectedTransactions) === count($allIds);
    }

    public function deleteSelected(): void
    {
        if (empty($this->selectedTransactions)) {
            return;
        }

        $count = 0;
        foreach ($this->selectedTransactions as $compositeId) {
            if ($this->performDelete($compositeId)) {
                $count++;
            }
        }

        $this->selectedTransactions = [];
        $this->selectAll = false;

        Notification::make()
            ->title($count === 1 ? '1 proof deleted' : "{$count} proofs deleted")
            ->success()
            ->send();
    }

    public function deleteProof(string $compositeId): void
    {
        if ($this->performDelete($compositeId)) {
            $this->selectedTransactions = array_values(array_filter(
                $this->selectedTransactions,
                fn (string $id): bool => $id !== $compositeId,
            ));

            $this->updatedSelectedTransactions();

            Notification::make()
                ->title('Proof deleted')
                ->success()
                ->send();
        }
    }

    protected function performDelete(string $compositeId): bool
    {
        $parts = explode('_', $compositeId, 2);
        $type = $parts[0] ?? '';
        $id = (int) ($parts[1] ?? 0);

        if ($type === 'confirmed' && $id > 0) {
            $tx = Transaction::find($id);
            if ($tx) {
                $tx->deleteProof();
                return true;
            }
        } elseif ($type === 'rebooked' && $id > 0) {
            $tx = Transaction::find($id);
            if ($tx && $tx->rebooking_proof_of_payment) {
                Storage::disk('public')->delete($tx->rebooking_proof_of_payment);
                $tx->update(['rebooking_proof_of_payment' => null]);
                return true;
            }
        } elseif ($type === 'refunded' && $id > 0) {
            $b = Booking::find($id);
            if ($b && $b->refund_proof) {
                Storage::disk('public')->delete($b->refund_proof);
                $b->update(['refund_proof' => null]);
                return true;
            }
        }

        return false;
    }

    public function viewBookingUrl(?Booking $booking): ?string
    {
        return $booking ? BookingResource::getUrl('view', ['record' => $booking]) : null;
    }

    public function downloadZip(bool $onlySelected = false): ?\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $items = $this->proofs;

        if ($onlySelected && ! empty($this->selectedTransactions)) {
            $items = $items->whereIn('composite_id', $this->selectedTransactions);
        }

        if ($items->isEmpty()) {
            Notification::make()
                ->title('No proofs available to download')
                ->warning()
                ->send();

            return null;
        }

        $zipFileName = 'payment-proofs-' . now()->format('Y-m-d-His') . '.zip';
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
        $disk = Storage::disk('public');

        foreach ($items as $item) {
            $proofPath = $item->proof_disk_path ?? null;
            if (! $proofPath) {
                continue;
            }

            try {
                $fileContents = $disk->get($proofPath);
            } catch (\Exception $e) {
                $fileContents = null;
            }

            if ($fileContents !== null) {
                $extension = pathinfo($proofPath, PATHINFO_EXTENSION) ?: 'jpg';
                $cleanName = preg_replace('/[^A-Za-z0-9_-]/', '_', $item->display_name);
                $zipEntryName = "{$cleanName}_proof.{$extension}";

                // Handle duplicates inside ZIP
                $counter = 1;
                while ($zip->statName($zipEntryName) !== false) {
                    $zipEntryName = "{$cleanName}_proof_{$counter}.{$extension}";
                    $counter++;
                }

                $zip->addFromString($zipEntryName, $fileContents);
                $filesAdded++;
            }
        }

        $zip->close();

        if ($filesAdded === 0 || ! file_exists($zipFilePath)) {
            if (file_exists($zipFilePath)) {
                @unlink($zipFilePath);
            }

            Notification::make()
                ->title('No proof files found on disk')
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
            ->color('warning')
            ->action(fn () => $this->downloadZip(false))
            ->disabled(fn (): bool => $this->proofs->isEmpty());
    }

    public function downloadSelectedZipAction(): Action
    {
        return Action::make('downloadSelectedZip')
            ->label(fn (): string => 'Download ZIP (' . count($this->selectedTransactions) . ')')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('warning')
            ->action(fn () => $this->downloadZip(true))
            ->disabled(fn (): bool => empty($this->selectedTransactions));
    }

    public function deleteSelectedAction(): Action
    {
        return Action::make('deleteSelected')
            ->label('Delete selected')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Delete selected proofs')
            ->modalDescription('Delete the selected proof images? This cannot be undone.')
            ->modalSubmitActionLabel('Delete')
            ->action(fn () => $this->deleteSelected())
            ->disabled(fn (): bool => empty($this->selectedTransactions));
    }

    #[Computed]
    public function archives(): Collection
    {
        return app(\App\Services\ProofArchivalService::class)->listArchives();
    }

    public function createPreRetentionArchiveManual(): void
    {
        try {
            $archive = app(\App\Services\ProofArchivalService::class)->createArchive();

            unset($this->archives);

            if ($archive) {
                Notification::make()
                    ->title('Proofs & Receipts Backup Created!')
                    ->body("Archive {$archive['filename']} created with {$archive['files_count']} item(s) ({$archive['formatted_size']}).")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('No proofs to backup')
                    ->body('There are currently no proofs or receipts in the system.')
                    ->info()
                    ->send();
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Backup creation failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteProofAction(): Action
    {
        return Action::make('deleteProof')
            ->label('Delete')
            ->color('danger')
            ->size(ActionSize::Small)
            ->requiresConfirmation()
            ->modalHeading('Delete proof')
            ->modalDescription('Delete this proof image? This cannot be undone.')
            ->modalSubmitActionLabel('Delete')
            ->action(function (array $data, array $arguments = []): void {
                $compositeId = (string) ($arguments['compositeId'] ?? $data['compositeId'] ?? '');
                $this->deleteProof($compositeId);
            })
            ->extraAttributes(['class' => 'flex-1']);
    }

    public function createBackupAction(): Action
    {
        return Action::make('createBackup')
            ->label('📦 Backup All Proofs')
            ->icon('heroicon-o-archive-box-arrow-down')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Backup All Proofs & Receipts')
            ->modalDescription('Package all payment proofs, rebooking proofs, refund proofs, and official receipts across the system into a backup ZIP archive?')
            ->modalSubmitActionLabel('Create Backup ZIP')
            ->action(fn () => $this->createPreRetentionArchiveManual());
    }

    public function deleteArchiveAction(): Action
    {
        return Action::make('deleteArchive')
            ->label('Delete')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->size(ActionSize::ExtraSmall)
            ->requiresConfirmation()
            ->modalHeading('Delete Backup Archive')
            ->modalDescription('Are you sure you want to permanently delete this backup ZIP archive? This cannot be undone.')
            ->modalSubmitActionLabel('Delete')
            ->modalIcon('heroicon-o-trash')
            ->action(function (array $data, array $arguments = []): void {
                $filename = (string) ($arguments['filename'] ?? $data['filename'] ?? '');
                if ($filename) {
                    $deleted = app(\App\Services\ProofArchivalService::class)->deleteArchive($filename);
                    unset($this->archives);

                    if ($deleted) {
                        Notification::make()
                            ->title('Backup archive deleted')
                            ->body("Archive {$filename} was deleted successfully.")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Failed to delete archive')
                            ->body("Archive {$filename} could not be found or deleted.")
                            ->danger()
                            ->send();
                    }
                }
            });
    }
}
