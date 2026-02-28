<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly SupportTicket $ticket)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Support ticket submitted: {$this->ticket->subject}")
            ->line('A new support ticket has been submitted.')
            ->line("Ticket #{$this->ticket->id}")
            ->line("Subject: {$this->ticket->subject}")
            ->line('Please review and respond from the dashboard.');
    }
}
