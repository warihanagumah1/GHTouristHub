<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_only_sees_owned_or_member_tenants(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $outsider = User::factory()->create();

        $ownedTenant = Tenant::create([
            'name' => 'Owned Tenant',
            'slug' => 'owned-tenant-'.Str::lower(Str::random(5)),
            'type' => 'tour_company',
            'status' => 'approved',
            'owner_user_id' => $owner->id,
        ]);

        $memberTenant = Tenant::create([
            'name' => 'Member Tenant',
            'slug' => 'member-tenant-'.Str::lower(Str::random(5)),
            'type' => 'utility_owner',
            'status' => 'approved',
            'owner_user_id' => $member->id,
        ]);

        TenantMember::create([
            'tenant_id' => $memberTenant->id,
            'user_id' => $owner->id,
            'role' => 'utility_staff',
            'is_active' => true,
        ]);

        $otherTenant = Tenant::create([
            'name' => 'Other Tenant',
            'slug' => 'other-tenant-'.Str::lower(Str::random(5)),
            'type' => 'utility_owner',
            'status' => 'approved',
            'owner_user_id' => $outsider->id,
        ]);

        $visibleIds = Tenant::query()->visibleTo($owner)->pluck('id')->all();

        $this->assertContains($ownedTenant->id, $visibleIds);
        $this->assertContains($memberTenant->id, $visibleIds);
        $this->assertNotContains($otherTenant->id, $visibleIds);
    }
}
