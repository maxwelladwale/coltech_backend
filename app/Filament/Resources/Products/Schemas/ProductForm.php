<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Select::make('category')
                    ->options([
            'mdvr' => 'Mdvr',
            'camera' => 'Camera',
            'cable' => 'Cable',
            'accessory' => 'Accessory',
            'installation' => 'Installation',
            'license' => 'License',
        ])
                    ->required(),
                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('short_description')
                    ->columnSpanFull(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                FileUpload::make('image_url')
                    ->image(),
                TextInput::make('video_url')
                    ->url(),
                Toggle::make('in_stock')
                    ->required(),
                TextInput::make('stock_quantity')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('includes_free_license'),
                Select::make('license_type')
                    ->options(['ai' => 'Ai', 'non-ai' => 'Non ai']),
                TextInput::make('license_duration_months')
                    ->numeric(),
                TextInput::make('channels')
                    ->numeric(),
                TextInput::make('storage_options'),
                TextInput::make('features'),
                TextInput::make('specifications'),
            ]);
    }
}
