<?php

namespace App\Filament\Resources\TransportClassResource\Pages;

use App\Filament\Resources\TransportClassResource;
use App\Models\Schedule;
use App\Models\ScheduleTransportClass;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Notifications\Notification;

class ViewTransportClass extends ViewRecord
{
    protected static string $resource = TransportClassResource::class;

    protected static string $view = 'filament.resources.transport-class-resource.pages.view-transport-class';

    /** @var int[] */
    public array $selectedSchedules = [];

    public bool $showPriceModal = false;

    public string $newAdditionalPrice = '';

    public bool $showPromoModal = false;

    public string $modalRateType = 'promotional'; // 'regular', 'promotional', 'super_promotional'

    public string $modalPrice = '';

    public string $modalPromoType = 'temporary'; // 'temporary', 'permanent'

    public ?string $modalDurationStart = null;

    public ?string $modalDurationEnd = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Class Details')
                    ->icon('heroicon-o-ticket')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Class Name')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight(\Filament\Support\Enums\FontWeight::Bold),

                                TextEntry::make('mode')
                                    ->label('Mode')
                                    ->badge()
                                    ->color(fn (?string $state): string => $state === 'airline' ? 'info' : 'success')
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'airline' => '✈️ Airline',
                                        'ferry'   => '🚢 Ferry',
                                        default   => ucfirst($state ?? 'Unknown'),
                                    }),

                                TextEntry::make('operatorRecord.name')
                                    ->label('Operator')
                                    ->badge()
                                    ->color('gray'),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Class Code')
                                    ->default('—'),

                                TextEntry::make('price')
                                    ->label('Base Price')
                                    ->money('PHP'),

                                IconEntry::make('is_active')
                                    ->label('Active')
                                    ->boolean(),
                            ]),

                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->default('No description provided.')
                            ->prose(),
                    ]),
            ]);
    }

    // ─── Selection ────────────────────────────────────────────────────────────

    public function toggleSchedule(int $scheduleId): void
    {
        if (in_array($scheduleId, $this->selectedSchedules, true)) {
            $this->selectedSchedules = array_values(
                array_filter($this->selectedSchedules, fn ($id) => $id !== $scheduleId)
            );
        } else {
            $this->selectedSchedules[] = $scheduleId;
        }
    }

    /**
     * Toggle all schedules within a given route. If all are already selected,
     * deselect them; otherwise select any that are not yet selected.
     */
    public function toggleRouteAll(int $ferryRouteId): void
    {
        $scheduleIds = $this->getRecord()
            ->schedules()
            ->where('ferry_route_id', $ferryRouteId)
            ->pluck('schedules.id')
            ->toArray();

        $selectedInRoute = array_intersect($scheduleIds, $this->selectedSchedules);

        if (count($selectedInRoute) === count($scheduleIds) && count($scheduleIds) > 0) {
            // All selected → deselect all in route
            $this->selectedSchedules = array_values(
                array_filter($this->selectedSchedules, fn ($id) => !in_array($id, $scheduleIds, true))
            );
        } else {
            // Partial or none → select all in route
            foreach ($scheduleIds as $id) {
                if (!in_array($id, $this->selectedSchedules, true)) {
                    $this->selectedSchedules[] = $id;
                }
            }
        }
    }

    public function clearSelection(): void
    {
        $this->selectedSchedules = [];
    }

    // ─── Edit Price ───────────────────────────────────────────────────────────

    public function openPriceModal(?int $scheduleId = null): void
    {
        if ($scheduleId !== null) {
            $this->selectedSchedules = [$scheduleId];
        }

        // Pre-fill with current add-on price of the first selected schedule
        if (!empty($this->selectedSchedules)) {
            $firstScheduleId = $this->selectedSchedules[0];
            $existing = ScheduleTransportClass::where('transport_class_id', $this->getRecord()->id)
                ->where('schedule_id', $firstScheduleId)
                ->value('additional_price');
            $this->newAdditionalPrice = $existing !== null ? (string) $existing : '0';
        } else {
            $this->newAdditionalPrice = '0';
        }

        $this->showPriceModal = true;
    }

    public function applyPriceChange(): void
    {
        if (empty($this->selectedSchedules)) {
            $this->showPriceModal = false;
            return;
        }

        $price = max(0, (float) $this->newAdditionalPrice);

        ScheduleTransportClass::where('transport_class_id', $this->getRecord()->id)
            ->whereIn('schedule_id', $this->selectedSchedules)
            ->update(['additional_price' => $price]);

        $count = count($this->selectedSchedules);
        $this->selectedSchedules = [];
        $this->showPriceModal  = false;
        $this->newAdditionalPrice = '';

        Notification::make()
            ->title("Add-on price updated for {$count} schedule(s)")
            ->success()
            ->send();
    }

    public function cancelPriceModal(): void
    {
        $this->showPriceModal     = false;
        $this->newAdditionalPrice = '';
    }

    // ─── Restore Price ────────────────────────────────────────────────────────

    /**
     * Restore additional_price to the transport class's base price.
     * If $scheduleId is provided, restores only that one; otherwise restores all selected.
     */
    public function restorePrice(?int $scheduleId = null): void
    {
        $targets = $scheduleId !== null ? [$scheduleId] : $this->selectedSchedules;

        if (empty($targets)) {
            Notification::make()->title('No schedules selected')->warning()->send();
            return;
        }

        $basePrice = (float) $this->getRecord()->price;

        ScheduleTransportClass::where('transport_class_id', $this->getRecord()->id)
            ->whereIn('schedule_id', $targets)
            ->update(['additional_price' => $basePrice]);

        $count = count($targets);

        if ($scheduleId === null) {
            $this->selectedSchedules = [];
        }

        Notification::make()
            ->title("Restored to ₱" . number_format($basePrice, 2) . " for {$count} schedule(s)")
            ->success()
            ->send();
    }

    // ─── Promo / Super Promo Modal ────────────────────────────────────────────

    public function openPromoModal(?int $scheduleId = null): void
    {
        if ($scheduleId !== null) {
            $this->selectedSchedules = [$scheduleId];
        }

        if (empty($this->selectedSchedules)) {
            Notification::make()->title('No schedules selected')->warning()->send();
            return;
        }

        $firstScheduleId = $this->selectedSchedules[0];
        $existing = ScheduleTransportClass::where('transport_class_id', $this->getRecord()->id)
            ->where('schedule_id', $firstScheduleId)
            ->first();

        if ($existing) {
            $this->modalRateType = in_array($existing->rate_type, ['promotional', 'super_promotional'], true)
                ? $existing->rate_type
                : 'promotional';
            $this->modalPrice = (string) ($existing->additional_price !== null ? $existing->additional_price : '0');
            $this->modalPromoType = in_array($existing->promo_type, ['temporary', 'permanent'], true)
                ? $existing->promo_type
                : 'temporary';
            $this->modalDurationStart = $existing->promo_duration_start
                ? Carbon::parse($existing->promo_duration_start)->format('Y-m-d\TH:i')
                : now()->format('Y-m-d\TH:i');
            $this->modalDurationEnd = $existing->promo_duration_end
                ? Carbon::parse($existing->promo_duration_end)->format('Y-m-d\TH:i')
                : now()->addDays(14)->setTime(23, 59)->format('Y-m-d\TH:i');
        } else {
            $this->modalRateType = 'promotional';
            $this->modalPrice = (string) ($this->getRecord()->price ?? '0');
            $this->modalPromoType = 'temporary';
            $this->modalDurationStart = now()->format('Y-m-d\TH:i');
            $this->modalDurationEnd = now()->addDays(14)->setTime(23, 59)->format('Y-m-d\TH:i');
        }

        $this->showPromoModal = true;
    }

    public function applyPromoModal(): void
    {
        if (empty($this->selectedSchedules)) {
            $this->showPromoModal = false;
            return;
        }

        $price = max(0, (float) $this->modalPrice);

        if ($this->modalRateType === 'regular') {
            $updateData = [
                'rate_type'            => 'regular',
                'is_promo'             => false,
                'additional_price'     => $price,
                'promo_type'           => null,
                'promo_duration_start' => null,
                'promo_duration_end'   => null,
            ];
            $tierLabel = 'Regular Fare';
        } else {
            // Promotional or Super Promotional
            if (! empty($this->modalDurationStart) && ! empty($this->modalDurationEnd)) {
                $start = Carbon::parse($this->modalDurationStart);
                $end = Carbon::parse($this->modalDurationEnd);
                if ($end->lessThan($start)) {
                    Notification::make()
                        ->title('Invalid Date Horizon')
                        ->body('End date/time must be after start date/time.')
                        ->danger()
                        ->send();
                    return;
                }
            }

            $updateData = [
                'rate_type'            => $this->modalRateType,
                'is_promo'             => true,
                'additional_price'     => $price,
                'promo_type'           => $this->modalPromoType,
                'promo_duration_start' => $this->modalDurationStart ? Carbon::parse($this->modalDurationStart) : now(),
                'promo_duration_end'   => $this->modalDurationEnd ? Carbon::parse($this->modalDurationEnd) : null,
            ];

            $tierLabel = $this->modalRateType === 'super_promotional' ? 'Super Promo' : 'Promotional';
        }

        ScheduleTransportClass::where('transport_class_id', $this->getRecord()->id)
            ->whereIn('schedule_id', $this->selectedSchedules)
            ->update($updateData);

        Schedule::bust();

        $count = count($this->selectedSchedules);
        $this->selectedSchedules = [];
        $this->showPromoModal = false;

        Notification::make()
            ->title("{$count} schedule(s) updated to {$tierLabel}")
            ->success()
            ->send();
    }

    public function cancelPromoModal(): void
    {
        $this->showPromoModal = false;
    }
}
