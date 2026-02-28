<?php

namespace Tests\Feature\Admin;

use App\Models\PayoutRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VendorProfile;
use App\Notifications\PayoutRequestPaidNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class PayoutManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_marking_payout_paid_can_auto_create_stripe_transfer(): void
    {
        Notification::fake();

        [$admin, $payoutRequest] = $this->makeAdminAndPayoutRequest();

        config([
            'services.stripe.secret' => 'sk_test_123',
            'services.stripe.connect_destination_enabled' => true,
        ]);

        Http::fake([
            'https://api.stripe.com/v1/transfers' => Http::response([
                'id' => 'tr_test_123',
                'object' => 'transfer',
            ], 200),
        ]);

        $response = $this->actingAs($admin)->put(route('admin.payouts.update', $payoutRequest), [
            'status' => 'paid',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('payout_requests', [
            'id' => $payoutRequest->id,
            'status' => 'paid',
            'stripe_transfer_id' => 'tr_test_123',
            'processed_by_user_id' => $admin->id,
        ]);

        Notification::assertSentTo($payoutRequest->requester, PayoutRequestPaidNotification::class);
    }

    private function makeAdminAndPayoutRequest(): array
    {
        $admin = User::factory()->create([
            'user_role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $vendor = User::factory()->create([
            'user_role' => User::ROLE_TOUR_OWNER,
            'email_verified_at' => now(),
        ]);

        $tenant = Tenant::create([
            'name' => 'Payout Admin Tenant',
            'slug' => 'payout-admin-tenant-'.Str::lower(Str::random(5)),
            'type' => 'tour_company',
            'status' => 'approved',
            'owner_user_id' => $vendor->id,
        ]);

        VendorProfile::create([
            'tenant_id' => $tenant->id,
            'kyc_status' => 'approved',
            'stripe_connect_account_id' => 'acct_test1234567890',
            'payout_mode' => 'platform_payouts',
        ]);

        $payoutRequest = PayoutRequest::create([
            'tenant_id' => $tenant->id,
            'requested_by_user_id' => $vendor->id,
            'amount' => 250,
            'currency' => 'USD',
            'status' => 'approved',
        ]);

        return [$admin, $payoutRequest];
    }
}
