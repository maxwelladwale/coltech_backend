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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        \Log::info('Sending order status change notification', [
            'order_number' => $this->order->order_number,
            'customer_email' => $notifiable->email ?? $this->order->shipping_email,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'has_tracking' => !empty($this->order->tracking_number),
        ]);

        $statusMessages = [
            'confirmed' => 'Your order has been confirmed and is being prepared.',
            'processing' => 'Your order is currently being processed.',
            'shipped' => 'Great news! Your order has been shipped and is on its way.',
            'delivered' => 'Your order has been delivered. We hope you enjoy your purchase!',
            'cancelled' => 'Your order has been cancelled. If you have any questions, please contact us.',
        ];

        $message = $statusMessages[$this->newStatus] ?? 'Your order status has been updated.';

        $mail = (new MailMessage)
            ->subject('Order Status Update - ' . $this->order->order_number)
            ->greeting('Hello ' . $this->order->getCustomerName() . '!')
            ->line('Your order status has been updated.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**New Status:** ' . ucfirst($this->newStatus))
            ->line($message);

        // Add tracking information for shipped orders
        if ($this->newStatus === 'shipped' && $this->order->tracking_number) {
            $mail->line("\n**Tracking Information:**")
                ->line('**Tracking Number:** ' . $this->order->tracking_number);

            if ($this->order->carrier) {
                $mail->line('**Carrier:** ' . ucfirst($this->order->carrier));
            }

            if ($this->order->tracking_url) {
                $mail->action('Track Your Package', $this->order->tracking_url);
            }
        } else {
            $mail->action('View Order', url('/orders/' . $this->order->id));
        }

        // Add installation details for shipped orders with technician installation
        if ($this->newStatus === 'shipped' && $this->order->installation_method === 'technician' && $this->order->garage) {
            $mail->line("\n**Installation Details:**")
                ->line('Your order includes professional installation at ' . $this->order->garage->name)
                ->line('**Location:** ' . $this->order->garage->address . ', ' . $this->order->garage->city)
                ->line('**Phone:** ' . $this->order->garage->phone)
                ->line("\nThe garage will contact you to schedule the installation once your package arrives.");
        }

        return $mail->line('Thank you for your business!');
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
