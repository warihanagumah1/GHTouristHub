<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Notifications\BookingInvoiceNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class BookingInvoiceController extends Controller
{
    public function __invoke(Booking $booking): View
    {
        $tenant = request()->user()->primaryTenant();
        abort_unless($tenant && (int) $booking->tenant_id === (int) $tenant->id, 403);

        $booking->loadMissing(['listing', 'tenant.profile', 'user', 'payments']);

        return view('bookings.invoice', [
            'booking' => $booking,
            'audience' => 'vendor',
        ]);
    }

    public function emailToClient(Request $request, Booking $booking): RedirectResponse
    {
        $tenant = $request->user()->primaryTenant();
        abort_unless($tenant && (int) $booking->tenant_id === (int) $tenant->id, 403);

        $booking->loadMissing(['listing', 'user']);
        $recipientEmail = trim((string) ($booking->user?->email ?? ''));

        if ($recipientEmail === '') {
            return back()->withErrors([
                'invoice' => 'Client email is missing. Invoice email was not sent.',
            ]);
        }

        try {
            $booking->user?->notify(new BookingInvoiceNotification($booking));
        } catch (Throwable) {
            return back()->withErrors([
                'invoice' => "Unable to email invoice to {$recipientEmail}. Please try again.",
            ]);
        }

        return back()->with('status', "Invoice emailed to {$recipientEmail}.");
    }
}
