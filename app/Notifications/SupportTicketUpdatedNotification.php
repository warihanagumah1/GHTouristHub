<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use App\Models\User;
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
        $role = (string) ($notifiable->user_role ?? '');
        $isAdminRecipient = in_array($role, [User::ROLE_ADMIN, User::ROLE_ADMIN_STAFF], true);

        return (new MailMessage)
            ->subject("Support ticket update: #{$this->ticket->id}")
            ->line($this->message)
            ->line("Ticket: {$this->ticket->subject}")
            ->line('Status: '.ucfirst(str_replace('_', ' ', (string) $this->ticket->status)))
            ->action('Open Ticket', $isAdminRecipient
                ? route('admin.support-tickets.show', $this->ticket)
                : route('support.tickets.show', $this->ticket));
    }
}
