<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\PartnerGarage;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->required()
                    ->unique(ignoreRecord: true),
                Select::make('user_id')
                    ->label('Customer')
                    ->options(User::all()->pluck('full_name', 'id'))
                    ->searchable()
                    ->placeholder('Leave empty for guest checkout'),
                TextInput::make('guest_email')
                    ->label('Guest Email')
                    ->email()
                    ->visible(fn (Get $get) => !$get('user_id')),

                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->prefix('KES'),
                TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('KES'),
                TextInput::make('shipping')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('KES'),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->prefix('KES'),

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
                    ->options(['mpesa' => 'M-Pesa', 'card' => 'Card', 'bank' => 'Bank']),
                TextInput::make('payment_transaction_id')
                    ->label('Transaction ID'),

                TextInput::make('tracking_number')
                    ->label('Tracking Number')
                    ->visible(fn (Get $get) => in_array($get('status'), ['shipped', 'delivered'])),
                Select::make('carrier')
                    ->label('Shipping Carrier')
                    ->options([
                        'dhl' => 'DHL',
                        'fedex' => 'FedEx',
                        'ups' => 'UPS',
                        'posta' => 'Posta Kenya',
                        'skynet' => 'Skynet',
                        'other' => 'Other',
                    ])
                    ->searchable()
                    ->visible(fn (Get $get) => in_array($get('status'), ['shipped', 'delivered'])),
                TextInput::make('tracking_url')
                    ->label('Tracking URL')
                    ->url()
                    ->visible(fn (Get $get) => in_array($get('status'), ['shipped', 'delivered'])),

                TextInput::make('shipping_name')
                    ->label('Ship To: Full Name')
                    ->required(),
                TextInput::make('shipping_phone')
                    ->label('Ship To: Phone')
                    ->tel()
                    ->required(),
                TextInput::make('shipping_email')
                    ->label('Ship To: Email')
                    ->email()
                    ->required(),
                Textarea::make('shipping_address')
                    ->label('Shipping Address')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('shipping_city')
                    ->label('City')
                    ->required(),
                TextInput::make('shipping_county')
                    ->label('County')
                    ->required(),
                TextInput::make('shipping_postal_code')
                    ->label('Postal Code'),

                Select::make('installation_method')
                    ->label('Installation Method')
                    ->options(['self' => 'Self Installation', 'technician' => 'Technician Installation'])
                    ->live(),
                Select::make('garage_id')
                    ->label('Partner Garage')
                    ->options(PartnerGarage::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->visible(fn (Get $get) => $get('installation_method') === 'technician')
                    ->required(fn (Get $get) => $get('installation_method') === 'technician'),
                DateTimePicker::make('appointment_date')
                    ->label('Appointment Date & Time')
                    ->visible(fn (Get $get) => $get('installation_method') === 'technician'),
                TextInput::make('appointment_time')
                    ->label('Appointment Time')
                    ->visible(fn (Get $get) => $get('installation_method') === 'technician'),
                TextInput::make('vehicle_registration')
                    ->label('Vehicle Registration')
                    ->visible(fn (Get $get) => $get('installation_method') === 'technician'),
                TextInput::make('vehicle_make')
                    ->label('Vehicle Make')
                    ->visible(fn (Get $get) => $get('installation_method') === 'technician'),
                TextInput::make('vehicle_model')
                    ->label('Vehicle Model')
                    ->visible(fn (Get $get) => $get('installation_method') === 'technician'),

                TextInput::make('invoice_url')
                    ->label('Invoice URL')
                    ->url(),
                TextInput::make('invoice_qr_code')
                    ->label('QR Code'),
            ]);
    }
}
