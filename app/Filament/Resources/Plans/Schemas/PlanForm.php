<?php

namespace App\Filament\Resources\Plans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->label('Pricing description')
                    ->helperText('Shown to customers on the pricing page.')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('is_trial')
                    ->required(),
                Toggle::make('is_popular')
                    ->required(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('monthly_price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('₹'),
                TextInput::make('yearly_price')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('₹'),
                TextInput::make('customer_limit')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('whatsapp_limit')
                    ->label('WhatsApp message limit')
                    ->helperText('Messages included per billing period. Enter 0 for ∞ Unlimited.')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                TextInput::make('status')
                    ->required()
                    ->default('active'),
            ]);
    }
}
