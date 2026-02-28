<?php

namespace App\Notifications;

use App\Models\PayoutRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayoutRequestPaidNotification extends Notification
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
        $transferRef = $this->payoutRequest->stripe_transfer_id ?: 'Not provided';

        return (new MailMessage)
            ->subject("Payout paid #{$this->payoutRequest->id}")
            ->line('Your payout request has been marked as paid.')
            ->line("Amount: {$this->payoutRequest->currency} ".number_format((float) $this->payoutRequest->amount, 2))
            ->line("Transfer reference: {$transferRef}");
    }
}
