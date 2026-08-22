<?php

namespace App\Filament\Resources\BookingResource\RelationManagers;

use App\Models\Passenger;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PassengersRelationManager extends RelationManager
{
    protected static string $relationship = 'passengers';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Passenger Name')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'adult' => 'Adult',
                        'child' => 'Child',
                        'driver' => 'Driver',
                    ])
                    ->required(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->defaultSort('item_number', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('item_number')
                    ->label('Item #')
                    ->formatStateUsing(fn ($state) => 'Item ' . ($state ?? '1'))
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Passenger Name')
                    ->searchable()
                    ->description(fn (Passenger $record): string => $record->ticket_number ?? ''),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst($state ?? 'adult')),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (Passenger $record) => $record->getStatusLabel())
                    ->color(fn (Passenger $record) => $record->getStatusColor()),
                Tables\Columns\TextColumn::make('fare_and_class')
                    ->label('Fare & Class')
                    ->state(fn (Passenger $record): string => '₱' . number_format($record->getEffectiveFareAndClass(), 2)),
                Tables\Columns\TextColumn::make('web_admin_fee')
                    ->label('Web Admin Fee')
                    ->state(fn (Passenger $record): string => '₱' . number_format($record->getEffectiveWebAdminFee(), 2))
                    ->color('gray'),
                Tables\Columns\TextColumn::make('transaction_fee')
                    ->label('Transaction Fee')
                    ->state(fn (Passenger $record): string => '₱' . number_format($record->getEffectiveTransactionFee(), 2))
                    ->color('gray'),
                Tables\Columns\TextColumn::make('refund_amount')
                    ->label('Refund / Fee')
                    ->state(function (Passenger $record): string {
                        if ((float) $record->refund_amount > 0) {
                            return 'Refund: ₱' . number_format((float) $record->refund_amount, 2);
                        }
                        if ((float) $record->cancellation_fee > 0) {
                            return 'Fee: ₱' . number_format((float) $record->cancellation_fee, 2);
                        }
                        return '—';
                    })
                    ->color(fn (Passenger $record) => (float) $record->refund_amount > 0 ? 'danger' : 'gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('item_total')
                    ->label('Item Total')
                    ->state(fn (Passenger $record): string => '₱' . number_format($record->getEffectiveItemTotal(), 2))
                    ->weight('bold')
                    ->color('primary'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Item Status')
                    ->options([
                        'confirmed'          => 'Confirmed (Active)',
                        'pending'            => 'Pending',
                        'refund_pending'     => 'Refund Pending',
                        'refunded'           => 'Refunded',
                        'rebooking_pending'  => 'Rebooking Pending',
                        'rebooked'           => 'Rebooked (Historical)',
                        'cancelled'          => 'Cancelled',
                        'operator_cancelled' => 'Cancelled by Operator',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_ticket')
                    ->label('E-Ticket')
                    ->icon('heroicon-m-ticket')
                    ->color('success')
                    ->url(fn (Passenger $record): string => route('ticket.passenger', ['id' => $record->id]))
                    ->openUrlInNewTab()
                    ->visible(fn (Passenger $record): bool => $record->isActiveBookingItem() && in_array($record->status, ['confirmed', 'rebooked'])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
