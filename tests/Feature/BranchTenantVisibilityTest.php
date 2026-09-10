<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Business;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BranchTenantVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('branches');
        Schema::dropIfExists('users');
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('businesses');

        Schema::create('businesses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('currency_code')->nullable();
            $table->timestamps();
        });

        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('business_id')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->string('status')->default('active');
            $table->string('plan')->nullable();
            $table->string('currency_code')->nullable();
            $table->string('country_code')->nullable();
            $table->string('admin_email')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('full_name');
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->uuid('tenant_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('tenant_id')->nullable();
            $table->uuid('business_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function test_current_tenant_only_sees_branches_for_its_business(): void
    {
        $businessA = Business::create([
            'id' => '11111111-1111-4111-8111-111111111111',
            'name' => 'Business A',
            'currency_code' => 'UGX',
        ]);

        $businessB = Business::create([
            'id' => '22222222-2222-4222-8222-222222222222',
            'name' => 'Business B',
            'currency_code' => 'UGX',
        ]);

        $tenantA = Tenant::create([
            'id' => '33333333-3333-4333-8333-333333333333',
            'name' => 'Tenant A',
            'business_id' => $businessA->id,
            'slug' => 'tenant-a',
            'status' => 'active',
            'plan' => 'starter',
            'currency_code' => 'UGX',
            'country_code' => 'UG',
            'admin_email' => 'tenant-a@example.com',
        ]);

        Tenant::create([
            'id' => '44444444-4444-4444-8444-444444444444',
            'name' => 'Tenant B',
            'business_id' => $businessB->id,
            'slug' => 'tenant-b',
            'status' => 'active',
            'plan' => 'starter',
            'currency_code' => 'UGX',
            'country_code' => 'UG',
            'admin_email' => 'tenant-b@example.com',
        ]);

        $user = User::create([
            'id' => '55555555-5555-4555-8555-555555555555',
            'full_name' => 'Tenant A Admin',
            'email' => 'admin@tenant-a.test',
            'password_hash' => bcrypt('password'),
            'tenant_id' => $tenantA->id,
            'is_active' => true,
        ]);

        $branchA = Branch::create([
            'id' => '66666666-6666-4666-8666-666666666666',
            'name' => 'Main Branch',
            'business_id' => $businessA->id,
            'tenant_id' => $tenantA->id,
            'is_active' => true,
        ]);

        Branch::create([
            'id' => '77777777-7777-4777-8777-777777777777',
            'name' => 'Other Branch',
            'business_id' => $businessB->id,
            'tenant_id' => '44444444-4444-4444-8444-444444444444',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $visibleIds = Branch::query()->forCurrentTenantBusiness()->pluck('id')->all();

        $this->assertContains($branchA->id, $visibleIds);
        $this->assertNotContains('77777777-7777-4777-8777-777777777777', $visibleIds);
    }
}
