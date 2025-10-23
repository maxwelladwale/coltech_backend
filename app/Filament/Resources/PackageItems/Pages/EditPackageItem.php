<?php

namespace App\Filament\Resources\PackageItems\Pages;

use App\Filament\Resources\PackageItems\PackageItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPackageItem extends EditRecord
{
    protected static string $resource = PackageItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
