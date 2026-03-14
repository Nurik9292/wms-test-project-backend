<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $suppliers = Supplier::whereHas('tenants', fn ($q) => $q->where('tenant_id', $request->user()->tenant_id))
            ->with('user', 'tenants')
            ->orderBy('company_name')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => SupplierResource::collection($suppliers),
            'meta' => [
                'current_page' => $suppliers->currentPage(),
                'last_page' => $suppliers->lastPage(),
                'per_page' => $suppliers->perPage(),
                'total' => $suppliers->total(),
            ],
        ]);
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $request): JsonResponse {
            $user = User::create([
                'tenant_id' => null,
                'name' => $data['contact_name'] ?? $data['company_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
            ]);

            $user->assignRole('supplier');

            $supplier = Supplier::create([
                'user_id' => $user->id,
                'company_name' => $data['company_name'],
                'contact_name' => $data['contact_name'] ?? null,
                'country' => $data['country'] ?? null,
            ]);

            $supplier->tenants()->attach($request->user()->tenant_id, ['is_active' => true]);

            $supplier->load('user', 'tenants');

            return response()->json([
                'data' => new SupplierResource($supplier),
            ], 201);
        });
    }

    public function update(int $id, UpdateSupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::whereHas('tenants', fn ($q) => $q->where('tenant_id', $request->user()->tenant_id))
            ->findOrFail($id);

        $supplier->update($request->validated());
        $supplier->load('user', 'tenants');

        return response()->json([
            'data' => new SupplierResource($supplier),
        ]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $supplier = Supplier::whereHas('tenants', fn ($q) => $q->where('tenant_id', $request->user()->tenant_id))
            ->findOrFail($id);

        $supplier->tenants()->updateExistingPivot($request->user()->tenant_id, ['is_active' => false]);

        return response()->json(null, 204);
    }
}
