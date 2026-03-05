<?php

namespace Tests\Feature\Admin;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_vendor_approval_status(): void
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
            'name' => 'Pending Vendor',
            'slug' => 'pending-vendor-'.Str::lower(Str::random(5)),
            'type' => 'tour_company',
            'status' => 'pending',
            'owner_user_id' => $vendor->id,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.vendors.approval', $tenant), [
            'status' => 'approved',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'status' => 'approved',
        ]);
    }
}
