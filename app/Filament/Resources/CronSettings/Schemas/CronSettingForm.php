<?php

namespace App\Filament\Resources\CronSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CronSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('command')
                    ->required(),
                TextInput::make('schedule')
                    ->required()
                    ->helperText('Use a standard cron expression (e.g., "0 9 * * *" for 9:00 AM every day).')
                    ->default('0 9 * * *'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
