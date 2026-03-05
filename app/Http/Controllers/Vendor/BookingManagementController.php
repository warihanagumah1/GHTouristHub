<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Notifications\BookingStatusUpdatedNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingManagementController extends Controller
{
    public function index(): View
    {
        $tenant = request()->user()->primaryTenant();
        abort_unless($tenant, 403);

        $bookings = $tenant->bookings()
            ->with(['listing', 'user', 'payments'])
            ->latest()
            ->paginate(20);

        return view('vendor.bookings.index', [
            'tenant' => $tenant,
            'bookings' => $bookings,
        ]);
    }

    public function show(Booking $booking): View
    {
        $tenant = request()->user()->primaryTenant();
        abort_unless($tenant && $booking->tenant_id === $tenant->id, 403);

        $booking->load([
            'listing',
            'user',
            'payments',
            'messages.sender:id,name,email',
        ]);

        return view('vendor.bookings.show', [
            'tenant' => $tenant,
            'booking' => $booking,
        ]);
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $tenant = $request->user()->primaryTenant();
        abort_unless($tenant && $booking->tenant_id === $tenant->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:pending_payment,paid,confirmed,cancelled,completed'],
        ]);

        $oldStatus = (string) $booking->status;
        $newStatus = (string) $validated['status'];

        $allowedTransitions = [
            'pending_payment' => ['pending_payment', 'paid', 'cancelled'],
            'paid' => ['paid', 'confirmed', 'completed', 'cancelled'],
            'confirmed' => ['confirmed', 'completed', 'cancelled'],
            'completed' => ['completed'],
            'cancelled' => ['cancelled'],
        ];

        if (! in_array($newStatus, $allowedTransitions[$oldStatus] ?? [], true)) {
            return back()->withErrors([
                'status' => "Invalid status transition from {$oldStatus} to {$newStatus}.",
            ]);
        }

        if (in_array($oldStatus, ['paid', 'confirmed', 'completed'], true) && $newStatus === 'pending_payment') {
            return back()->withErrors([
                'status' => 'Paid bookings cannot be reverted to pending payment.',
            ]);
        }

        $booking->update([
            'status' => $newStatus,
            'paid_at' => $newStatus === 'paid' && $oldStatus !== 'paid' ? now() : $booking->paid_at,
        ]);

        if ($oldStatus !== $newStatus) {
            $booking->loadMissing(['listing', 'tenant', 'user']);

            $booking->user?->notify(new BookingStatusUpdatedNotification(
                $booking,
                $this->clientStatusMessage($booking)
            ));
        }

        return back()->with('status', 'Booking status updated.');
    }

    protected function clientStatusMessage(Booking $booking): string
    {
        return match ((string) $booking->status) {
            'confirmed' => "Your booking {$booking->booking_no} has been confirmed by the company.",
            'completed' => "Your booking {$booking->booking_no} has been marked as completed.",
            'cancelled' => "Your booking {$booking->booking_no} has been cancelled. Please check details or contact support.",
            'paid' => "Payment for booking {$booking->booking_no} is confirmed.",
            default => "Your booking {$booking->booking_no} status has been updated.",
        };
    }
}
