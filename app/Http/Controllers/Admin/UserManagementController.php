<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserManagementController extends Controller
{
    /**
     * Display users with filters and role breakdown.
     */
    public function index(Request $request): View
    {
        $query = User::query();

        if ($request->filled('q')) {
            $search = trim((string) $request->query('q'));
            $query->where(function ($inner) use ($search): void {
                $inner->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('user_role', (string) $request->query('role'));
        }

        if ($request->filled('blocked')) {
            $query->where('is_blocked', $request->boolean('blocked'));
        }

        $users = $query->latest()->paginate(25)->withQueryString();

        $roleCounts = User::query()
            ->selectRaw('user_role, count(*) as total')
            ->groupBy('user_role')
            ->pluck('total', 'user_role');

        return view('admin.users.index', [
            'users' => $users,
            'roleCounts' => $roleCounts,
            'roles' => [
                User::ROLE_ADMIN,
                User::ROLE_ADMIN_STAFF,
                User::ROLE_TOUR_OWNER,
                User::ROLE_TOUR_STAFF,
                User::ROLE_UTILITY_OWNER,
                User::ROLE_UTILITY_STAFF,
                User::ROLE_CLIENT,
            ],
        ]);
    }

    /**
     * Reset password for a selected user.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'new_password' => ['nullable', 'string', 'min:8', 'max:255'],
        ]);

        $newPassword = $validated['new_password'] ?? Str::random(12);

        $user->update([
            'password' => Hash::make($newPassword),
            'remember_token' => Str::random(60),
        ]);

        return back()->with('status', "Password reset for {$user->email}. New password: {$newPassword}");
    }

    /**
     * Block or unblock a selected user account.
     */
    public function toggleBlock(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_blocked' => ['required', 'boolean'],
        ]);

        $shouldBlock = (bool) $validated['is_blocked'];

        if ($request->user()?->is($user) && $shouldBlock) {
            return back()->with('status', 'You cannot block your own account.');
        }

        $user->update([
            'is_blocked' => $shouldBlock,
            'blocked_at' => $shouldBlock ? now() : null,
            'remember_token' => Str::random(60),
        ]);

        $message = $shouldBlock
            ? "User {$user->email} has been blocked."
            : "User {$user->email} has been unblocked.";

        return back()->with('status', $message);
    }
}
