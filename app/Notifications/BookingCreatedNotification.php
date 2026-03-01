<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Booking $booking)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isClientRecipient = (string) $notifiable->user_role === User::ROLE_CLIENT;

        return (new MailMessage)
            ->subject("Booking created: {$this->booking->booking_no}")
            ->line($isClientRecipient
                ? 'Your booking request has been created successfully.'
                : 'A new booking has been placed on your listing.')
            ->line("Booking reference: {$this->booking->booking_no}")
            ->line('Listing: '.($this->booking->listing?->title ?? 'Listing'))
            ->line('Travelers/Quantity: '.(int) $this->booking->travelers_count)
            ->line('Total: '.strtoupper((string) $this->booking->currency).' '.number_format((float) $this->booking->total_amount, 2))
            ->action('Open Booking', $this->bookingUrlFor($notifiable));
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
