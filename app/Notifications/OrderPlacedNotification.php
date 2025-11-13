<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        \Log::info('Sending order confirmation to customer', [
            'order_number' => $this->order->order_number,
            'customer_email' => $notifiable->email ?? $this->order->shipping_email,
            'customer_name' => $this->order->getCustomerName(),
            'order_total' => $this->order->total,
        ]);

        return (new MailMessage)
            ->subject('Order Confirmation - ' . $this->order->order_number)
            ->greeting('Hello ' . $this->order->getCustomerName() . '!')
            ->line('Thank you for your order. We have received your order and it is being processed.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Order Total:** KES ' . number_format($this->order->total, 2))
            ->line('**Status:** ' . ucfirst($this->order->status))
            ->line('**Payment Status:** ' . ucfirst($this->order->payment_status))
            ->action('View Order', url('/orders/' . $this->order->id))
            ->line('We will notify you when your order status changes.')
            ->line('Thank you for shopping with us!');
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
            'total' => $this->order->total,
            'status' => $this->order->status,
        ];
    }
}
