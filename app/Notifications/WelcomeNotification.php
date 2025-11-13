<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public User $user;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
        \Log::info('Sending welcome email to new user', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'full_name' => $this->user->full_name,
        ]);

        return (new MailMessage)
            ->subject('Welcome to COLTECH - Your Account is Ready!')
            ->greeting('Hello ' . $this->user->full_name . '! 👋')
            ->line('Welcome to **COLTECH**! We\'re excited to have you on board.')
            ->line('Your account has been successfully created and you\'re all set to start shopping for premium automotive products.')
            ->line('')
            ->line('**What\'s Next?**')
            ->line('🔍 Browse our extensive catalog of quality car parts')
            ->line('🛒 Add items to your cart and checkout easily')
            ->line('📦 Track your orders in real-time')
            ->line('🔧 Schedule professional installation at partner garages')
            ->line('')
            ->action('Start Shopping', url('/products'))
            ->line('If you have any questions or need assistance, our support team is always here to help.')
            ->line('Thank you for choosing COLTECH!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'user_id' => $this->user->id,
            'message' => 'Welcome to COLTECH',
        ];
    }
}
