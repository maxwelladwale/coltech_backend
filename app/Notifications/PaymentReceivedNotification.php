<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('Payment Received - ' . $this->order->order_number)
            ->greeting('Hello ' . $this->order->getCustomerName() . '!')
            ->line('We have received your payment. Thank you!')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Amount Paid:** KES ' . number_format($this->order->total, 2))
            ->line('**Payment Method:** ' . ucfirst($this->order->payment_method ?? 'N/A'))
            ->line('**Transaction ID:** ' . ($this->order->payment_transaction_id ?? 'N/A'))
            ->line('Your order will now be processed and shipped soon.')
            ->action('View Order', url('/orders/' . $this->order->id))
            ->line('Thank you for your payment!');
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
            'amount' => $this->order->total,
            'payment_method' => $this->order->payment_method,
        ];
    }
}
