<?php

namespace Tests\Feature\Support;

use App\Models\SupportTicket;
use App\Models\SupportTicketComment;
use App\Models\User;
use App\Notifications\SupportTicketSubmittedNotification;
use App\Notifications\SupportTicketUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_support_ticket_and_admin_is_notified(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'user_role' => User::ROLE_CLIENT,
            'email_verified_at' => now(),
        ]);

        $admin = User::factory()->create([
            'user_role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('support.tickets.store'), [
            'subject' => 'Payment failed for booking THB-123',
            'category' => 'payments',
            'priority' => 'high',
            'message' => 'I was charged but booking still shows pending payment.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $user->id,
            'subject' => 'Payment failed for booking THB-123',
            'status' => 'open',
        ]);
        $this->assertDatabaseHas('support_ticket_comments', [
            'user_id' => $user->id,
            'is_internal' => 0,
        ]);

        Notification::assertSentTo($admin, SupportTicketSubmittedNotification::class);
        Notification::assertSentTo($user, SupportTicketSubmittedNotification::class);
    }

    public function test_admin_can_update_status_and_reply_on_ticket(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'user_role' => User::ROLE_CLIENT,
            'email_verified_at' => now(),
        ]);

        $admin = User::factory()->create([
            'user_role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'subject' => 'Need listing support',
            'category' => 'listing',
            'priority' => 'medium',
            'status' => 'open',
        ]);

        SupportTicketComment::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => 'Please help with listing media.',
        ]);

        $statusResponse = $this->actingAs($admin)->put(route('admin.support-tickets.status', $ticket), [
            'status' => 'in_progress',
        ]);
        $statusResponse->assertRedirect();

        $commentResponse = $this->actingAs($admin)->post(route('admin.support-tickets.comments.store', $ticket), [
            'body' => 'We are reviewing this now.',
        ]);
        $commentResponse->assertRedirect();

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticket->id,
            'status' => 'in_progress',
        ]);
        $this->assertDatabaseHas('support_ticket_comments', [
            'support_ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'body' => 'We are reviewing this now.',
            'is_internal' => 0,
        ]);

        Notification::assertSentTo($user, SupportTicketUpdatedNotification::class);
    }
}
