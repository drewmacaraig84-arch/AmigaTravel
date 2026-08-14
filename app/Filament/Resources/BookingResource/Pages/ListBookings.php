<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\ActionGroup::make([
                Actions\Action::make('download-pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from_date')->label('From Date'),
                        \Filament\Forms\Components\DatePicker::make('to_date')->label('To Date'),
                    ])
                    ->action(function (array $data) {
                        return redirect()->to(route('bookings.export.pdf', [
                            'from_date' => $data['from_date'],
                            'to_date' => $data['to_date'],
                        ]));
                    }),
                Actions\Action::make('download-csv')
                    ->label('Download CSV')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from_date')->label('From Date'),
                        \Filament\Forms\Components\DatePicker::make('to_date')->label('To Date'),
                    ])
                    ->action(function (array $data) {
                        return redirect()->to(route('bookings.export.csv', [
                            'from_date' => $data['from_date'],
                            'to_date' => $data['to_date'],
                        ]));
                    }),
                Actions\Action::make('print-pdf')
                    ->label('Print (PDF)')
                    ->icon('heroicon-m-printer')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from_date')->label('From Date'),
                        \Filament\Forms\Components\DatePicker::make('to_date')->label('To Date'),
                    ])
                    ->action(function (array $data) {
                        return redirect()->to(route('bookings.export.print', [
                            'from_date' => $data['from_date'],
                            'to_date' => $data['to_date'],
                        ]));
                    }),
            ])
            ->label('Actions')
            ->icon('heroicon-m-ellipsis-vertical'),
        ];
    }
}
