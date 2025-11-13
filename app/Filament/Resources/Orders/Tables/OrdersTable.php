<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\PaymentReceivedNotification;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('user.full_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->default('Guest'),
                TextColumn::make('total')
                    ->money('KES')
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'confirmed' => 'info',
                        'processing' => 'warning',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'failed' => 'danger',
                    })
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('shipping_name')
                    ->label('Ship To')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('shipping_phone')
                    ->label('Phone')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('shipping_city')
                    ->label('City')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('installation_method')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('garage.name')
                    ->label('Garage')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('vehicle_registration')
                    ->label('Vehicle Reg.')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Ordered At')
                    ->dateTime('M j, Y')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),
                SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                    ])
                    ->multiple(),
                SelectFilter::make('payment_method')
                    ->options([
                        'mpesa' => 'M-Pesa',
                        'card' => 'Card',
                        'bank' => 'Bank',
                    ]),
                SelectFilter::make('installation_method')
                    ->options([
                        'self' => 'Self Installation',
                        'technician' => 'Technician',
                    ]),
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('Ordered From'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Ordered Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('markAsPaid')
                    ->label('Mark as Paid')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->payment_status !== 'paid')
                    ->action(function (Order $record) {
                        $record->update(['payment_status' => 'paid']);

                        // Send email to customer
                        $message = "Order {$record->order_number} has been marked as paid.";
                        if ($record->user) {
                            // Registered user
                            $record->user->notify(new PaymentReceivedNotification($record));
                            $message .= " Customer notified via email {$record->user->email}.";
                        } elseif ($record->shipping_email) {
                            // Guest user
                            \Illuminate\Support\Facades\Notification::route('mail', $record->shipping_email)
                                ->notify(new PaymentReceivedNotification($record));
                            $message .= " Guest customer notified via email {$record->shipping_email}.";
                        } else {
                            $message .= " No email address found for customer.";
                        }

                        Notification::make()
                            ->success()
                            ->title('Order marked as paid')
                            ->body($message)
                            ->send();
                    }),
                Action::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        \Filament\Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'confirmed' => 'Confirmed',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data) {
                        $oldStatus = $record->status;
                        $record->update(['status' => $data['status']]);

                        // Reload relationships
                        $record->load('garage');

                        // Send email to customer
                        $message = "Order {$record->order_number} status changed from {$oldStatus} to {$data['status']}.";
                        if ($record->user) {
                            // Registered user
                            $record->user->notify(new OrderStatusChangedNotification($record, $oldStatus, $data['status']));
                            $message .= " Customer notified via email {$record->user->email}.";
                        } elseif ($record->shipping_email) {
                            // Guest user
                            \Illuminate\Support\Facades\Notification::route('mail', $record->shipping_email)
                                ->notify(new OrderStatusChangedNotification($record, $oldStatus, $data['status']));
                            $message .= " Guest customer notified via email {$record->shipping_email}.";
                        } else {
                            $message .= " No email address found for customer.";
                        }

                        Notification::make()
                            ->success()
                            ->title('Status updated')
                            ->body($message)
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
