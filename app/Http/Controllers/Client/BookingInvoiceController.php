<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class BookingInvoiceController extends Controller
{
    public function __invoke(Booking $booking): View
    {
        abort_unless($booking->user_id === request()->user()->id, 403);

        $booking->loadMissing(['listing', 'tenant.profile', 'user', 'payments']);

        return view('bookings.invoice', [
            'booking' => $booking,
            'audience' => 'client',
        ]);
    }

    public function viewViaEmail(Request $request, Booking $booking): View
    {
        $booking->loadMissing(['listing', 'tenant.profile', 'user', 'payments']);

        $expectedRecipient = sha1(strtolower(trim((string) $booking->user?->email)));
        $actualRecipient = (string) $request->query('recipient', '');
        abort_unless(hash_equals($expectedRecipient, $actualRecipient), 403);

        return view('bookings.invoice-public', [
            'booking' => $booking,
            'audience' => 'client',
        ]);
    }
}
