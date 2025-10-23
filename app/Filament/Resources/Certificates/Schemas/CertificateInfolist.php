<?php

namespace App\Filament\Resources\Certificates\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CertificateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('certificate_number'),
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('qr_code'),
                TextEntry::make('issued_to'),
                TextEntry::make('issued_date')
                    ->date(),
                TextEntry::make('expiry_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('order_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('license_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('pdf_url')
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
