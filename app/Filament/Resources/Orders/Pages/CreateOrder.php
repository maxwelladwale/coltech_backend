<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderAssignedToGarageNotification;
use App\Notifications\OrderPlacedNotification;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['order_number'] = \App\Models\Order::generateOrderNumber();
        return $data;
    }

    protected function afterCreate(): void
    {
        $order = $this->record;
        $order->load('items', 'garage', 'user');

        \Log::info('Order created via Filament', [
            'order_number' => $order->order_number,
            'has_customer' => (bool) $order->user,
        ]);

        // Send order confirmation email to customer
        if ($order->user) {
            // Registered user
            \Log::info('Queueing order confirmation email (Filament - registered user)', [
                'order_number' => $order->order_number,
                'customer_email' => $order->user->email,
            ]);
            $order->user->notify(new OrderPlacedNotification($order));
        } else {
            // Guest user
            \Log::info('Queueing order confirmation email (Filament - guest)', [
                'order_number' => $order->order_number,
                'guest_email' => $order->shipping_email,
            ]);
            \Illuminate\Support\Facades\Notification::route('mail', $order->shipping_email)
                ->notify(new OrderPlacedNotification($order));
        }

        // Send notification to admin users
        $adminUsers = User::where('role', 'admin')->get();
        \Log::info('Queueing admin notifications (Filament)', [
            'order_number' => $order->order_number,
            'admin_count' => $adminUsers->count(),
            'admin_emails' => $adminUsers->pluck('email')->toArray(),
        ]);
        foreach ($adminUsers as $admin) {
            $admin->notify(new NewOrderNotification($order));
        }

        // Send notification to garage if installation is required
        if ($order->garage_id && $order->garage) {
            \Log::info('Queueing garage notification (Filament)', [
                'order_number' => $order->order_number,
                'garage_name' => $order->garage->name,
                'garage_email' => $order->garage->email,
            ]);
            \Illuminate\Support\Facades\Notification::route('mail', $order->garage->email)
                ->notify(new OrderAssignedToGarageNotification($order));
        }
    }
}
