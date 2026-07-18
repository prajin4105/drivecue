<?php

namespace App\Filament\Resources\VehicleRecords\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VehicleRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.center_name')
                    ->label('Center')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer_name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer_mobile')
                    ->searchable(),
                TextColumn::make('vehicle_number')
                    ->sortable()
                    ->searchable()
                    ->badge(),
                TextColumn::make('vehicle_type')
                    ->sortable(),
                TextColumn::make('fuel_type'),
                TextColumn::make('expiry_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('vehicle_type')
                    ->options([
                        'Bike' => 'Bike',
                        'Car' => 'Car',
                        'Auto' => 'Auto',
                        'Truck' => 'Truck',
                        'Bus' => 'Bus',
                        'Other' => 'Other',
                    ]),
                SelectFilter::make('fuel_type')
                    ->options([
                        'Petrol' => 'Petrol',
                        'Diesel' => 'Diesel',
                        'CNG' => 'CNG',
                        'LPG' => 'LPG',
                        'Hybrid' => 'Hybrid',
                    ]),
            ])
            ->recordActions([])
            ->bulkActions([]);
    }
}
