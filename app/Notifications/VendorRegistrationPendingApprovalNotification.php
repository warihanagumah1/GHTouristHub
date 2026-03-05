<?php

namespace App\Notifications;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VendorRegistrationPendingApprovalNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly User $vendor,
        public readonly Tenant $tenant
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New vendor registration awaiting approval')
            ->line('A new vendor has registered and is waiting for approval.')
            ->line("Name: {$this->vendor->name}")
            ->line("Email: {$this->vendor->email}")
            ->line('Role: '.str_replace('_', ' ', (string) $this->vendor->user_role))
            ->line("Company: {$this->tenant->name}")
            ->line('Type: '.str_replace('_', ' ', (string) $this->tenant->type))
            ->line("Current status: {$this->tenant->status}")
            ->action('Open Admin Users', route('admin.users.index'));
    }
}
