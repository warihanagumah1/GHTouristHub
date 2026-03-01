<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingMessageReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Booking $booking,
        public readonly string $senderName
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New booking message: {$this->booking->booking_no}")
            ->line("{$this->senderName} sent a new message on booking {$this->booking->booking_no}.")
            ->line('Listing: '.($this->booking->listing?->title ?? 'Listing'))
            ->action('Open Conversation', $this->bookingUrlFor($notifiable));
    }

    protected function bookingUrlFor(object $notifiable): string
    {
        $role = (string) ($notifiable->user_role ?? '');
        $vendorRoles = [
            User::ROLE_TOUR_OWNER,
            User::ROLE_TOUR_STAFF,
            User::ROLE_UTILITY_OWNER,
            User::ROLE_UTILITY_STAFF,
        ];

        if (in_array($role, $vendorRoles, true)) {
            return route('vendor.bookings.show', $this->booking);
        }

        return route('client.bookings.show', $this->booking);
    }
}
