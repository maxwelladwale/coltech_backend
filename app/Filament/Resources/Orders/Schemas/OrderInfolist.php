<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class OrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('order_number'),
                TextEntry::make('user_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('guest_email')
                    ->placeholder('-'),
                TextEntry::make('subtotal')
                    ->numeric(),
                TextEntry::make('tax')
                    ->numeric(),
                TextEntry::make('shipping')
                    ->numeric(),
                TextEntry::make('total')
                    ->numeric(),
                TextEntry::make('status')
                    ->badge(),
                TextEntry::make('payment_status')
                    ->badge(),
                TextEntry::make('payment_method')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('payment_transaction_id')
                    ->placeholder('-'),
                TextEntry::make('tracking_number')
                    ->placeholder('-')
                    ->visible(fn (Order $record): bool => in_array($record->status, ['shipped', 'delivered'])),
                TextEntry::make('carrier')
                    ->badge()
                    ->placeholder('-')
                    ->visible(fn (Order $record): bool => in_array($record->status, ['shipped', 'delivered'])),
                TextEntry::make('tracking_url')
                    ->url(fn (Order $record): ?string => $record->tracking_url)
                    ->placeholder('-')
                    ->visible(fn (Order $record): bool => in_array($record->status, ['shipped', 'delivered'])),
                TextEntry::make('shipping_name'),
                TextEntry::make('shipping_phone'),
                TextEntry::make('shipping_email'),
                TextEntry::make('shipping_address')
                    ->columnSpanFull(),
                TextEntry::make('shipping_city'),
                TextEntry::make('shipping_county'),
                TextEntry::make('shipping_postal_code')
                    ->placeholder('-'),
                TextEntry::make('installation_method')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('garage_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('appointment_date')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('appointment_time')
                    ->placeholder('-'),
                TextEntry::make('vehicle_registration')
                    ->placeholder('-'),
                TextEntry::make('vehicle_make')
                    ->placeholder('-'),
                TextEntry::make('vehicle_model')
                    ->placeholder('-'),
                TextEntry::make('invoice_url')
                    ->label('Invoice')
                    ->url(fn (Order $record): ?string => $record->invoice_url)
                    ->openUrlInNewTab()
                    ->placeholder('Not generated yet')
                    ->visible(fn (Order $record): bool => !empty($record->invoice_url)),
                TextEntry::make('invoice_qr_code')
                    ->label('Invoice QR Code')
                    ->placeholder('-')
                    ->visible(fn (Order $record): bool => !empty($record->invoice_qr_code)),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Order $record): bool => $record->trashed()),
            ]);
    }
}
