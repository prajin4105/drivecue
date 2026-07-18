<?php

namespace App\Filament\Resources\CronLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CronLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('command')
                    ->required(),
                TextInput::make('status')
                    ->required(),
                Textarea::make('output')
                    ->columnSpanFull(),
                DateTimePicker::make('started_at')
                    ->required(),
                DateTimePicker::make('completed_at'),
            ]);
    }
}
