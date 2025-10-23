<?php

namespace App\Filament\Resources\Notifications\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class NotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_id')
                    ->numeric(),
                Select::make('type')
                    ->options(['email' => 'Email', 'sms' => 'Sms', 'whatsapp' => 'Whatsapp'])
                    ->required(),
                Select::make('status')
                    ->options(['pending' => 'Pending', 'sent' => 'Sent', 'failed' => 'Failed'])
                    ->required(),
                TextInput::make('recipient')
                    ->required(),
                TextInput::make('subject'),
                Textarea::make('message')
                    ->required()
                    ->columnSpanFull(),
                DateTimePicker::make('sent_at'),
                Textarea::make('error_message')
                    ->columnSpanFull(),
                TextInput::make('retry_count')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
