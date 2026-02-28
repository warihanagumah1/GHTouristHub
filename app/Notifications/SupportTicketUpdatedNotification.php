<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly SupportTicket $ticket,
        public readonly string $message
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Support ticket update: #{$this->ticket->id}")
            ->line($this->message)
            ->line("Ticket: {$this->ticket->subject}")
            ->action('Open Ticket', route('support.tickets.show', $this->ticket));
    }
}
