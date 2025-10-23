<?php

namespace App\Filament\Resources\Licenses\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LicenseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('license_key'),
                TextEntry::make('mdvr_serial_number')
                    ->placeholder('-'),
                TextEntry::make('vehicle_registration'),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('activation_date')
                    ->date(),
                TextEntry::make('expiry_date')
                    ->date(),
                TextEntry::make('renewal_price')
                    ->numeric(),
                TextEntry::make('order_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('user_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
