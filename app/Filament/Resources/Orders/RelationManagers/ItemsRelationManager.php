<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\Product;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_number')
            ->columns([
                TextColumn::make('product_name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('product_sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('product_category')
                    ->label('Category')
                    ->badge()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label('Unit Price')
                    ->money('KES')
                    ->sortable(),
                TextColumn::make('total_price')
                    ->label('Total')
                    ->money('KES')
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
