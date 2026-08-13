<?php

namespace App\Filament\Resources\FerryRouteResource\Pages;

use App\Filament\Resources\FerryRouteResource;
use App\Models\Vehicle;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFerryRoute extends EditRecord
{
    protected static string $resource = FerryRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['vehicle_id'])) {
            $data['operator_id'] = optional(Vehicle::find($data['vehicle_id']))->operator_id;
        }

        return $data;
    }
}
