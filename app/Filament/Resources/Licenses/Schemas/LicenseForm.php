<?php

namespace App\Filament\Resources\Licenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LicenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('license_key')
                    ->required(),
                TextInput::make('mdvr_serial_number'),
                TextInput::make('vehicle_registration')
                    ->required(),
                Select::make('type')
                    ->options(['ai' => 'Ai', 'non-ai' => 'Non ai'])
                    ->required(),
                Select::make('status')
                    ->options(['active' => 'Active', 'expired' => 'Expired', 'suspended' => 'Suspended'])
                    ->default('active')
                    ->required(),
                DatePicker::make('activation_date')
                    ->required(),
                DatePicker::make('expiry_date')
                    ->required(),
                TextInput::make('renewal_price')
                    ->required()
                    ->numeric(),
                TextInput::make('order_id')
                    ->numeric(),
                TextInput::make('user_id')
                    ->numeric(),
            ]);
    }
}
