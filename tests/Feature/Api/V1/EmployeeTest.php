<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->tenant = Tenant::factory()->create();
        $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_create_employee_with_role(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/employees', [
                'name' => 'New Master',
                'email' => 'master@test.com',
                'password' => 'password',
                'role' => 'master',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.role', 'master');

        $this->assertDatabaseHas('users', [
            'email' => 'master@test.com',
            'tenant_id' => $this->tenant->id,
        ]);
    }

    public function test_admin_can_deactivate_employee(): void
    {
        $employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $employee->assignRole('master');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/employees/{$employee->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('users', ['id' => $employee->id]);
    }

    public function test_admin_can_restore_employee(): void
    {
        $employee = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'is_active' => false,
        ]);
        $employee->assignRole('master');
        $employee->delete();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/employees/{$employee->id}/restore");

        $response->assertOk()
            ->assertJsonPath('data.is_active', true);
    }
}
