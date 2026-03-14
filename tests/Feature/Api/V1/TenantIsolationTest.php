<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant1;
    private Tenant $tenant2;
    private User $user1;
    private User $user2;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->tenant1 = Tenant::factory()->create();
        $this->tenant2 = Tenant::factory()->create();

        $this->user1 = User::factory()->create(['tenant_id' => $this->tenant1->id]);
        $this->user1->assignRole('admin');

        $this->user2 = User::factory()->create(['tenant_id' => $this->tenant2->id]);
        $this->user2->assignRole('admin');
    }

    public function test_tenant1_cannot_see_tenant2_products(): void
    {
        $this->actingAs($this->user1, 'sanctum')
            ->postJson('/api/v1/products', [
                'name' => 'Tenant 1 Product',
                'article' => 'T1-001',
                'unit' => 'pcs',
                'status' => 'active',
                'type' => 'regular',
            ])->assertStatus(201);

        $this->actingAs($this->user2, 'sanctum')
            ->postJson('/api/v1/products', [
                'name' => 'Tenant 2 Product',
                'article' => 'T2-001',
                'unit' => 'pcs',
                'status' => 'active',
                'type' => 'regular',
            ])->assertStatus(201);

        $response = $this->actingAs($this->user1, 'sanctum')
            ->getJson('/api/v1/products');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Tenant 1 Product', $data[0]['name']);
    }

    public function test_tenant1_cannot_edit_tenant2_products(): void
    {
        $response = $this->actingAs($this->user2, 'sanctum')
            ->postJson('/api/v1/products', [
                'name' => 'Tenant 2 Product',
                'article' => 'T2-002',
                'unit' => 'pcs',
                'status' => 'active',
                'type' => 'regular',
            ]);

        $productId = $response->json('data.id');

        $editResponse = $this->actingAs($this->user1, 'sanctum')
            ->putJson("/api/v1/products/{$productId}", [
                'name' => 'Hacked',
            ]);

        $editResponse->assertNotFound();
    }

    public function test_products_auto_get_tenant_id_on_create(): void
    {
        $response = $this->actingAs($this->user1, 'sanctum')
            ->postJson('/api/v1/products', [
                'name' => 'Auto Tenant Product',
                'article' => 'ATP-001',
                'unit' => 'pcs',
                'status' => 'active',
                'type' => 'regular',
            ]);

        $response->assertStatus(201);
        $this->assertEquals(
            $this->tenant1->id,
            Product::withoutGlobalScopes()->where('article', 'ATP-001')->first()->tenant_id,
        );
    }
}
