<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Order $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        \Log::info('Sending admin notification for new order', [
            'order_number' => $this->order->order_number,
            'admin_email' => $notifiable->email ?? 'unknown',
            'admin_name' => $notifiable->full_name ?? 'unknown',
            'order_total' => $this->order->total,
        ]);

        $itemsCount = $this->order->items->count();
        $itemsList = $this->order->items->map(function ($item) {
            return "- {$item->product_name} (x{$item->quantity}) - KES " . number_format($item->total_price, 2);
        })->join("\n");

        $installationInfo = '';
        if ($this->order->installation_method === 'technician' && $this->order->garage) {
            $installationInfo = "\n**Installation Details:**\n" .
                "- Method: Professional Installation\n" .
                "- Garage: {$this->order->garage->name}\n" .
                "- Vehicle: {$this->order->vehicle_make} {$this->order->vehicle_model} ({$this->order->vehicle_registration})";
        } elseif ($this->order->installation_method === 'self') {
            $installationInfo = "\n**Installation Method:** Self Installation";
        }

        return (new MailMessage)
            ->subject('New Order Received - ' . $this->order->order_number)
            ->greeting('New Order Alert!')
            ->line('A new order has been placed on your store.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Customer:** ' . $this->order->getCustomerName())
            ->line('**Email:** ' . $this->order->shipping_email)
            ->line('**Phone:** ' . $this->order->shipping_phone)
            ->line('**Order Total:** KES ' . number_format($this->order->total, 2))
            ->line('**Payment Method:** ' . ucfirst($this->order->payment_method))
            ->line("\n**Order Items:** ({$itemsCount} items)")
            ->line($itemsList)
            ->line($installationInfo)
            ->line("\n**Shipping Address:**")
            ->line($this->order->shipping_address . ', ' . $this->order->shipping_city . ', ' . $this->order->shipping_county)
            ->action('Manage Order in Admin', url('/admin/orders/' . $this->order->id))
            ->line('Please process this order as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'customer_name' => $this->order->getCustomerName(),
            'total' => $this->order->total,
            'items_count' => $this->order->items->count(),
        ];
    }
}
