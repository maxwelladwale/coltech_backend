<?php

namespace App\Filament\Resources\OrderItems\Schemas;

use App\Models\Order;
use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Schemas\Schema;

class OrderItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('order_id')
                    ->label('Order')
                    ->options(Order::all()->pluck('order_number', 'id'))
                    ->required()
                    ->searchable(),
                Select::make('product_id')
                    ->label('Product')
                    ->options(Product::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state) {
                        if ($state) {
                            $product = Product::find($state);
                            if ($product) {
                                $set('product_name', $product->name);
                                $set('product_sku', $product->sku);
                                $set('product_category', $product->category);
                                $set('unit_price', $product->price);
                            }
                        }
                    }),
                TextInput::make('product_name')
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('product_sku')
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('product_category')
                    ->required()
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->live()
                    ->afterStateUpdated(function (Set $set, ?string $state, Get $get) {
                        $quantity = (int) $state;
                        $unitPrice = (float) $get('unit_price');
                        $set('total_price', $quantity * $unitPrice);
                    }),
                TextInput::make('unit_price')
                    ->required()
                    ->numeric()
                    ->prefix('KES')
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('total_price')
                    ->required()
                    ->numeric()
                    ->prefix('KES')
                    ->disabled()
                    ->dehydrated(),
                TextInput::make('customizations')
                    ->label('Customizations (JSON)')
                    ->helperText('Optional product customizations'),
            ]);
    }
}
