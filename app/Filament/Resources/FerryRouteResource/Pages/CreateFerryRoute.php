<?php

namespace App\Filament\Resources\FerryRouteResource\Pages;

use App\Filament\Resources\FerryRouteResource;
use App\Models\Vehicle;
use Filament\Resources\Pages\CreateRecord;

class CreateFerryRoute extends CreateRecord
{
    protected static string $resource = FerryRouteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['vehicle_id'])) {
            $data['operator_id'] = optional(Vehicle::find($data['vehicle_id']))->operator_id;
        }

        return $data;
    }
}
