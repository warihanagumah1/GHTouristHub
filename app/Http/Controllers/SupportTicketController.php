<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketComment;
use App\Models\User;
use App\Notifications\SupportTicketSubmittedNotification;
use App\Notifications\SupportTicketUpdatedNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (in_array($request->user()->user_role, [User::ROLE_ADMIN, User::ROLE_ADMIN_STAFF], true)) {
            return redirect()->route('admin.support-tickets.index');
        }

        $tickets = SupportTicket::query()
            ->with('comments')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('support.tickets.index', [
            'tickets' => $tickets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'min:6', 'max:180'],
            'category' => ['nullable', 'string', 'max:80'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $user = $request->user();
        $tenant = $user->primaryTenant();

        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'tenant_id' => $tenant?->id,
            'subject' => $validated['subject'],
            'category' => $validated['category'] ?? 'general',
            'priority' => $validated['priority'],
            'status' => 'open',
            'last_replied_at' => now(),
        ]);

        SupportTicketComment::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => $validated['message'],
            'is_internal' => false,
        ]);

        User::query()
            ->whereIn('user_role', [User::ROLE_ADMIN, User::ROLE_ADMIN_STAFF])
            ->each(fn (User $admin) => $admin->notify(new SupportTicketSubmittedNotification($ticket, true)));

        $user->notify(new SupportTicketSubmittedNotification($ticket, false));

        return redirect()->route('support.tickets.show', $ticket)->with('status', 'Ticket submitted successfully.');
    }

    public function show(Request $request, SupportTicket $ticket): View
    {
        abort_unless(
            $ticket->user_id === $request->user()->id
            || in_array($request->user()->user_role, [User::ROLE_ADMIN, User::ROLE_ADMIN_STAFF], true),
            403
        );

        $ticket->load(['user', 'tenant', 'comments.user']);

        return view('support.tickets.show', [
            'ticket' => $ticket,
            'isAdmin' => in_array($request->user()->user_role, [User::ROLE_ADMIN, User::ROLE_ADMIN_STAFF], true),
        ]);
    }

    public function storeComment(Request $request, SupportTicket $ticket): RedirectResponse
    {
        abort_unless($ticket->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:5000'],
        ]);

        SupportTicketComment::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'is_internal' => false,
        ]);

        $ticket->update([
            'last_replied_at' => now(),
            'status' => $ticket->status === 'closed' ? 'open' : $ticket->status,
            'closed_at' => $ticket->status === 'closed' ? null : $ticket->closed_at,
        ]);

        User::query()
            ->whereIn('user_role', [User::ROLE_ADMIN, User::ROLE_ADMIN_STAFF])
            ->each(fn (User $admin) => $admin->notify(new SupportTicketUpdatedNotification(
                $ticket,
                "Customer replied on ticket #{$ticket->id}."
            )));

        return back()->with('status', 'Comment added.');
    }
}
