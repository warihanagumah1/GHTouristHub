<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\ListingModerationController;
use App\Http\Controllers\Admin\PayoutManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Client\BookingController as ClientBookingController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\StripeCheckoutController;
use App\Http\Controllers\CurrencyPreferenceController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\Vendor\BookingManagementController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\ListingManagementController;
use App\Http\Controllers\Vendor\PayoutRequestController;
use App\Http\Controllers\Vendor\ProfileSiteController;
use App\Http\Controllers\Vendor\ReviewManagementController;
use App\Http\Controllers\Vendor\TeamManagementController;
use App\Http\Controllers\Admin\SupportTicketController as AdminSupportTicketController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketplaceController::class, 'home'])->name('home');
Route::get('/tours', [MarketplaceController::class, 'tours'])->name('marketplace.tours');
Route::get('/utilities', [MarketplaceController::class, 'utilities'])->name('marketplace.utilities');
Route::get('/listings/{slug}', [MarketplaceController::class, 'listing'])->name('marketplace.listing');
Route::get('/vendors/{slug}', [MarketplaceController::class, 'vendor'])->name('marketplace.vendor');
Route::view('/about', 'company.about')->name('company.about');
Route::view('/terms', 'company.terms')->name('company.terms');
Route::view('/privacy', 'company.privacy')->name('company.privacy');
Route::post('/currency/update', [CurrencyPreferenceController::class, 'update'])->name('currency.update');

Route::post('/listings/{listing}/book', [ClientBookingController::class, 'store'])
    ->middleware(['auth', 'active_user', 'verified', 'role:client'])
    ->name('client.bookings.store');

Route::get('dashboard', DashboardRedirectController::class)
    ->middleware(['auth', 'active_user', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth', 'active_user'])
    ->name('profile');

Route::middleware(['auth', 'active_user'])->group(function () {
    Route::get('support/tickets', [SupportTicketController::class, 'index'])->name('support.tickets');
    Route::post('support/tickets', [SupportTicketController::class, 'store'])->name('support.tickets.store');
    Route::get('support/tickets/{ticket}', [SupportTicketController::class, 'show'])->name('support.tickets.show');
    Route::post('support/tickets/{ticket}/comments', [SupportTicketController::class, 'storeComment'])->name('support.tickets.comments.store');
});

Route::view('help-support', 'support.help')
    ->middleware(['auth', 'active_user'])
    ->name('support.help');

Route::view('components-showcase', 'components-showcase')
    ->middleware(['auth', 'active_user'])
    ->name('components.showcase');

Route::middleware(['auth', 'active_user', 'verified'])->group(function () {
    Route::middleware('role:'.User::ROLE_CLIENT)->prefix('client')->name('client.')->group(function () {
        Route::get('/dashboard', ClientDashboardController::class)->name('dashboard');
        Route::get('/bookings', [ClientBookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{booking}', [ClientBookingController::class, 'show'])->name('bookings.show');
        Route::post('/bookings/{booking}/review', [ClientBookingController::class, 'storeReview'])->name('bookings.review.store');
        Route::post('/bookings/{booking}/checkout', [StripeCheckoutController::class, 'createSession'])->name('bookings.checkout');
        Route::get('/bookings/{booking}/stripe/success', [StripeCheckoutController::class, 'success'])->name('bookings.stripe.success');
    });

    Route::middleware('role:'.User::ROLE_TOUR_OWNER.','.User::ROLE_TOUR_STAFF.','.User::ROLE_UTILITY_OWNER.','.User::ROLE_UTILITY_STAFF)
        ->prefix('vendor')
        ->name('vendor.')
        ->group(function () {
            Route::get('/dashboard', VendorDashboardController::class)->name('dashboard');
            Route::get('/listings', [ListingManagementController::class, 'index'])->name('listings.index');
            Route::get('/listings/create', [ListingManagementController::class, 'create'])->name('listings.create');
            Route::post('/listings', [ListingManagementController::class, 'store'])->name('listings.store');
            Route::get('/listings/{listing}/edit', [ListingManagementController::class, 'edit'])->name('listings.edit');
            Route::put('/listings/{listing}', [ListingManagementController::class, 'update'])->name('listings.update');
            Route::put('/listings/{listing}/visibility', [ListingManagementController::class, 'updateVisibility'])->name('listings.visibility');
            Route::delete('/listings/{listing}', [ListingManagementController::class, 'destroy'])->name('listings.destroy');
            Route::get('/bookings', [BookingManagementController::class, 'index'])->name('bookings.index');
            Route::put('/bookings/{booking}/status', [BookingManagementController::class, 'updateStatus'])->name('bookings.status');
            Route::get('/reviews', [ReviewManagementController::class, 'index'])->name('reviews.index');
            Route::get('/payouts', [PayoutRequestController::class, 'index'])->name('payouts.index');
            Route::post('/payouts', [PayoutRequestController::class, 'store'])->name('payouts.store');
            Route::put('/payouts/setup', [PayoutRequestController::class, 'updateSetup'])->name('payouts.setup');
            Route::get('/site-profile', [ProfileSiteController::class, 'edit'])->name('site-profile.edit');
            Route::put('/site-profile', [ProfileSiteController::class, 'update'])->name('site-profile.update');
        });

    Route::middleware('role:'.User::ROLE_TOUR_OWNER.','.User::ROLE_UTILITY_OWNER)
        ->prefix('vendor')
        ->name('vendor.')
        ->group(function () {
            Route::get('/team', [TeamManagementController::class, 'index'])->name('team.index');
            Route::post('/team', [TeamManagementController::class, 'store'])->name('team.store');
            Route::put('/team/{member}/deactivate', [TeamManagementController::class, 'deactivate'])->name('team.deactivate');
        });

    Route::middleware('role:'.User::ROLE_ADMIN.','.User::ROLE_ADMIN_STAFF)->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
        Route::put('/users/{user}/block', [UserManagementController::class, 'toggleBlock'])->name('users.block');
        Route::get('/listings', [ListingModerationController::class, 'index'])->name('listings.index');
        Route::put('/listings/{listing}/status', [ListingModerationController::class, 'updateStatus'])->name('listings.status');
        Route::put('/listings/{listing}/block', [ListingModerationController::class, 'toggleBlocked'])->name('listings.block');
        Route::put('/listings/{listing}/featured', [ListingModerationController::class, 'updateFeatured'])->name('listings.featured');
        Route::get('/analytics', AdminAnalyticsController::class)->name('analytics');
        Route::get('/payouts', [PayoutManagementController::class, 'index'])->name('payouts.index');
        Route::put('/payouts/{payoutRequest}', [PayoutManagementController::class, 'update'])->name('payouts.update');
        Route::get('/support-tickets', [AdminSupportTicketController::class, 'index'])->name('support-tickets.index');
        Route::get('/support-tickets/{supportTicket}', [AdminSupportTicketController::class, 'show'])->name('support-tickets.show');
        Route::put('/support-tickets/{supportTicket}/status', [AdminSupportTicketController::class, 'updateStatus'])->name('support-tickets.status');
        Route::post('/support-tickets/{supportTicket}/comments', [AdminSupportTicketController::class, 'storeComment'])->name('support-tickets.comments.store');
    });
});

require __DIR__.'/auth.php';
