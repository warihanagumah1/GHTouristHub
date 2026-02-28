<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\TenantMember;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeamManagementController extends Controller
{
    /**
     * List and manage staff members for the owner's tenant.
     */
    public function index(): View
    {
        $tenant = request()->user()->primaryTenant();
        abort_unless($tenant && (int) $tenant->owner_user_id === (int) request()->user()->id, 403);

        $staffMembers = $tenant->members()
            ->with('user')
            ->whereIn('role', [User::ROLE_TOUR_STAFF, User::ROLE_UTILITY_STAFF])
            ->latest()
            ->get();

        return view('vendor.team.index', [
            'tenant' => $tenant,
            'staffMembers' => $staffMembers,
        ]);
    }

    /**
     * Create staff user under a vendor tenant.
     */
    public function store(Request $request): RedirectResponse
    {
        $tenant = $request->user()->primaryTenant();
        abort_unless($tenant && (int) $tenant->owner_user_id === (int) $request->user()->id, 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
        ]);

        $generatedPassword = null;
        if (! filled($validated['password'] ?? null)) {
            $generatedPassword = Str::random(12);
            $validated['password'] = $generatedPassword;
        }

        $staffRole = $tenant->type === 'tour_company'
            ? User::ROLE_TOUR_STAFF
            : User::ROLE_UTILITY_STAFF;

        $staff = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'user_role' => $staffRole,
            'email_verified_at' => now(),
        ]);

        TenantMember::create([
            'tenant_id' => $tenant->id,
            'user_id' => $staff->id,
            'role' => $staffRole,
            'permissions' => [
                'listings.manage' => true,
                'bookings.manage' => true,
                'profile.manage' => false,
            ],
            'is_active' => true,
        ]);

        $message = 'Staff account created successfully.';
        if ($generatedPassword !== null) {
            $message .= " Temporary password: {$generatedPassword}";
        }

        return back()->with('status', $message);
    }

    /**
     * Deactivate a staff member from this tenant.
     */
    public function deactivate(Request $request, TenantMember $member): RedirectResponse
    {
        $tenant = $request->user()->primaryTenant();
        abort_unless($tenant && (int) $tenant->owner_user_id === (int) $request->user()->id, 403);
        abort_unless((int) $member->tenant_id === (int) $tenant->id, 403);
        abort_if(in_array($member->role, [User::ROLE_TOUR_OWNER, User::ROLE_UTILITY_OWNER], true), 422);

        $member->update(['is_active' => false]);

        return back()->with('status', 'Staff member deactivated.');
    }
}
