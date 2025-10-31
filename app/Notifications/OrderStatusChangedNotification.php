<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Order $order;
    public string $oldStatus;
    public string $newStatus;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order, string $oldStatus, string $newStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
        $statusMessages = [
            'confirmed' => 'Your order has been confirmed and is being prepared.',
            'processing' => 'Your order is currently being processed.',
            'shipped' => 'Great news! Your order has been shipped and is on its way.',
            'delivered' => 'Your order has been delivered. We hope you enjoy your purchase!',
            'cancelled' => 'Your order has been cancelled. If you have any questions, please contact us.',
        ];

        $message = $statusMessages[$this->newStatus] ?? 'Your order status has been updated.';

        // var_dump($message);

        return (new MailMessage)
            ->subject('Order Status Update - ' . $this->order->order_number)
            ->greeting('Hello ' . $this->order->getCustomerName() . '!')
            ->line('Your order status has been updated.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**New Status:** ' . ucfirst($this->newStatus))
            ->line($message)
            ->action('View Order', url('/orders/' . $this->order->id))
            ->line('Thank you for your business!');
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
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ];
    }
}
