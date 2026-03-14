<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->tenant = Tenant::factory()->create();
    }

    private function createUser(string $role): User
    {
        $user = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_can_manage_employees(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/employees');

        $response->assertOk();
    }

    public function test_master_cannot_manage_employees(): void
    {
        $master = $this->createUser('master');

        $response = $this->actingAs($master, 'sanctum')
            ->getJson('/api/v1/employees');

        $response->assertForbidden();
    }

    public function test_master_cannot_delete_products(): void
    {
        $master = $this->createUser('master');

        $product = Product::withoutGlobalScopes()->create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Test Product',
            'article' => 'TEST-001',
            'unit' => 'pcs',
            'status' => 'active',
            'type' => 'regular',
        ]);

        $response = $this->actingAs($master, 'sanctum')
            ->deleteJson("/api/v1/products/{$product->id}");

        $response->assertForbidden();
    }

    public function test_master_can_view_products(): void
    {
        $master = $this->createUser('master');

        $response = $this->actingAs($master, 'sanctum')
            ->getJson('/api/v1/products');

        $response->assertOk();
    }

    public function test_warehouse_can_create_products(): void
    {
        $warehouse = $this->createUser('warehouse');

        $response = $this->actingAs($warehouse, 'sanctum')
            ->postJson('/api/v1/products', [
                'name' => 'New Product',
                'article' => 'WH-001',
                'unit' => 'pcs',
                'status' => 'active',
                'type' => 'regular',
            ]);

        $response->assertStatus(201);
    }

    public function test_purchaser_can_manage_suppliers(): void
    {
        $purchaser = $this->createUser('purchaser');

        $response = $this->actingAs($purchaser, 'sanctum')
            ->getJson('/api/v1/suppliers');

        $response->assertOk();
    }

    public function test_supplier_can_only_see_own_orders(): void
    {
        $user = User::factory()->create(['tenant_id' => null]);
        $user->assignRole('supplier');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/supplier/orders');

        $response->assertOk();
    }
}
