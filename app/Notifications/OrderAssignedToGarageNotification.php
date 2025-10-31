<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderAssignedToGarageNotification extends Notification implements ShouldQueue
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
        $appointmentInfo = '';
        if ($this->order->appointment_date) {
            $appointmentInfo = '**Appointment:** ' . $this->order->appointment_date->format('M j, Y') . ' at ' . $this->order->appointment_time;
        }

        return (new MailMessage)
            ->subject('New Installation Order Assigned - ' . $this->order->order_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new installation order has been assigned to your garage.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Customer:** ' . $this->order->getCustomerName())
            ->line('**Customer Phone:** ' . $this->order->shipping_phone)
            ->line($appointmentInfo)
            ->line('**Vehicle:** ' . ($this->order->vehicle_make ?? 'N/A') . ' ' . ($this->order->vehicle_model ?? ''))
            ->line('**Registration:** ' . ($this->order->vehicle_registration ?? 'N/A'))
            ->action('View Order Details', url('/admin/orders/' . $this->order->id))
            ->line('Please prepare for the installation appointment.');
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
            'appointment_date' => $this->order->appointment_date,
        ];
    }
}
