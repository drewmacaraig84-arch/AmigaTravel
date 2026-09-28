<?php

namespace App\Filament\Resources\FerryRouteResource\RelationManagers;

use App\Models\Schedule;
use App\Models\TransportClass;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SchedulesRelationManager extends RelationManager
{
    protected static string $relationship = 'schedules';

    protected static ?string $title = 'Schedules for this Route';

    protected static ?string $recordTitleAttribute = 'departure_time';

    public function form(Form $form): Form
    {
        $ownerRecord = $this->getOwnerRecord();

        return $form
            ->schema([
                Forms\Components\Section::make('Schedule Information')
                    ->schema([
                        TextInput::make('vehicle_name')
                            ->label('Vehicle / Vessel / Flight')
                            ->default(fn () => optional($ownerRecord?->vehicle)->name)
                            ->placeholder('e.g. MV 2GO Masagana')
                            ->maxLength(255),

                        TextInput::make('plate_no')
                            ->label('Plate / Tail No.')
                            ->maxLength(255),

                        DateTimePicker::make('departure_time')
                            ->label('Departure Time')
                            ->seconds(false)
                            ->native(false)
                            ->required(),

                        DateTimePicker::make('arrival_time')
                            ->label('Arrival Time')
                            ->seconds(false)
                            ->native(false)
                            ->required(),

                        TextInput::make('duration_minutes')
                            ->label('Duration (minutes)')
                            ->helperText('Optional — auto-calculated if left blank.')
                            ->numeric()
                            ->minValue(1),

                        TextInput::make('price')
                            ->label('Reseller Base Price (₱)')
                            ->numeric()
                            ->prefix('₱')
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        TextInput::make('availability_label')
                            ->label('Availability Note')
                            ->placeholder('e.g. Available, Limited availability')
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Visible to clients when booking')
                            ->default(true),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Transport Classes')
                    ->description('Assign classes, fares, and bed inclusions to this individual schedule.')
                    ->schema([
                        Repeater::make('scheduleTransportClasses')
                            ->relationship('scheduleTransportClasses')
                            ->label('')
                            ->schema([
                                Select::make('transport_class_id')
                                    ->label('Transport Class')
                                    ->options(function () use ($ownerRecord) {
                                        return TransportClass::query()
                                            ->when($ownerRecord?->operator_id, fn ($q, $opId) => $q->where('operator_id', $opId))
                                            ->where('is_active', true)
                                            ->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(fn ($item) => [
                                                $item->id => $item->operator_record ? "{$item->operator_record->name} - {$item->name}" : $item->name,
                                            ])
                                            ->toArray();
                                    })
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $tc = TransportClass::find($state);
                                            if ($tc) {
                                                $set('transport_class_name', $tc->name);
                                                $price = ($tc->is_on_sale && $tc->sale_price !== null && $tc->sale_price > 0) ? $tc->sale_price : $tc->price;
                                                $set('additional_price', $price ?? 0);
                                                if ($tc->description) {
                                                    $set('description', $tc->description);
                                                }
                                            }
                                        }
                                    })
                                    ->columnSpanFull(),

                                Textarea::make('description')
                                    ->placeholder('Details about this transport class option')
                                    ->rows(2)
                                    ->columnSpanFull(),

                                TextInput::make('additional_price')
                                    ->label('Additional Price (₱)')
                                    ->numeric()
                                    ->prefix('₱')
                                    ->default(0)
                                    ->minValue(0),

                                TextInput::make('rate_code')
                                    ->label('Promo Rate Code')
                                    ->placeholder('e.g. PROMO, EARLYBIRD')
                                    ->maxLength(255),

                                TextInput::make('tickets_available')
                                    ->label('Tickets Available')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(50)
                                    ->required(),

                                Select::make('rate_type')
                                    ->label('Rate Tier & Fare Policy')
                                    ->options([
                                        'regular'           => '🔵 Regular Fare (Standard - 100% Rate)',
                                        'promotional'       => '🟠 Promotional Fare (Promo - Non-refundable)',
                                        'super_promotional' => '🟣 Super Promotional (Super Promo - No Discounts/Vouchers/Points)',
                                    ])
                                    ->default('regular')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('is_promo', in_array($state, ['promotional', 'super_promotional'], true));
                                    }),

                                Toggle::make('is_promo')
                                    ->label('Promotional Ticket (Non-refundable)')
                                    ->helperText('Tickets in this class will not be eligible for standard refunds.')
                                    ->live()
                                    ->hidden(),

                                DateTimePicker::make('promo_duration_start')
                                    ->label('Promo Start Date & Time')
                                    ->visible(fn (callable $get) => in_array($get('rate_type'), ['promotional', 'super_promotional'], true) || $get('is_promo') === true)
                                    ->required(fn (callable $get) => in_array($get('rate_type'), ['promotional', 'super_promotional'], true)),

                                DateTimePicker::make('promo_duration_end')
                                    ->label('Promo End Date & Time')
                                    ->visible(fn (callable $get) => in_array($get('rate_type'), ['promotional', 'super_promotional'], true) || $get('is_promo') === true)
                                    ->required(fn (callable $get) => in_array($get('rate_type'), ['promotional', 'super_promotional'], true))
                                    ->after('promo_duration_start'),

                                Toggle::make('has_bed')
                                    ->label('Includes bed / berth')
                                    ->helperText('Enable for transport classes that include sleeping berths.'),

                                Toggle::make('is_active')
                                    ->label('Visible to clients when booking')
                                    ->default(true),

                                TextInput::make('transport_class_name')->hidden(),
                            ])
                            ->columns(2)
                            ->collapsible()
                            ->collapsed(false)
                            ->itemLabel(fn (array $state): ?string => $state['transport_class_name'] ?? null)
                            ->defaultItems(0)
                            ->cloneable()
                            ->deletable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('departure_time')
            ->defaultSort('departure_time', 'asc')
            ->defaultPaginationPageOption(15)
            ->paginationPageOptions([10, 15, 25, 50, 100])
            ->columns([
                TextColumn::make('vehicle_name')
                    ->label('Vehicle / Vessel')
                    ->searchable()
                    ->sortable()
                    ->placeholder(fn () => optional($this->getOwnerRecord()->vehicle)->name ?? '—'),

                TextColumn::make('plate_no')
                    ->label('Plate / Tail')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('departure_time')
                    ->label('Departure')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),

                TextColumn::make('arrival_time')
                    ->label('Arrival')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Base Price')
                    ->money('PHP')
                    ->sortable(),

                TextColumn::make('schedule_transport_classes_count')
                    ->counts('scheduleTransportClasses')
                    ->label('Classes')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => "{$state} " . ($state == 1 ? 'class' : 'classes')),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->afterStateUpdated(fn () => Schedule::bust()),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Schedule')
                    ->icon('heroicon-m-plus')
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (empty($data['vehicle_name']) && $this->getOwnerRecord()->vehicle) {
                            $data['vehicle_name'] = $this->getOwnerRecord()->vehicle->name;
                        }
                        if (empty($data['service_name']) && !empty($data['vehicle_name'])) {
                            $data['service_name'] = $data['vehicle_name'];
                        }
                        return $data;
                    })
                    ->after(fn () => Schedule::bust()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (empty($data['service_name']) && !empty($data['vehicle_name'])) {
                            $data['service_name'] = $data['vehicle_name'];
                        }
                        return $data;
                    })
                    ->after(fn () => Schedule::bust()),
                Tables\Actions\DeleteAction::make()
                    ->after(fn () => Schedule::bust()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(fn () => Schedule::bust()),
                ]),
            ]);
    }
}
