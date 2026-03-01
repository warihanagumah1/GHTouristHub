<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Notifications\BookingMessageReceivedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingMessageController extends Controller
{
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        $tenant = $request->user()->primaryTenant();
        abort_unless($tenant && (int) $booking->tenant_id === (int) $tenant->id, 403);

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

        $booking->loadMissing(['listing', 'tenant', 'user']);
        if ((int) $booking->user_id !== (int) $request->user()->id) {
            $booking->user?->notify(new BookingMessageReceivedNotification(
                $booking,
                (string) $request->user()->name
            ));
        }

        return back()->with('status', 'Message sent to the customer.');
    }
}
