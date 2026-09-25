<?php

namespace App\Filament\Resources\VehicleRateResource\Pages;

use App\Filament\Resources\VehicleRateResource;
use App\Models\VehicleRate;
use App\Models\VehicleRouteRate;
use Filament\Actions\Action as HeaderAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ManageVehicleRateRoutes extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = VehicleRateResource::class;

    protected static string $view = 'filament.resources.vehicle-rate-resource.pages.manage-vehicle-rate-routes';

    /**
     * The VehicleRate record resolved from the URL {record} parameter.
     */
    public VehicleRate|int|string|null $record = null;

    public function mount(int|string $record): void
    {
        $this->record = VehicleRate::findOrFail($record);
        abort_unless(VehicleRateResource::canAccess(), 403);
    }

    public function getTitle(): string
    {
        return $this->record->name . ' — Route Pricing';
    }

    public function getBreadcrumbs(): array
    {
        return [
            VehicleRateResource::getUrl()                                          => 'Vehicle Rates',
            VehicleRateResource::getUrl('routes', ['record' => $this->record->id]) => $this->record->name,
            ''                                                                      => 'Route Pricing',
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Header actions (page-level — back button)
    // ─────────────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            HeaderAction::make('back')
                ->label('Back to Vehicle Rates')
                ->url(VehicleRateResource::getUrl())
                ->icon('heroicon-o-arrow-left')
                ->color('gray'),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Table
    // ─────────────────────────────────────────────────────────────

    public function table(Table $table): Table
    {
        return $table
            ->query(
                VehicleRouteRate::query()
                    ->where('vehicle_rate_id', $this->record->id)
                    ->orderBy('origin')
                    ->orderBy('destination')
            )
            ->heading('Starlite Route Prices for: ' . $this->record->name)
            ->description('Set the rolling-cargo price for this vehicle category on each Starlite route. Prices here override the flat default price.')
            ->columns([
                TextColumn::make('origin')
                    ->label('Origin')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('destination')
                    ->label('Destination')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('route_key')
                    ->label('Route Key')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->money('PHP')
                    ->sortable()
                    ->weight('semibold'),

                ToggleColumn::make('is_active')
                    ->label('Active'),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('origin')
                    ->label('Filter by Origin')
                    ->options(fn () => VehicleRouteRate::where('vehicle_rate_id', $this->record->id)
                        ->distinct()
                        ->orderBy('origin')
                        ->pluck('origin', 'origin')
                        ->toArray()),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All routes')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->headerActions([
                // Add a brand-new route price (for routes not yet in the list)
                Action::make('add_route_price')
                    ->label('Add Route Price')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->modalHeading('Add Route Price for ' . $this->record->name)
                    ->form([
                        Grid::make(2)->schema([
                            TextInput::make('origin')
                                ->label('Origin')
                                ->placeholder('e.g. Batangas')
                                ->required()
                                ->maxLength(120),

                            TextInput::make('destination')
                                ->label('Destination')
                                ->placeholder('e.g. Calapan')
                                ->required()
                                ->maxLength(120),
                        ]),

                        TextInput::make('price')
                            ->label('Price (₱)')
                            ->numeric()
                            ->prefix('₱')
                            ->required()
                            ->minValue(0)
                            ->default($this->record->price),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->action(function (array $data): void {
                        $routeKey = trim($data['origin']) . '|' . trim($data['destination']);

                        if (VehicleRouteRate::where('vehicle_rate_id', $this->record->id)
                            ->where('route_key', $routeKey)
                            ->exists()) {
                            Notification::make()
                                ->title('Duplicate route')
                                ->body('A price entry for this origin-destination already exists.')
                                ->warning()
                                ->send();

                            return;
                        }

                        VehicleRouteRate::create([
                            'vehicle_rate_id'  => $this->record->id,
                            'vehicle_model_id' => null,
                            'route_key'        => $routeKey,
                            'origin'           => trim($data['origin']),
                            'destination'      => trim($data['destination']),
                            'price'            => $data['price'],
                            'is_active'        => $data['is_active'],
                        ]);

                        Notification::make()
                            ->title('Route price added')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->form([
                        Grid::make(2)->schema([
                            TextInput::make('origin')
                                ->label('Origin')
                                ->required()
                                ->maxLength(120),

                            TextInput::make('destination')
                                ->label('Destination')
                                ->required()
                                ->maxLength(120),
                        ]),

                        TextInput::make('price')
                            ->label('Price (₱)')
                            ->numeric()
                            ->prefix('₱')
                            ->required()
                            ->minValue(0),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->mutateRecordDataUsing(fn (array $data) => $data)
                    ->using(function (VehicleRouteRate $record, array $data): VehicleRouteRate {
                        $record->update([
                            'origin'      => trim($data['origin']),
                            'destination' => trim($data['destination']),
                            'route_key'   => trim($data['origin']) . '|' . trim($data['destination']),
                            'price'       => $data['price'],
                            'is_active'   => $data['is_active'],
                        ]);

                        return $record;
                    }),

                DeleteAction::make(),
            ])
            ->defaultSort('origin', 'asc')
            ->paginated([15, 25, 50])
            ->striped();
    }
}
