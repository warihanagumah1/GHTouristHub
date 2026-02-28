<?php

namespace App\Notifications;

use App\Models\PayoutRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayoutRequestSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly PayoutRequest $payoutRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Payout request submitted #{$this->payoutRequest->id}")
            ->line('A payout request has been submitted.')
            ->line("Amount: {$this->payoutRequest->currency} ".number_format((float) $this->payoutRequest->amount, 2))
            ->line("Status: {$this->payoutRequest->status}");
    }
}
