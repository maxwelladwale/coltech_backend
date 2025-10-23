<?php

namespace App\Filament\Resources\PackageItems;

use App\Filament\Resources\PackageItems\Pages\CreatePackageItem;
use App\Filament\Resources\PackageItems\Pages\EditPackageItem;
use App\Filament\Resources\PackageItems\Pages\ListPackageItems;
use App\Filament\Resources\PackageItems\Schemas\PackageItemForm;
use App\Filament\Resources\PackageItems\Tables\PackageItemsTable;
use App\Models\PackageItem;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PackageItemResource extends Resource
{
    protected static ?string $model = PackageItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 3;
    
    public static function form(Schema $schema): Schema
    {
        return PackageItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PackageItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPackageItems::route('/'),
            'create' => CreatePackageItem::route('/create'),
            'edit' => EditPackageItem::route('/{record}/edit'),
        ];
    }
}
