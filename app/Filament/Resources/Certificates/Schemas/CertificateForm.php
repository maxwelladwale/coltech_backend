<?php

namespace App\Filament\Resources\Certificates\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CertificateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('certificate_number')
                    ->required(),
                Select::make('type')
                    ->options(['installation' => 'Installation', 'license' => 'License', 'product' => 'Product'])
                    ->required(),
                TextInput::make('qr_code')
                    ->required(),
                TextInput::make('issued_to')
                    ->required(),
                DatePicker::make('issued_date')
                    ->required(),
                DatePicker::make('expiry_date'),
                TextInput::make('details')
                    ->required(),
                TextInput::make('order_id')
                    ->numeric(),
                TextInput::make('license_id')
                    ->numeric(),
                TextInput::make('pdf_url')
                    ->url(),
            ]);
    }
}
