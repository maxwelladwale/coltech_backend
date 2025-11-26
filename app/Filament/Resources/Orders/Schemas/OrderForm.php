<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\PartnerGarage;
use App\Models\Product;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('order_number')
                    ->label('Order Number (auto-generated)')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Will be generated automatically'),
                Select::make('user_id')
                    ->label('Customer')
                    ->options(User::all()->pluck('full_name', 'id'))
                    ->searchable()
                    ->placeholder('Leave empty for guest checkout')
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if ($state) {
                            $set('guest_email', null);
                        }
                    }),
                TextInput::make('guest_email')
                    ->label('Guest Email')
                    ->email()
                    ->visible(fn (Get $get) => !$get('user_id'))
                    ->required(fn (Get $get) => !$get('user_id')),

                Repeater::make('items')
                    ->relationship()
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->options(Product::where('in_stock', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                if (!$state) {
                                    // Clear values if product is removed
                                    $set('unit_price', 0);
                                    $set('total_price', 0);
                                    return;
                                }

                                $product = Product::find($state);
                                if ($product) {
                                    $price = floatval($product->price);
                                    $quantity = intval($get('quantity'));

                                    // Ensure quantity is at least 1 and sync it if it was empty
                                    if ($quantity <= 0) {
                                        $quantity = 1;
                                        $set('quantity', 1);
                                    }
                                    
                                    // Set snapshot details
                                    $set('product_name', $product->name);
                                    $set('product_sku', $product->sku);
                                    $set('product_category', $product->category);
                                    
                                    // Set prices directly using the fetched variables
                                    $set('unit_price', $price);
                                    $set('total_price', $price * $quantity);
                                }
                            }),
                        TextInput::make('quantity')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required()
                            ->live() 
                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                                $unitPrice = floatval($get('unit_price') ?? 0);
                                $quantity = intval($state ?? 1);
                                $set('total_price', $unitPrice * $quantity);
                            }),
                        TextInput::make('unit_price')
                            ->label('Unit Price')
                            ->numeric()
                            ->prefix('KES')
                            ->disabled()
                            ->dehydrated(), // Removed reactive() to prevent race conditions
                        TextInput::make('total_price')
                            ->label('Total Price')
                            ->numeric()
                            ->prefix('KES')
                            ->disabled()
                            ->dehydrated(), // Removed reactive() to prevent race conditions
                        TextInput::make('product_name')
                            ->label('Product Name (Snapshot)')
                            ->disabled()
                            ->dehydrated()
                            ->visible(fn (Get $get) => !empty($get('product_name'))),
                        TextInput::make('product_sku')
                            ->label('SKU (Snapshot)')
                            ->disabled()
                            ->dehydrated()
                            ->visible(fn (Get $get) => !empty($get('product_sku'))),
                        TextInput::make('product_category')
                            ->label('Category (Snapshot)')
                            ->disabled()
                            ->dehydrated()
                            ->visible(fn (Get $get) => !empty($get('product_category'))),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->minItems(1)
                    ->addActionLabel('Add Product')
                    ->reorderableWithButtons()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['product_name'] ?? 'New Item')
                    ->columnSpanFull()
                    ->live(debounce: 500)
                    ->afterStateUpdated(function (Set $set, Get $get) {
                        $items = $get('items') ?? [];
                        $subtotal = 0;

                        // Manual calculation loop to ensure accuracy
                        foreach ($items as $item) {
                            $qty = intval($item['quantity'] ?? 1);
                            $price = floatval($item['unit_price'] ?? 0);
                            $subtotal += ($qty * $price);
                        }

                        $set('subtotal', $subtotal);

                        // Recalculate total
                        $tax = floatval($get('tax') ?? 0);
                        $shipping = floatval($get('shipping') ?? 0);
                        $total = $subtotal + $tax + $shipping;

                        $set('total', $total);
                    }),

                TextInput::make('subtotal')
                    ->label('Subtotal (auto-calculated)')
                    ->numeric()
                    ->prefix('KES')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Automatically calculated from order items')
                    ->live(),
                TextInput::make('tax')
                    ->numeric()
                    ->default(0.0)
                    ->prefix('KES')
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                        $subtotal = floatval($get('subtotal') ?? 0);
                        $tax = floatval($state ?? 0);
                        $shipping = floatval($get('shipping') ?? 0);
                        $set('total', $subtotal + $tax + $shipping);
                    }),
                TextInput::make('shipping')
                    ->numeric()
                    ->default(0.0)
                    ->prefix('KES')
                    ->live()
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state) {
                        $subtotal = floatval($get('subtotal') ?? 0);
                        $tax = floatval($get('tax') ?? 0);
                        $shipping = floatval($state ?? 0);
                        $set('total', $subtotal + $tax + $shipping);
                    }),
                TextInput::make('total')
                    ->label('Total (auto-calculated)')
                    ->numeric()
                    ->prefix('KES')
                    ->disabled()
                    ->dehydrated()
                    ->helperText('Subtotal + Tax + Shipping')
                    ->live(),

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
                    ->required()
                    ->live(),
                Select::make('payment_method')
                    ->options(['mpesa' => 'M-Pesa', 'card' => 'Card', 'bank' => 'Bank'])
                    ->visible(fn (Get $get) => in_array($get('payment_status'), ['paid', 'failed']))
                    ->required(fn (Get $get) => $get('payment_status') === 'paid')
                    ->live(),
                TextInput::make('payment_transaction_id')
                    ->label('Transaction ID')
                    ->visible(fn (Get $get) => in_array($get('payment_status'), ['paid', 'failed']))
                    ->required(fn (Get $get) => $get('payment_status') === 'paid'),

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
                    ->label('Invoice URL (auto-generated)')
                    ->url()
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn ($record) => $record !== null && !empty($record->invoice_url))
                    ->helperText('Invoice will be generated automatically after order creation'),
                TextInput::make('invoice_qr_code')
                    ->label('Invoice QR Code (auto-generated)')
                    ->disabled()
                    ->dehydrated(false)
                    ->visible(fn ($record) => $record !== null && !empty($record->invoice_qr_code)),
            ]);
    }
}