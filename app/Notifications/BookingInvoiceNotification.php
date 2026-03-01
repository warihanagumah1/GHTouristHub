<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class BookingInvoiceNotification extends Notification
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
        return (new MailMessage)
            ->subject("Invoice for booking {$this->booking->booking_no}")
            ->line('Your booking invoice is ready.')
            ->line("Booking reference: {$this->booking->booking_no}")
            ->line('Listing: '.($this->booking->listing?->title ?? 'Listing'))
            ->line('Total: '.strtoupper((string) $this->booking->currency).' '.number_format((float) $this->booking->total_amount, 2))
            ->action('View Invoice', $this->signedInvoiceUrl());
    }

    protected function signedInvoiceUrl(): string
    {
        $email = strtolower(trim((string) ($this->booking->user?->email ?? '')));

        return URL::temporarySignedRoute(
            'bookings.invoice.public',
            now()->addDays(14),
            [
                'booking' => $this->booking,
                'recipient' => sha1($email),
            ]
        );
    }
}
