<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->required(),
                TextInput::make('last_name')
                    ->required(),
                TextInput::make('center_name'),
                Textarea::make('center_address')
                    ->columnSpanFull(),
                TextInput::make('mobile')
                    ->required(),
                TextInput::make('password')
                    ->password()
                    ->required(),
                TextInput::make('role')
                    ->required()
                    ->numeric()
                    ->default(1),
                Toggle::make('mobile_verified')
                    ->required(),
                Textarea::make('whatsapp_message_template')
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                FileUpload::make('profile_image')
                    ->image(),
            ]);
    }
}
