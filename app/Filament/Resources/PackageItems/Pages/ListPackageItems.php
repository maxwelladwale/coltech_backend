<?php

namespace App\Filament\Resources\PackageItems\Pages;

use App\Filament\Resources\PackageItems\PackageItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPackageItems extends ListRecords
{
    protected static string $resource = PackageItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
