<?php

namespace App\Filament\Resources\OrderItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrderItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.order_number')
                    ->label('Order')
                    ->sortable()
                    ->searchable(),
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
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
