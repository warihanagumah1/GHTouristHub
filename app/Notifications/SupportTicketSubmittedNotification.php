<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SupportTicketSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly SupportTicket $ticket,
        public readonly bool $forAdmin = false
    )
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject("Support ticket submitted: {$this->ticket->subject}")
            ->line($this->forAdmin
                ? 'A new support ticket has been submitted and needs attention.'
                : 'Your support ticket has been submitted successfully.')
            ->line("Ticket #{$this->ticket->id}")
            ->line("Subject: {$this->ticket->subject}")
            ->line('Priority: '.ucfirst((string) $this->ticket->priority));

        if ($this->forAdmin) {
            return $mail
                ->line('Please review and respond from the admin dashboard.')
                ->action('Open Ticket', route('admin.support-tickets.show', $this->ticket));
        }

        return $mail
            ->line('Our support team will reply shortly.')
            ->action('Open Ticket', route('support.tickets.show', $this->ticket));
    }
}
