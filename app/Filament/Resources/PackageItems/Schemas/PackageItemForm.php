<?php

namespace App\Filament\Resources\PackageItems\Schemas;

use App\Models\Package;
use App\Models\Product;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PackageItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('package_id')
                    ->label('Package')
                    ->options(Package::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                Select::make('product_id')
                    ->label('Product')
                    ->options(Product::all()->pluck('name', 'id'))
                    ->required()
                    ->searchable(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1)
                    ->minValue(1),
            ]);
    }
}
