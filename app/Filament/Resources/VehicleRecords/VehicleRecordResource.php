<?php

namespace App\Filament\Resources\VehicleRecords;

use App\Filament\Resources\VehicleRecords\Pages\CreateVehicleRecord;
use App\Filament\Resources\VehicleRecords\Pages\EditVehicleRecord;
use App\Filament\Resources\VehicleRecords\Pages\ListVehicleRecords;
use App\Filament\Resources\VehicleRecords\Schemas\VehicleRecordForm;
use App\Filament\Resources\VehicleRecords\Tables\VehicleRecordsTable;
use App\Models\VehicleRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VehicleRecordResource extends Resource
{
    protected static ?string $model = VehicleRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static \UnitEnum|string|null $navigationGroup = 'Customers';

    protected static ?string $recordTitleAttribute = 'vehicle_number';

    public static function form(Schema $schema): Schema
    {
        return VehicleRecordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VehicleRecordsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVehicleRecords::route('/'),
        ];
    }
    
    public static function canCreate(): bool
    {
        return false;
    }
}
