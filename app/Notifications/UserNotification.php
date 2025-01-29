<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification
{
    use Queueable;

    public $data;

    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
        // return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject($this->data['title'] ?? 'Notification') // Subject for the email
                    ->line($this->data['message'] ?? 'You have a new notification.') // Custom message
                    ->action('View Notification', url('/')) // URL for the action button
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->data['title'] ?? 'New Notification', // Title of the notification
            'message' => $this->data['message'] ?? 'You have a new notification.', // Message content
            'type' => $this->data['type'] ?? 'info', // Type of notification (e.g., info, warning)
            'icon' => $this->data['icon'] ?? 'bell', // Icon to show for the notification
            'icon_alt' => $this->data['icon_alt'] ?? 'bell', // Alternative text for the icon
            'bgColor' => $this->data['bgColor'] ?? '#f0f0f0', // Background color
            'textColor' => $this->data['textColor'] ?? '#000', // Text color
            'property_id' => $this->data['property_id'] ?? '', // The related property to this notification
            'from_user_id' => $this->data['sender_user_id'] ?? '', // Source/Sender
        ];
    }
}
