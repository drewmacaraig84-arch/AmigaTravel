<?php

namespace App\Filament\Resources\TransportClassResource\Pages;

use App\Filament\Resources\TransportClassResource;
use App\Models\ScheduleTransportClass;
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
}
