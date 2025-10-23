<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required(),
                TextInput::make('user_id')
                    ->numeric(),
                TextInput::make('guest_email')
                    ->email(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric(),
                TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('shipping')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                TextInput::make('total')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options([
            'pending' => 'Pending',
            'confirmed' => 'Confirmed',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ])
                    ->default('pending')
                    ->required(),
                Select::make('payment_status')
                    ->options(['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed'])
                    ->default('pending')
                    ->required(),
                Select::make('payment_method')
                    ->options(['mpesa' => 'Mpesa', 'card' => 'Card', 'bank' => 'Bank']),
                TextInput::make('payment_transaction_id'),
                TextInput::make('shipping_name')
                    ->required(),
                TextInput::make('shipping_phone')
                    ->tel()
                    ->required(),
                TextInput::make('shipping_email')
                    ->email()
                    ->required(),
                Textarea::make('shipping_address')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('shipping_city')
                    ->required(),
                TextInput::make('shipping_county')
                    ->required(),
                TextInput::make('shipping_postal_code'),
                Select::make('installation_method')
                    ->options(['self' => 'Self', 'technician' => 'Technician']),
                TextInput::make('garage_id')
                    ->numeric(),
                DateTimePicker::make('appointment_date'),
                TextInput::make('appointment_time'),
                TextInput::make('vehicle_registration'),
                TextInput::make('vehicle_make'),
                TextInput::make('vehicle_model'),
                TextInput::make('invoice_url')
                    ->url(),
                TextInput::make('invoice_qr_code'),
            ]);
    }
}
