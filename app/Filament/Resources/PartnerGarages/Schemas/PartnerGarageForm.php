<?php

namespace App\Filament\Resources\PartnerGarages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PartnerGarageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('location')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('county')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('rating')
                    ->required()
                    ->numeric()
                    ->maxValue(10)
                    ->minValue(0)
                    ->maxLength(2)
                    ->default(0.0),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('operating_hours'),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
