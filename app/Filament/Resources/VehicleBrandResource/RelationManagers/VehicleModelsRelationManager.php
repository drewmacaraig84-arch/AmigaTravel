<?php

namespace App\Filament\Resources\VehicleBrandResource\RelationManagers;

use App\Filament\Resources\VehicleBrandResource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class VehicleModelsRelationManager extends RelationManager
{
    protected static string $relationship = 'models';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Model name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('price')
                    ->label('Default / Fallback Price (₱)')
                    ->numeric()
                    ->prefix('₱')
                    ->minValue(0)
                    ->required()
                    ->helperText('Default price used as fallback for non-Starlite routes. Use Route Prices to configure per-route pricing.'),

                TextInput::make('sort_order')
                    ->label('Sort order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first'),

                Toggle::make('is_active')
                    ->label('Visible to clients when booking')
                    ->default(true),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Model')
                    ->searchable()
                    ->sortable()
                    ->description('Click row or Route Prices to set prices per route'),

                TextColumn::make('route_rates_count')
                    ->counts('routeRates')
                    ->label('Routes Set')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'gray'),

                ToggleColumn::make('is_active')
                    ->label('Active'),
            ])
            ->defaultSort('sort_order', 'asc')
            ->recordUrl(fn ($record) => VehicleBrandResource::getUrl('model-routes', ['record' => $record->id]))
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actionsColumnLabel('Action')
            ->actions([
                Tables\Actions\Action::make('route_prices')
                    ->label('Route Prices')
                    ->icon('heroicon-o-map')
                    ->url(fn ($record) => VehicleBrandResource::getUrl('model-routes', ['record' => $record->id]))
                    ->color('primary')
                    ->openUrlInNewTab(false),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
