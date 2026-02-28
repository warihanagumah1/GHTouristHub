<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class DashboardRedirectController extends Controller
{
    /**
     * Redirect users to role-specific dashboards.
     */
    public function __invoke(): RedirectResponse
    {
        $user = request()->user();

        return redirect()->route($user?->dashboardRoute() ?? 'home');
    }
}
