<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingReminderNotification extends Notification
{
    use Queueable;

    public const TYPE_PENDING_PAYMENT = 'pending_payment';
    public const TYPE_UPCOMING_SERVICE = 'upcoming_service';

    public function __construct(
        public readonly Booking $booking,
        public readonly string $type
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $listingTitle = $this->booking->listing?->title ?? 'Listing';

        $mail = (new MailMessage)
            ->line("Booking reference: {$this->booking->booking_no}")
            ->line("Listing: {$listingTitle}")
            ->line('Total: '.strtoupper((string) $this->booking->currency).' '.number_format((float) $this->booking->total_amount, 2))
            ->action('Open Booking', $this->bookingUrlFor($notifiable));

        if ($this->type === self::TYPE_UPCOMING_SERVICE) {
            $serviceDate = $this->booking->service_date?->format('F j, Y') ?? 'soon';

            return $mail
                ->subject("Upcoming booking reminder: {$this->booking->booking_no}")
                ->line("Your booked service is coming up on {$serviceDate}.")
                ->line('Please review your booking details and be ready for your trip/service.');
        }

        return $mail
            ->subject("Payment reminder: {$this->booking->booking_no}")
            ->line('Your booking is still pending payment.')
            ->line('Complete payment to confirm your booking and avoid cancellation.');
    }

    protected function bookingUrlFor(object $notifiable): string
    {
        $role = (string) ($notifiable->user_role ?? '');

        if (in_array($role, [
            User::ROLE_TOUR_OWNER,
            User::ROLE_TOUR_STAFF,
            User::ROLE_UTILITY_OWNER,
            User::ROLE_UTILITY_STAFF,
        ], true)) {
            return route('vendor.bookings.show', $this->booking);
        }

        return route('client.bookings.show', $this->booking);
    }
}
