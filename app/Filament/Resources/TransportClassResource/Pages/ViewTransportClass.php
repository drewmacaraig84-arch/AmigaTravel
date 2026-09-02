<?php

namespace App\Filament\Resources\TransportClassResource\Pages;

use App\Filament\Resources\TransportClassResource;
use App\Models\Schedule;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\IconEntry;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;

class ViewTransportClass extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = TransportClassResource::class;

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

    public function table(Table $table): Table
    {
        $classId = $this->getRecord()->id;

        return $table
            ->query(
                Schedule::query()
                    ->whereHas('transportClasses', fn (Builder $q) => $q->where('transport_classes.id', $classId))
                    ->with([
                        'ferryRoute',
                        'transportClasses' => fn ($q) => $q->where('transport_classes.id', $classId),
                    ])
                    ->orderBy('departure_time')
            )
            ->heading('Routes & Schedules Using This Class')
            ->description('All schedules that have this transport class attached.')
            ->columns([
                TextColumn::make('ferryRoute.origin')
                    ->label('Origin')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('ferryRoute.destination')
                    ->label('Destination')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('ferryRoute.operator')
                    ->label('Operator')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('ferryRoute.mode')
                    ->label('Mode')
                    ->badge()
                    ->color(fn (?string $state): string => $state === 'airline' ? 'info' : 'success')
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'airline' => '✈️ Airline',
                        'ferry'   => '🚢 Ferry',
                        default   => ucfirst($state ?? '—'),
                    }),

                TextColumn::make('vehicle_name')
                    ->label('Vehicle / Flight')
                    ->searchable(),

                TextColumn::make('departure_time')
                    ->label('Departure')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('M j, Y g:i A') : '—')
                    ->sortable(),

                TextColumn::make('arrival_time')
                    ->label('Arrival')
                    ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->format('M j, Y g:i A') : '—')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Base Price')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('class_addon_price')
                    ->label('Class Add-on')
                    ->getStateUsing(function (Schedule $record) use ($classId): string {
                        $tc = $record->transportClasses->first();
                        return $tc ? '₱' . number_format((float) $tc->pivot->additional_price, 2) : '—';
                    }),

                TextColumn::make('class_tickets')
                    ->label('Tickets Avail.')
                    ->getStateUsing(function (Schedule $record) use ($classId): string {
                        $tc = $record->transportClasses->first();
                        return $tc ? number_format((int) $tc->pivot->tickets_available) : '—';
                    }),

                TextColumn::make('class_rate_type')
                    ->label('Rate Tier')
                    ->badge()
                    ->getStateUsing(function (Schedule $record) use ($classId): string {
                        $tc = $record->transportClasses->first();
                        return $tc ? ($tc->pivot->rate_type ?? 'regular') : 'regular';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'promotional'       => 'warning',
                        'super_promotional' => 'danger',
                        default             => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'promotional'       => '🟠 Promotional',
                        'super_promotional' => '🟣 Super Promo',
                        default             => '🔵 Regular',
                    }),

                TextColumn::make('is_active')
                    ->label('Active')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->formatStateUsing(fn ($state) => $state ? 'Active' : 'Inactive'),
            ])
            ->defaultSort('departure_time', 'asc')
            ->striped()
            ->paginated([25, 50, 100]);
    }
}
