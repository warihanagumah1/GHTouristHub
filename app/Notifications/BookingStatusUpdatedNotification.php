<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Booking $booking,
        public readonly ?string $headline = null,
        public readonly ?string $note = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = ucfirst(str_replace('_', ' ', (string) $this->booking->status));
        $message = (new MailMessage)
            ->subject("Booking update ({$statusLabel}): {$this->booking->booking_no}")
            ->line($this->headline ?: "Your booking is now {$statusLabel}.")
            ->line("Booking reference: {$this->booking->booking_no}")
            ->line('Listing: '.($this->booking->listing?->title ?? 'Listing'))
            ->line('Travelers/Quantity: '.(int) $this->booking->travelers_count)
            ->line('Total: '.strtoupper((string) $this->booking->currency).' '.number_format((float) $this->booking->total_amount, 2))
            ->action('Open Booking', $this->bookingUrlFor($notifiable));

        if ($this->note) {
            $message->line($this->note);
        }

        return $message;
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
