<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketComment;
use App\Notifications\SupportTicketUpdatedNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $query = SupportTicket::query()->with(['user', 'tenant']);

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', (string) $request->query('priority'));
        }

        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(function ($inner) use ($search): void {
                $inner->where('subject', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', "%{$search}%"));
            });
        }

        $tickets = $query->latest()->paginate(25)->withQueryString();

        return view('admin.support-tickets.index', [
            'tickets' => $tickets,
        ]);
    }

    public function show(SupportTicket $supportTicket): View
    {
        $supportTicket->load(['user', 'tenant', 'comments.user']);

        return view('admin.support-tickets.show', [
            'ticket' => $supportTicket,
        ]);
    }

    public function updateStatus(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $oldStatus = $supportTicket->status;
        $newStatus = $validated['status'];

        $supportTicket->update([
            'status' => $newStatus,
            'closed_at' => $newStatus === 'closed' ? now() : null,
            'last_replied_at' => now(),
        ]);

        if ($oldStatus !== $newStatus) {
            $supportTicket->user->notify(new SupportTicketUpdatedNotification(
                $supportTicket,
                "Your support ticket #{$supportTicket->id} status changed to ".str_replace('_', ' ', $newStatus).'.'
            ));
        }

        return back()->with('status', 'Ticket status updated.');
    }

    public function storeComment(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:5000'],
            'is_internal' => ['nullable', 'boolean'],
        ]);

        SupportTicketComment::create([
            'support_ticket_id' => $supportTicket->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'is_internal' => (bool) ($validated['is_internal'] ?? false),
        ]);

        $supportTicket->update([
            'last_replied_at' => now(),
            'status' => $supportTicket->status === 'open' ? 'in_progress' : $supportTicket->status,
        ]);

        if (! (bool) ($validated['is_internal'] ?? false)) {
            $supportTicket->user->notify(new SupportTicketUpdatedNotification(
                $supportTicket,
                "Support replied on your ticket #{$supportTicket->id}."
            ));
        }

        return back()->with('status', 'Comment posted.');
    }
}
