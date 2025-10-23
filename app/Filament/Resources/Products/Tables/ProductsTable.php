<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('category')
                    ->badge(),
                TextColumn::make('price')
                    ->money()
                    ->sortable(),
                ImageColumn::make('image_url'),
                TextColumn::make('video_url')
                    ->searchable(),
                IconColumn::make('in_stock')
                    ->boolean(),
                TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('includes_free_license')
                    ->boolean(),
                TextColumn::make('license_type')
                    ->badge(),
                TextColumn::make('license_duration_months')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('channels')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'mdvr' => 'MDVR',
                        'camera' => 'Camera',
                    ]),
                TernaryFilter::make('in_stock')
                    ->label('Stock Status')
                    ->placeholder('All products')
                    ->trueLabel('In Stock')
                    ->falseLabel('Out of Stock'),
                TernaryFilter::make('includes_free_license')
                    ->label('Free License')
                    ->placeholder('All products')
                    ->trueLabel('Includes License')
                    ->falseLabel('No License'),
                SelectFilter::make('license_type')
                    ->options([
                        'basic' => 'Basic',
                        'standard' => 'Standard',
                        'premium' => 'Premium',
                    ])
                    ->label('License Type'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
