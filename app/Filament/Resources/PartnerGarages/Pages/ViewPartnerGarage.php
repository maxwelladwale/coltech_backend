<?php

namespace App\Filament\Resources\PartnerGarages\Pages;

use App\Filament\Resources\PartnerGarages\PartnerGarageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPartnerGarage extends ViewRecord
{
    protected static string $resource = PartnerGarageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
