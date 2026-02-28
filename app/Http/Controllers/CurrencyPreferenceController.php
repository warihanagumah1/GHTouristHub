<?php

namespace App\Http\Controllers;

use App\Services\CurrencyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrencyPreferenceController extends Controller
{
    /**
     * Update market display currency for the current session.
     */
    public function update(Request $request, CurrencyService $currencyService): RedirectResponse
    {
        $validated = $request->validate([
            'currency_code' => ['required', 'string', 'size:3'],
        ]);

        $currencyService->setSelectedCurrency($validated['currency_code']);

        return back();
    }
}
