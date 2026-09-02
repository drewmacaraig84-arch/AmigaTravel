<?php

namespace App\Filament\Resources\TransportClassResource\Pages;

use App\Filament\Resources\TransportClassResource;
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
        /** @var \App\Models\TransportClass $transportClass */
        $transportClass = $this->getRecord();

        return $table
            ->query(
                $transportClass->schedules()->getQuery()
                    ->with('ferryRoute')
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

                TextColumn::make('pivot.additional_price')
                    ->label('Class Add-on')
                    ->formatStateUsing(fn ($state) => $state !== null ? '₱' . number_format((float) $state, 2) : '—'),

                TextColumn::make('pivot.tickets_available')
                    ->label('Tickets Avail.')
                    ->formatStateUsing(fn ($state) => $state !== null ? number_format((int) $state) : '—'),

                TextColumn::make('pivot.rate_type')
                    ->label('Rate Tier')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'promotional'       => 'warning',
                        'super_promotional' => 'danger',
                        default             => 'success',
                    })
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
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
