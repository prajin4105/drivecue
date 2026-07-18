<?php

namespace App\Filament\Resources\VehicleRecords\Pages;

use App\Filament\Resources\VehicleRecords\VehicleRecordResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVehicleRecord extends EditRecord
{
    protected static string $resource = VehicleRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
