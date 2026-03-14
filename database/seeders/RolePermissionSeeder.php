<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'products.edit_price',
            'stock.view',
            'stock.reserve',
            'stock.write_off',
            'stock.receive',
            'employees.manage',
            'suppliers.manage',
            'supplier.orders',
            'tenant.settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $rolePermissions = [
            'admin' => [
                'products.view', 'products.create', 'products.edit', 'products.delete', 'products.edit_price',
                'stock.view', 'stock.reserve', 'stock.write_off', 'stock.receive',
                'employees.manage', 'suppliers.manage', 'tenant.settings',
            ],
            'senior_master' => [
                'products.view', 'products.edit', 'products.edit_price',
                'stock.view', 'stock.reserve', 'stock.write_off',
            ],
            'master' => [
                'products.view',
                'stock.view', 'stock.reserve',
            ],
            'warehouse' => [
                'products.view', 'products.create', 'products.edit',
                'stock.view', 'stock.reserve', 'stock.write_off', 'stock.receive',
            ],
            'purchaser' => [
                'products.view', 'products.create', 'products.edit', 'products.edit_price',
                'stock.view', 'stock.receive',
                'suppliers.manage',
            ],
            'supplier' => [
                'supplier.orders',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
        }
    }
}
