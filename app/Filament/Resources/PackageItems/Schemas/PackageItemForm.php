<?php

namespace App\Filament\Resources\PackageItems\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PackageItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('package_id')
                    ->required()
                    ->numeric(),
                TextInput::make('product_id')
                    ->required()
                    ->numeric(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->default(1),
            ]);
    }
}
