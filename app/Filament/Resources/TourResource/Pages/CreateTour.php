<?php

namespace App\Filament\Resources\TourResource\Pages;

use App\Filament\Resources\TourResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTour extends CreateRecord
{
    protected static string $resource = TourResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Prevent NOT NULL constraint violation: default destinations to ''
        $data['destinations'] = $data['destinations'] ?? '';

        return $data;
    }
}

