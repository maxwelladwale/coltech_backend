<?php

namespace App\Filament\Resources\PartnerGarages\Pages;

use App\Filament\Resources\PartnerGarages\PartnerGarageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPartnerGarage extends EditRecord
{
    protected static string $resource = PartnerGarageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
