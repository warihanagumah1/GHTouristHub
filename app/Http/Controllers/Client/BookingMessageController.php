<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Notifications\BookingMessageReceivedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingMessageController extends Controller
{
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $message = trim((string) $validated['message']);
        if ($message === '') {
            return back()->withErrors([
                'message' => 'Message cannot be empty.',
            ]);
        }

        $booking->messages()->create([
            'sender_user_id' => $request->user()->id,
            'message' => $message,
        ]);

        $booking->loadMissing(['listing', 'tenant']);
        $this->vendorUsersForBooking($booking)
            ->reject(fn (User $vendorUser) => (int) $vendorUser->id === (int) $request->user()->id)
            ->each(fn (User $vendorUser) => $vendorUser->notify(new BookingMessageReceivedNotification(
                $booking,
                (string) $request->user()->name
            )));

        return back()->with('status', 'Message sent to the vendor.');
    }

    protected function vendorUsersForBooking(Booking $booking)
    {
        return User::query()
            ->where(function ($query) use ($booking): void {
                $query->whereHas('ownedTenants', fn ($owned) => $owned->where('tenants.id', $booking->tenant_id))
                    ->orWhereHas('tenantMemberships', function ($memberships) use ($booking): void {
                        $memberships->where('tenant_id', $booking->tenant_id)->where('is_active', true);
                    });
            })
            ->get();
    }
}
