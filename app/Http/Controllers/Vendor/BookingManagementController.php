<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Booking;
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

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $tenant = $request->user()->primaryTenant();
        abort_unless($tenant && $booking->tenant_id === $tenant->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:pending_payment,paid,confirmed,cancelled,completed'],
        ]);

        $booking->update([
            'status' => $validated['status'],
            'paid_at' => $validated['status'] === 'paid' ? now() : $booking->paid_at,
        ]);

        return back()->with('status', 'Booking status updated.');
    }
}
