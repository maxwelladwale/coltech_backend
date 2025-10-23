<?php

namespace App\Filament\Resources\PartnerGarages;

use App\Filament\Resources\PartnerGarages\Pages\CreatePartnerGarage;
use App\Filament\Resources\PartnerGarages\Pages\EditPartnerGarage;
use App\Filament\Resources\PartnerGarages\Pages\ListPartnerGarages;
use App\Filament\Resources\PartnerGarages\Pages\ViewPartnerGarage;
use App\Filament\Resources\PartnerGarages\Schemas\PartnerGarageForm;
use App\Filament\Resources\PartnerGarages\Schemas\PartnerGarageInfolist;
use App\Filament\Resources\PartnerGarages\Tables\PartnerGaragesTable;
use App\Models\PartnerGarage;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PartnerGarageResource extends Resource
{
    protected static ?string $model = PartnerGarage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Partners';
    
    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PartnerGarageForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PartnerGarageInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnerGaragesTable::configure($table);
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
            'index' => ListPartnerGarages::route('/'),
            'create' => CreatePartnerGarage::route('/create'),
            'view' => ViewPartnerGarage::route('/{record}'),
            'edit' => EditPartnerGarage::route('/{record}/edit'),
        ];
    }
}
