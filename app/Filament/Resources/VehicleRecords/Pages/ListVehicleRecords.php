<?php

namespace App\Filament\Resources\VehicleRecords\Pages;

use App\Filament\Resources\VehicleRecords\VehicleRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicleRecords extends ListRecords
{
    protected static string $resource = VehicleRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
