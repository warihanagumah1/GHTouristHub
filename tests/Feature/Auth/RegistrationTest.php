<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\VendorRegistrationPendingApprovalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@example.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('verification.notice', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_vendor_registration_notifies_admin_and_support_for_approval(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'user_role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $component = Volt::test('pages.auth.register')
            ->set('name', 'Tour Vendor')
            ->set('email', 'vendor@example.com')
            ->set('account_type', 'tour_company_owner')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');
        $component->assertRedirect(route('verification.notice', absolute: false));

        Notification::assertSentTo($admin, VendorRegistrationPendingApprovalNotification::class);
        Notification::assertSentOnDemand(VendorRegistrationPendingApprovalNotification::class);

        $this->assertDatabaseHas('tenants', [
            'owner_user_id' => auth()->id(),
            'status' => 'pending',
        ]);
    }
}
