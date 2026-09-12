<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Delete Booking')
                ->modalHeading('Delete Booking')
                ->modalDescription('This booking will be safely archived (soft-deleted). Only the Super Admin can view or restore it.')
                ->form([
                    TextInput::make('deletion_reason')
                        ->label('Reason for Deletion')
                        ->placeholder('e.g. Duplicate entry, customer cancelled via phone')
                        ->maxLength(255)
                        ->nullable(),
                ])
                ->before(function (Booking $record, array $data) {
                    if (Auth::check()) {
                        $record->deleted_by_user_id = Auth::id();
                    }
                    if (! empty($data['deletion_reason'])) {
                        $record->deletion_reason = $data['deletion_reason'];
                    }
                    $record->saveQuietly();
                }),
            Actions\RestoreAction::make()
                ->visible(fn (Booking $record): bool => $record->trashed() && (Auth::user()?->isSuperAdmin() ?? false)),
        ];
    }
}
