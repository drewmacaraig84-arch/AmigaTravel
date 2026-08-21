<?php

namespace App\Filament\Resources\AirlineBaggageRuleResource\Pages;

use App\Filament\Resources\AirlineBaggageRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAirlineBaggageRules extends ListRecords
{
    protected static string $resource = AirlineBaggageRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('add_ceb_pac')
                ->label('+ Cebu Pacific Rule')
                ->color('success')
                ->icon('heroicon-o-plus-circle')
                ->url(fn (): string => static::getResource()::getUrl('create', ['operator' => 'ceb_pac'])),

            Actions\Action::make('add_pal')
                ->label('+ PAL Rule')
                ->color('info')
                ->icon('heroicon-o-plus-circle')
                ->url(fn (): string => static::getResource()::getUrl('create', ['operator' => 'pal'])),

            Actions\Action::make('add_airasia')
                ->label('+ AirAsia Rule')
                ->color('warning')
                ->icon('heroicon-o-plus-circle')
                ->url(fn (): string => static::getResource()::getUrl('create', ['operator' => 'airasia'])),

            Actions\CreateAction::make()
                ->label('New airline baggage rule'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Components\Tab::make('All Airlines')
                ->badge(\App\Models\AirlineBaggageRule::count()),
            'ceb_pac' => \Filament\Resources\Components\Tab::make('Cebu Pacific')
                ->badge(\App\Models\AirlineBaggageRule::where('operator', 'ceb_pac')->count())
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('operator', 'ceb_pac')),
            'pal' => \Filament\Resources\Components\Tab::make('Philippine Airlines')
                ->badge(\App\Models\AirlineBaggageRule::where('operator', 'pal')->count())
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('operator', 'pal')),
            'airasia' => \Filament\Resources\Components\Tab::make('AirAsia')
                ->badge(\App\Models\AirlineBaggageRule::where('operator', 'airasia')->count())
                ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->where('operator', 'airasia')),
        ];
    }
}
