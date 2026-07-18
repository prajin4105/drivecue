<?php

namespace App\Filament\Resources\ReminderLogs;

use App\Filament\Resources\ReminderLogs\Pages\ManageReminderLogs;
use App\Models\ReminderLog;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReminderLogResource extends Resource
{
    protected static ?string $model = ReminderLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftEllipsis;

    protected static \UnitEnum|string|null $navigationGroup = 'Communications';

    protected static ?string $recordTitleAttribute = 'customer_mobile';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'id')
                    ->required(),
                Select::make('vehicle_record_id')
                    ->relationship('vehicleRecord', 'id'),
                TextInput::make('customer_mobile')
                    ->required(),
                TextInput::make('message_type')
                    ->required(),
                TextInput::make('reminder_stage')
                    ->required(),
                Textarea::make('message_body')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                Textarea::make('provider_response')
                    ->columnSpanFull(),
                DateTimePicker::make('sent_at'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.id')
                    ->label('User'),
                TextEntry::make('vehicleRecord.id')
                    ->label('Vehicle record')
                    ->placeholder('-'),
                TextEntry::make('customer_mobile'),
                TextEntry::make('message_type'),
                TextEntry::make('reminder_stage'),
                TextEntry::make('message_body')
                    ->columnSpanFull(),
                TextEntry::make('status'),
                TextEntry::make('provider_response')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('sent_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('customer_mobile')
            ->columns([
                TextColumn::make('user.center_name')
                    ->label('Center')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('vehicleRecord.vehicle_number')
                    ->label('Vehicle')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer_mobile')
                    ->searchable(),
                TextColumn::make('message_type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('reminder_stage')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sent' => 'success',
                        'failed' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('sent_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                        'pending' => 'Pending',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('message_type')
                    ->options([
                        'whatsapp' => 'WhatsApp',
                        'sms' => 'SMS',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageReminderLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
