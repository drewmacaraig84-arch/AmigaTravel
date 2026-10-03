<?php

namespace App\Filament\Resources\VoucherResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RedemptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'redemptions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->poll('4s')
            ->columns([
                Tables\Columns\TextColumn::make('booking.transaction_number')
                    ->label('Booking #')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('normalized_email')
                    ->label('Customer Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Discount')
                    ->money('PHP')
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_amount')
                    ->label('Base Amount')
                    ->money('PHP')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Redeemed At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => \App\Filament\Resources\BookingResource::getUrl('view', ['record' => $record->booking_id])),
            ])
            ->bulkActions([
                //
            ]);
    }
}
