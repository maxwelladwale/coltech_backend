<?php

namespace App\Filament\Resources\PartnerGarages\Pages;

use App\Filament\Resources\PartnerGarages\PartnerGarageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPartnerGarages extends ListRecords
{
    protected static string $resource = PartnerGarageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
